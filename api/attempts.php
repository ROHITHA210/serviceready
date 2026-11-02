<?php declare(strict_types=1);

// Tell the client that this endpoint returns JSON.
header('Content-Type: application/json; charset=utf-8');

// This endpoint only supports GET requests.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

// Load the shared PDO database connection.
require_once __DIR__ . '/../config/database.php';

try {
    // Count all saved assessment attempts.
    $countStatement = $pdo->query(
        'SELECT COUNT(*) FROM attempts'
    );

    $totalAttempts = (int) $countStatement->fetchColumn();

    // Check whether the client requested the complete assessment history.
    // The homepage uses the default option and receives only five attempts.
    $showAll = isset($_GET['all']) && $_GET['all'] === '1';

    // Retrieve all attempts for the history page, or only the latest five
    // for the homepage.
    $sql = '
        SELECT a.id, a.total_score, a.created_at,
               s.title AS scenario_title
        FROM attempts AS a
        INNER JOIN scenarios AS s ON s.id = a.scenario_id
        ORDER BY a.created_at DESC, a.id DESC
    ';

    // Apply the five-record limit only when all attempts were not requested.
    if (!$showAll) {
        $sql .= ' LIMIT 5';
    }

    // Execute the query and retrieve the assessment records.
    $attemptStatement = $pdo->query($sql);

    $attempts = $attemptStatement->fetchAll();

    // Return the attempt count and retrieved records as JSON.
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Assessment history retrieved successfully.',
        'count' => $totalAttempts,
        'showing' => count($attempts),
        'data' => $attempts
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Throwable $exception) {

    // Record technical details in the PHP error log.
    error_log(
        'ServiceReady attempts API error: ' . $exception->getMessage()
    );

    // Return a generic error message to the client.
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve assessment history.'
    ], JSON_UNESCAPED_UNICODE);
}
