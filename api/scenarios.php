<?php declare(strict_types=1);

// Set the response content type to JSON.
// This tells the browser or API client that the response contains JSON data.
header('Content-Type: application/json; charset=utf-8');

// Allow only GET requests because this endpoint retrieves scenario information.
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
    // Retrieve all active scenarios from the database.
    // Only the fields required by the frontend are selected.
    $statement = $pdo->query(
        'SELECT id, title, category, customer_message, instructions
         FROM scenarios
         WHERE is_active = 1
         ORDER BY id ASC'
    );

    // Fetch all matching records as associative arrays.
    $scenarios = $statement->fetchAll();

    // Return a consistent JSON response containing the scenarios.
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'message' => 'Scenarios retrieved successfully.',
        'count' => count($scenarios),
        'data' => $scenarios
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

} catch (Throwable $exception) {
    // Keep technical details in the server log instead of exposing them.
    error_log('ServiceReady scenarios API error: ' . $exception->getMessage());

    // Return a generic error response to the API client.
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to retrieve scenarios.'
    ]);
}
