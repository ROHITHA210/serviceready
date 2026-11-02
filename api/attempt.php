<?php declare(strict_types=1);

// Tell the browser or API client that this endpoint returns JSON.
header('Content-Type: application/json; charset=utf-8');

// This endpoint only supports GET requests because it retrieves saved results.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

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
 * @param array $data Response data.
 */
function sendAttemptResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Read and validate the assessment ID from the URL.
// Example: api/attempt.php?id=15
$attemptId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Reject missing, invalid, or non-positive assessment IDs.
if ($attemptId === false || $attemptId === null || $attemptId < 1) {
    sendAttemptResponse(400, [
        'success' => false,
        'message' => 'Please provide a valid assessment ID.'
    ]);
}

// Load the shared PDO database connection.
require_once __DIR__ . '/../config/database.php';

try {
    // Retrieve the saved attempt and its related scenario information.
    $attemptStatement = $pdo->prepare(
        'SELECT a.id, a.scenario_id, a.response_text, a.total_score, a.created_at,
                s.title AS scenario_title, s.category
         FROM attempts AS a
         INNER JOIN scenarios AS s ON s.id = a.scenario_id
         WHERE a.id = :id'
    );

    // Execute the query using the validated assessment ID.
    $attemptStatement->execute([
        'id' => $attemptId
    ]);

    // Fetch the matching assessment.
    $attempt = $attemptStatement->fetch();

    // Return 404 if the requested assessment does not exist.
    if (!$attempt) {
        sendAttemptResponse(404, [
            'success' => false,
            'message' => 'Assessment result not found.'
        ]);
    }

    // Retrieve the individual criterion scores and their feedback.
    $scoresStatement = $pdo->prepare(
        'SELECT c.name, c.description, c.weight, sc.score, sc.feedback
         FROM attempt_scores AS sc
         INNER JOIN criteria AS c ON c.id = sc.criterion_id
         WHERE sc.attempt_id = :attempt_id
         ORDER BY c.id ASC'
    );

    // Retrieve scores belonging only to the selected assessment.
    $scoresStatement->execute([
        'attempt_id' => $attemptId
    ]);

    // Fetch all criterion-level results.
    $scores = $scoresStatement->fetchAll();

    // Convert the overall score to a number and constrain it to 0–100.
    $totalScore = max(
        0.0,
        min(100.0, (float) $attempt['total_score'])
    );

    // Return the assessment and its criterion feedback as JSON.
    sendAttemptResponse(200, [
        'success' => true,
        'message' => 'Assessment result retrieved successfully.',
        'data' => [
            'id' => (int) $attempt['id'],
            'scenario_id' => (int) $attempt['scenario_id'],
            'scenario_title' => (string) $attempt['scenario_title'],
            'category' => (string) $attempt['category'],
            'response_text' => (string) $attempt['response_text'],
            'total_score' => $totalScore,
            'created_at' => (string) $attempt['created_at'],
            'scores' => $scores
        ]
    ]);

} catch (Throwable $exception) {

    // Record technical details in the PHP error log.
    // Do not expose database errors to API users.
    error_log(
        'ServiceReady attempt API error: ' . $exception->getMessage()
    );

    // Return a generic JSON server error.
    sendAttemptResponse(500, [
        'success' => false,
        'message' => 'Unable to load this assessment result.'
    ]);
}
