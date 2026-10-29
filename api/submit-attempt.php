<?php declare(strict_types=1);

// Load the database connection so this file can read scenarios
// and save assessment attempts and their individual scores.
require_once __DIR__ . '/../config/database.php';

// Only accept POST requests because this endpoint processes a submitted form.
// Directly opening this URL in a browser normally sends a GET request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Tell the client which HTTP method this endpoint accepts.
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// Read the scenario ID submitted by the assessment form.
// FILTER_VALIDATE_INT checks that the supplied value is an integer.
$scenarioId = filter_input(
    INPUT_POST,
    'scenario_id',
    FILTER_VALIDATE_INT
);

// Read the customer's response, convert missing input to an empty string,
// and remove unnecessary whitespace from the beginning and end.
$response = trim((string) ($_POST['response'] ?? ''));

// Reject missing, invalid, or non-positive scenario IDs before querying the database.
if (!$scenarioId || $scenarioId < 1) {
    http_response_code(400);
    exit('Invalid scenario selected.');
}

// Enforce the permitted response length on the server.
// Browser-side validation alone is not enough because it can be bypassed.
if (mb_strlen($response) < 10 || mb_strlen($response) > 5000) {
    http_response_code(400);
    exit('Your response must contain between 10 and 5000 characters.');
}

