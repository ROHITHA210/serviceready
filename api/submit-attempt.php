<?php declare(strict_types=1);

// Tell the client that this endpoint returns JSON.
header('Content-Type: application/json; charset=utf-8');

// This endpoint only accepts POST requests because it saves assessment responses.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

/**
 * Send a JSON response and stop further execution.
 *
 * @param int $statusCode HTTP response status code.
 * @param array $data Response data to convert into JSON.
 */
function sendJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Read the JSON request body sent by JavaScript.
$requestBody = file_get_contents('php://input');

// Convert the JSON request body into a PHP associative array.
$input = json_decode($requestBody, true);

// Reject invalid JSON or requests that do not contain a JSON object.
if (!is_array($input) || json_last_error() !== JSON_ERROR_NONE) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON request.'
    ]);
}

// Validate the scenario ID received in the JSON request.
$scenarioId = filter_var(
    $input['scenario_id'] ?? null,
    FILTER_VALIDATE_INT
);

// Read and trim the submitted response.
$response = trim((string) ($input['response'] ?? ''));

// Reject missing, invalid, or non-positive scenario IDs.
if ($scenarioId === false || $scenarioId === null || $scenarioId < 1) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Invalid scenario selected.'
    ]);
}

// Validate the response length on the server.
// Client-side validation can be bypassed, so this check is essential.
if (mb_strlen($response, 'UTF-8') < 10 || mb_strlen($response, 'UTF-8') > 5000) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Your response must contain between 10 and 5000 characters.'
    ]);
}

// Load the shared PDO database connection.
require_once __DIR__ . '/../config/database.php';

try {
    // Confirm that the requested scenario exists and is currently active.
    $scenarioStatement = $pdo->prepare(
        'SELECT id
         FROM scenarios
         WHERE id = :id AND is_active = 1'
    );

    $scenarioStatement->execute([
        'id' => $scenarioId
    ]);

    // Stop if the scenario does not exist or is inactive.
    if (!$scenarioStatement->fetch()) {
        sendJsonResponse(404, [
            'success' => false,
            'message' => 'This scenario is unavailable.'
        ]);
    }

    // Retrieve the evaluation criteria configured for this scenario.
    $criteriaStatement = $pdo->prepare(
        'SELECT id, name, description, weight, rule_type, rule_value
         FROM criteria
         WHERE scenario_id = :scenario_id
         ORDER BY id ASC'
    );

    $criteriaStatement->execute([
        'scenario_id' => $scenarioId
    ]);

    // Fetch all criteria so they can be evaluated individually.
    $criteria = $criteriaStatement->fetchAll();

    // A score cannot be calculated without evaluation criteria.
    if (!$criteria) {
        sendJsonResponse(422, [
            'success' => false,
            'message' => 'This scenario has no evaluation criteria yet.'
        ]);
    }

    // Convert the response to lowercase for case-insensitive keyword matching.
    $normalizedResponse = mb_strtolower($response, 'UTF-8');

    // Initialize the values used for the weighted average calculation.
    $totalWeight = 0.0;
    $weightedScore = 0.0;

    // Store individual criterion results until they are saved.
    $evaluations = [];

    // Evaluate every criterion configured for the selected scenario.
    foreach ($criteria as $criterion) {

        // Convert the criterion weight to a number and prevent negative weights.
        $weight = max(0.0, (float) $criterion['weight']);

        // Add this criterion's weight to the total weight.
        $totalWeight += $weight;

        // Split configured keywords using semicolons.
        // Trim whitespace and convert keywords to lowercase.
        $keywords = array_filter(
            array_map(
                static fn(string $word): string => trim(
                    mb_strtolower($word, 'UTF-8')
                ),
                explode(';', (string) $criterion['rule_value'])
            ),
            static fn(string $word): bool => $word !== ''
        );

        // Start with an empty list of matched keywords.
        $matchedKeywords = [];

        // Apply keyword scoring when the criterion uses the keyword rule.
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

            // Calculate the percentage of configured keywords that matched.
            // Example: 3 matches out of 4 keywords gives 75%.
            $criterionScore = round(
                count($matchedKeywords) / count($keywords) * 100,
                2
            );

        } else {
            // Unsupported rule types or empty keyword lists receive zero.
            $criterionScore = 0.0;
        }

        // Add the weighted criterion score to the overall calculation.
        $weightedScore += $criterionScore * $weight;

        // Generate feedback based on the matched keywords.
        $feedback = count($matchedKeywords) > 0
            ? 'Matched terms: ' . implode(', ', $matchedKeywords) . '. Review whether your response addresses the criteria fully.'
            : 'No configured keywords were detected. Review this criteria and consider addressing it more explicitly.';

        // Store the criterion evaluation until the database save operation.
        $evaluations[] = [
            'criterion_id' => (int) $criterion['id'],
            'name' => (string) $criterion['name'],
            'score' => $criterionScore,
            'feedback' => $feedback,
        ];
    }

    // Reject criteria configurations with no positive total weight.
    if ($totalWeight <= 0) {
        sendJsonResponse(422, [
            'success' => false,
            'message' => 'The evaluation criteria have no valid weights.'
        ]);
    }

    // Calculate the weighted average score on a 0–100 scale.
    $totalScore = round($weightedScore / $totalWeight, 2);

    // Start a transaction so the attempt and its criterion scores
    // are saved together or rolled back together.
    $pdo->beginTransaction();

    // Prepare the SQL statement for saving the overall assessment attempt.
    $attemptStatement = $pdo->prepare(
        'INSERT INTO attempts (scenario_id, response_text, total_score, created_at)
         VALUES (:scenario_id, :response_text, :total_score, NOW())'
    );

    // Save the selected scenario, submitted response, and calculated score.
    $attemptStatement->execute([
        'scenario_id' => $scenarioId,
        'response_text' => $response,
        'total_score' => $totalScore,
    ]);

    // Retrieve the ID generated for the new assessment attempt.
    $attemptId = (int) $pdo->lastInsertId();

    // Prepare a reusable statement for saving individual criterion scores.
    $scoreStatement = $pdo->prepare(
        'INSERT INTO attempt_scores (attempt_id, criterion_id, score, feedback)
         VALUES (:attempt_id, :criterion_id, :score, :feedback)'
    );

    // Save every criterion score and feedback message.
    foreach ($evaluations as $evaluation) {
        $scoreStatement->execute([
            'attempt_id' => $attemptId,
            'criterion_id' => $evaluation['criterion_id'],
            'score' => $evaluation['score'],
            'feedback' => $evaluation['feedback'],
        ]);
    }

    // Commit only after every database insert has succeeded.
    $pdo->commit();

    // Return the saved assessment details as JSON.
    sendJsonResponse(201, [
        'success' => true,
        'message' => 'Assessment submitted successfully.',
        'data' => [
            'attempt_id' => $attemptId,
            'total_score' => $totalScore,
            'evaluations' => $evaluations
        ]
    ]);

} catch (Throwable $exception) {

    // Roll back any unfinished transaction if an error occurs.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Record technical details in the PHP error log.
    error_log(
        'ServiceReady submission API error: ' . $exception->getMessage()
    );

    // Return a generic JSON server error.
    sendJsonResponse(500, [
        'success' => false,
        'message' => 'We could not save your assessment. Please try again.'
    ]);
}
