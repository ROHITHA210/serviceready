<?php declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Define the character encoding and responsive page layout. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the purpose of this page. -->
    <meta name="description" content="Practise a customer-service scenario with ServiceReady.">

    <!-- JavaScript will update the title after retrieving the scenario. -->
    <title>Practice Scenario | ServiceReady</title>

    <!-- Load the shared stylesheet and existing application JavaScript. -->
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>

    <!-- Load the JavaScript responsible for retrieving individual scenarios. -->
    <script src="assets/js/assessment.js" defer></script>
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

            <!-- Let the user return to the dashboard. -->
            <a class="text-link" href="index.php">← Back to dashboard</a>

            <!-- Display the scenario title after retrieving it from the API. -->
            <div class="section-heading" style="margin-top: 22px;">
                <div>
                    <span class="eyebrow">PRACTICE SCENARIO</span>
                    <h2 id="scenarioTitle">Loading scenario...</h2>
                </div>

                <span class="count-pill">Written practice</span>
            </div>

            <!-- Display the customer message and scenario instructions. -->
            <div class="scenario-context-grid">

                <article class="scenario-context-panel">
                    <span class="eyebrow">CUSTOMER MESSAGE</span>

                    <!-- JavaScript inserts the customer message returned by the API. -->
                    <p class="scenario-context-message" id="customerMessage">
                        Loading customer message...
                    </p>
                </article>

                <article class="scenario-context-panel">
                    <span class="eyebrow">WHAT YOU NEED TO DO</span>
                    <h3>Build a helpful response</h3>

                    <!-- Display the instructions stored for this scenario. -->
                    <p id="scenarioInstructions">
                        Loading scenario instructions...
                    </p>
                </article>

            </div>

            <!-- Collect the user's written response. -->
            <section class="scenario-card" style="min-height: auto;">
                <span class="eyebrow">YOUR RESPONSE</span>

                <h3 style="margin-top: 12px;">
                    How would you reply to this customer?
                </h3>

                <p class="scenario-description" style="display: block;">
                    Write a professional response that acknowledges the concern
                    and explains the next steps clearly.
                </p>

                <!-- The existing PHP endpoint will process this form for now. -->
                <form action="api/submit-attempt.php" method="post" id="assessmentForm">

                    <!-- JavaScript inserts the selected scenario ID here. -->
                    <input type="hidden" name="scenario_id" id="scenarioId" value="">

                    <!-- Associate the visible label with the response text area. -->
                    <label for="response" style="display: block; margin-bottom: 8px;">
                        Your message
                    </label>

                    <!-- Collect the user's written response. -->
                    <textarea
                        id="response"
                        name="response"
                        rows="8"
                        maxlength="5000"
                        minlength="10"
                        required
                        disabled
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

                        <span class="duration-label" id="characterCount">
                            0 / 5000
                        </span>
                    </div>

                    <!-- Display API errors if the scenario cannot be loaded. -->
                    <p id="assessmentError" role="alert" hidden></p>

                    <!-- Submit the response or return to the dashboard. -->
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;
                                margin-top: 22px;">

                        <button
                            class="button button-primary"
                            type="submit"
                            id="submitButton"
                            disabled
                        >
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