try {
    // Confirm that the requested scenario exists and is currently active.
    // A prepared statement keeps the supplied ID separate from the SQL query.
    $scenarioStatement = $pdo->prepare(
        'SELECT id FROM scenarios WHERE id = :id AND is_active = 1'
    );

    // Bind the submitted scenario ID and execute the query.
    $scenarioStatement->execute(['id' => $scenarioId]);

    // Stop processing if the scenario does not exist or is inactive.
    if (!$scenarioStatement->fetch()) {
        http_response_code(404);
        exit('This scenario is unavailable.');
    }

    // Retrieve the evaluation criteria configured for this scenario.
    // Each criterion has a weight and a rule that determines how it is scored.
    $criteriaStatement = $pdo->prepare(
        'SELECT id, name, description, weight, rule_type, rule_value
         FROM criteria
         WHERE scenario_id = :scenario_id
         ORDER BY id ASC'
    );

    // Supply the scenario ID to the criteria query.
    $criteriaStatement->execute(['scenario_id' => $scenarioId]);

    // Fetch all matching criteria so they can be evaluated one by one.
    $criteria = $criteriaStatement->fetchAll();

    // A score cannot be calculated if no evaluation criteria are configured.
    if (!$criteria) {
        http_response_code(422);
        exit('This scenario has no evaluation criteria yet.');
    }

    // Convert the response to lowercase so keyword matching is
    // case-insensitive; for example, "Refund" and "refund" match equally.
    $normalizedResponse = mb_strtolower($response, 'UTF-8');

    // Accumulate the total weight and weighted scores for the final calculation.
    $totalWeight = 0.0;
    $weightedScore = 0.0;

    // Store each criterion's score and feedback until the database save step.
    $evaluations = [];

    // Evaluate every configured criterion for the selected scenario.
    foreach ($criteria as $criterion) {

        // Convert the configured weight to a number and prevent negative
        // weights from contributing to the overall score.
        $weight = max(0.0, (float) $criterion['weight']);

        // Add this criterion's weight to the total used for normalization.
        $totalWeight += $weight;

        // Split the configured keywords at semicolons, then trim whitespace
        // and convert each keyword to lowercase for consistent matching.
        $keywords = array_filter(
            array_map(
                static fn(string $word): string => trim(mb_strtolower($word, 'UTF-8')),
                explode(';', (string) $criterion['rule_value'])
            ),
            // Remove empty entries so they do not affect the keyword count.
            static fn(string $word): bool => $word !== ''
        );

        // Start with no matched keywords for the current criterion.
        $matchedKeywords = [];

        // Apply keyword scoring only when the criterion uses the supported
        // "keyword" rule type and has at least one configured keyword.
        if (
            strtolower((string) $criterion['rule_type']) === 'keyword'
            && count($keywords) > 0
        ) {
            // Check whether each configured keyword appears in the response.
            foreach ($keywords as $keyword) {
                if (mb_strpos($normalizedResponse, $keyword) !== false) {
                    $matchedKeywords[] = $keyword;
                }
            }

            // Calculate the percentage of configured keywords that were found.
            // Example: 3 matches out of 4 keywords produces a score of 75%.
            $criterionScore = round(
                count($matchedKeywords) / count($keywords) * 100,
                2
            );
        } else {
            // Unsupported rule types or empty keyword lists receive zero.
            $criterionScore = 0.0;
        }

        // Multiply this criterion's percentage by its weight.
        // The weighted values will be divided by total weight later.
        $weightedScore += $criterionScore * $weight;

        // Generate feedback based on whether any configured keywords matched.
        // This feedback describes keyword detection, not a full understanding
        // of the meaning or quality of the response.
        $feedback = count($matchedKeywords) > 0
            ? 'Matched terms: ' . implode(', ', $matchedKeywords) . '. Review whether your response addresses the criteria fully.'
            : 'No configured keywords were detected. Review this criteria and consider addressing it more explicitly.';

        // Keep the result for this criterion in an array until it is saved.
        $evaluations[] = [
            'criterion_id' => (int) $criterion['id'],
            'name' => (string) $criterion['name'],
            'score' => $criterionScore,
            'feedback' => $feedback,
        ];
    }

    // Avoid division by zero and reject configurations with no positive weight.
    if ($totalWeight <= 0) {
        http_response_code(422);
        exit('The evaluation criteria have no valid weights.');
    }

    // Calculate the weighted average across all criteria.
    // Dividing by the total weight keeps the final score on a 0–100 scale.
    $totalScore = round($weightedScore / $totalWeight, 2);

    // Start a database transaction so the attempt and all criterion scores
    // can be saved together or rolled back together if something fails.
    $pdo->beginTransaction();

    // Prepare the SQL statement that saves the overall assessment attempt.
    $attemptStatement = $pdo->prepare(
        'INSERT INTO attempts (scenario_id, response_text, total_score, created_at)
         VALUES (:scenario_id, :response_text, :total_score, NOW())'
    );

    // Save the selected scenario, submitted response, calculated score,
    // and current database timestamp.
    $attemptStatement->execute([
        'scenario_id' => $scenarioId,
        'response_text' => $response,
        'total_score' => $totalScore,
    ]);

    // Retrieve the ID generated for this new assessment attempt.
    // It is used to associate the criterion scores with this attempt.
    $attemptId = (int) $pdo->lastInsertId();

    // Prepare one reusable statement for saving individual criterion scores.
    $scoreStatement = $pdo->prepare(
        'INSERT INTO attempt_scores (attempt_id, criterion_id, score, feedback)
         VALUES (:attempt_id, :criterion_id, :score, :feedback)'
    );

    // Save each criterion's score and feedback, linking it to the new attempt.
    foreach ($evaluations as $evaluation) {
        $scoreStatement->execute([
            'attempt_id' => $attemptId,
            'criterion_id' => $evaluation['criterion_id'],
            'score' => $evaluation['score'],
            'feedback' => $evaluation['feedback'],
        ]);
    }

    // Commit the transaction only after all database inserts have succeeded.
    $pdo->commit();

    // Redirect the user to the results page for this specific attempt.
    header('Location: ../results.php?id=' . $attemptId);

    // Stop execution after sending the redirect.
    exit;
} catch (Throwable $exception) {
    // If a transaction is still active, undo its database changes.
    // This prevents a partially saved assessment when an operation fails.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Record technical details in the server log instead of exposing
    // database errors to the user in the browser.
    error_log('ServiceReady submission failed: ' . $exception->getMessage());

    // Return a generic server error message to the user.
    http_response_code(500);
    exit('We could not save your assessment. Check the PHP error log.');
}
