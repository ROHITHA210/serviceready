<?php declare(strict_types=1);

// Tell the browser or API client that this endpoint returns JSON.
header('Content-Type: application/json; charset=utf-8');

// This endpoint only supports GET requests because it retrieves scenario data.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

// Read the scenario ID from the URL.
// Example: api/scenario.php?id=1
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Check whether the scenario ID is missing or invalid.
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid scenario ID.'
    ]);

    exit;
}

// Load the existing PDO database connection.
require_once __DIR__ . '/../config/database.php';

try {
    // Prepare a query to retrieve one active scenario.
    // A prepared statement keeps the supplied ID separate from the SQL query.
    $statement = $pdo->prepare(
        'SELECT id, title, category, customer_message, instructions
         FROM scenarios
         WHERE id = :id AND is_active = 1'
    );

    // Bind the validated scenario ID and execute the query.
    $statement->execute([
        'id' => $id
    ]);

    // Retrieve the matching scenario as an associative array.
    $scenario = $statement->fetch();

    // Return a 404 response if the scenario does not exist or is inactive.
    if (!$scenario) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Scenario not found.'
        ]);

        exit;
    }

    // Return the selected scenario as JSON.
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Scenario retrieved successfully.',
        'data' => $scenario
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Throwable $exception) {

    // Record technical details in the server log.
    // Do not expose database or server error details to API users.
    error_log('ServiceReady scenario API error: ' . $exception->getMessage());

    // Return a generic server error response.
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve the scenario.'
    ]);
}
