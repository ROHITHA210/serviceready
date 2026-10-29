<?php declare(strict_types=1);

// Connect to the database so we can retrieve the selected practice scenario.
require_once __DIR__ . '/config/database.php';

// Read the scenario ID from the URL and validate that it is an integer.
// Example: assessment.php?id=1
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Stop the request if the scenario ID is missing or invalid.
// HTTP 400 means the request contains invalid input.
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Please select a valid scenario.');
}

// Prepare a query to retrieve the selected active scenario.
// A prepared statement keeps the user-supplied ID separate from the SQL query.
$stmt = $pdo->prepare(
    'SELECT id, title, category, customer_message, instructions
     FROM scenarios
     WHERE id = :id AND is_active = 1'
);

// Pass the validated scenario ID into the query and execute it.
$stmt->execute(['id' => $id]);

// Fetch the matching scenario as an associative array.
// If no active scenario matches the ID, fetch() returns false.
$scenario = $stmt->fetch();

// Show a 404 response when the requested scenario does not exist
// or is no longer active.
if (!$scenario) {
    http_response_code(404);
    exit('Scenario not found.');
}

// Escape dynamic text before displaying it in HTML.
// This helps prevent HTML or JavaScript entered into the database
// from being interpreted as executable content.
function escapeHtml(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Define the character encoding and responsive page layout. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the purpose of this page for browsers and search engines. -->
    <meta name="description" content="Practise a customer-service scenario with ServiceReady.">

    <!-- Use the selected scenario's title as part of the browser tab title. -->
    <title><?= escapeHtml($scenario['title']) ?> | ServiceReady</title>

    <!-- Load the shared stylesheet and JavaScript used across the application. -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <!-- Shared site header with navigation to the main application sections. -->
    <header class="site-header">
        <a class="brand" href="index.php">
            <span class="brand-mark">S</span>
            <span>Service<span class="brand-accent">Ready</span></span>
        </a>

        <nav class="main-nav">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link active" href="index.php#scenarios">Practice</a>
            <a class="nav-link" href="index.php#recent">My progress</a>
        </nav>
    </header>

    <main class="page-container">
        <!-- Main section containing the selected scenario and response form. -->
        <section class="content-section" style="margin-top: 36px;">

            <!-- Let the user return to the dashboard without submitting a response. -->
            <a class="text-link" href="index.php">← Back to dashboard</a>

            <!-- Display the scenario title and identify the activity as written practice. -->
            <div class="section-heading" style="margin-top: 22px;">
                <div>
                    <span class="eyebrow">PRACTICE SCENARIO</span>
                    <h2><?= escapeHtml($scenario['title']) ?></h2>
                </div>
                <span class="count-pill">Written practice</span>
            </div>

            <!-- Show the customer's original message and guidance for answering it. -->
            <div class="scenario-context-grid">
                <article class="scenario-context-panel">
                    <span class="eyebrow">CUSTOMER MESSAGE</span>

                    <!-- Escape the message because it comes from the database. -->
                    <p class="scenario-context-message">
                        <?= escapeHtml($scenario['customer_message']) ?>
                    </p>
                </article>

                <article class="scenario-context-panel">
                    <span class="eyebrow">WHAT YOU NEED TO DO</span>
                    <h3>Build a helpful response</h3>

                    <p>
                        Start by acknowledging the customer's concern and showing that
                        you understand the issue.
                    </p>
                    <p>
                        Explain what you can do next. Ask for relevant details if needed,
                        and avoid promising an outcome you cannot confirm.
                    </p>
                    <p>
                        Finish politely. Keep your response clear, professional, and
                        focused on helping the customer.
                    </p>
                </article>
            </div>

            <!-- Collect the user's written reply through an HTML form. -->
            <section class="scenario-card" style="min-height: auto;">
                <span class="eyebrow">YOUR RESPONSE</span>
                <h3 style="margin-top: 12px;">How would you reply to this customer?</h3>

                <p class="scenario-description" style="display: block;">
                    Write a professional response that acknowledges the concern
                    and explains the next steps clearly.
                </p>

                <!-- Send the response to the PHP endpoint that processes submissions. -->
                <form action="api/submit-attempt.php" method="post">

                    <!-- Include the scenario ID so the server knows which scenario was answered. -->
                    <input type="hidden" name="scenario_id"
                           value="<?= (int) $scenario['id'] ?>">

                    <!-- Associate the visible label with the response text area. -->
                    <label for="response" style="display: block; margin-bottom: 8px;">
                        Your message
                    </label>

                    <!--
                        The textarea collects the written response.
                        Required and length attributes provide browser-side validation;
                        the server must validate the input again when it is submitted.
                    -->
                    <textarea
                        id="response"
                        name="response"
                        rows="8"
                        maxlength="5000"
                        minlength="10"
                        required
                        placeholder="Write your response to the customer here..."
                        style="display: block; width: 100%; resize: vertical;
                               padding: 14px; border: 1px solid var(--border);
                               border-radius: 10px; background: var(--page-bg);
                               color: var(--text-primary); font: inherit;"
                    ></textarea>

                    <!-- Show the allowed response length and live character count. -->
                    <div style="display: flex; justify-content: space-between;
                                gap: 12px; margin-top: 8px;">
                        <span class="duration-label">
                            10–5000 characters
                        </span>

                        <!-- JavaScript can update this element as the user types. -->
                        <span class="duration-label" id="characterCount">
                            0 / 5000
                        </span>
                    </div>

                    <!-- Provide actions to submit the response or return to the dashboard. -->
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;
                                margin-top: 22px;">
                        <button class="button button-primary" type="submit">
                            Submit response →
                        </button>

                        <a class="button button-secondary" href="index.php">
                            Cancel
                        </a>
                    </div>
                </form>
            </section>
        </section>

        <!-- Display the shared footer and current year automatically. -->
        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practice thoughtfully. Communicate confidently.</span>
        </footer>
    </main>
</body>
</html>
