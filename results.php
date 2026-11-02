<?php declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Configure the page encoding and responsive layout. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the purpose of the results page. -->
    <meta name="description" content="Review your ServiceReady practice assessment.">

    <!-- JavaScript updates this title after retrieving the assessment. -->
    <title>Assessment Results | ServiceReady</title>

    <!-- Reuse the application's shared stylesheet. -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Load the JavaScript responsible for retrieving assessment results. -->
    <script src="assets/js/results.js" defer></script>
</head>

<body>
    <!-- Shared navigation header for moving around the application. -->
    <header class="site-header">
        <a class="brand" href="index.php">
            <span class="brand-mark">S</span>
            <span>Service<span class="brand-accent">Ready</span></span>
        </a>

        <nav class="main-nav">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="index.php#scenarios">Practice</a>
            <a class="nav-link active" href="index.php#recent">My progress</a>
        </nav>
    </header>

    <main class="page-container">
        <!-- Main section containing the overall result and detailed feedback. -->
        <section class="content-section" style="margin-top: 36px;">

            <span class="eyebrow">ASSESSMENT COMPLETE</span>

            <h1 style="font-size: clamp(30px, 4vw, 42px); margin-bottom: 8px;">
                Your practice results
            </h1>

            <!-- JavaScript inserts the scenario title returned by the API. -->
            <p id="scenarioTitle" style="color: var(--text-secondary);">
                Loading assessment result...
            </p>

            <!-- Display the overall score and its visual progress indicator. -->
            <article class="scenario-card"
                     style="min-height: auto; margin: 24px 0;">

                <span class="stat-label">Overall rule-based score</span>

                <!-- JavaScript inserts the overall assessment score. -->
                <strong id="totalScore"
                        style="display: block; margin: 10px 0;
                               color: var(--accent); font-size: 54px;
                               line-height: 1.2;">
                    --<span style="font-size: 24px;">%</span>
                </strong>

                <!-- Visual progress bar for the overall score. -->
                <div style="height: 9px; overflow: hidden; border-radius: 99px;
                            background: var(--border);">
                    <div id="totalScoreBar"
                         style="width: 0%; height: 100%;
                                border-radius: 99px; background: var(--accent);">
                    </div>
                </div>

                <!-- Display the submission date and score explanation. -->
                <p id="submissionDate"
                   class="scenario-description"
                   style="display: block; margin: 16px 0 0;">
                    Loading submission date...
                </p>
            </article>

            <!-- Display the original response submitted by the user. -->
            <section class="scenario-card" style="min-height: auto;">
                <h2 style="font-size: 19px;">Your submitted response</h2>

                <!-- JavaScript inserts the saved response as plain text. -->
                <p id="submittedResponse"
                   style="color: var(--text-secondary); white-space: pre-wrap;
                          overflow-wrap: anywhere;">
                    Loading your response...
                </p>
            </section>

            <!-- Display feedback for each evaluation criterion. -->
            <section class="content-section">
                <div class="section-heading">
                    <div>
                        <h2>Criteria feedback</h2>
                        <p>Review what the configured rules detected.</p>
                    </div>
                </div>

                <!-- JavaScript creates one feedback card per criterion. -->
                <div class="attempt-list" id="criteriaFeedback">
                    <div class="empty-state">
                        <p>Loading criteria feedback...</p>
                    </div>
                </div>
            </section>

            <!-- Display errors if the results API request fails. -->
            <p id="resultsError" role="alert" hidden></p>

            <!-- Allow the user to repeat the scenario or return to the dashboard. -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin: 24px 0;">

                <!-- JavaScript updates this link using the returned scenario ID. -->
                <a class="button button-primary"
                   id="practiceAgainLink"
                   href="index.php"
                   aria-disabled="true">
                    Practise again
                </a>

                <a class="button button-secondary" href="index.php">
                    Back to dashboard
                </a>
            </div>

            <!-- Explain the limitations of keyword-based evaluation. -->
            <p class="stat-note">
                The evaluation uses configured keyword rules. It may miss
                valid answers or reward keywords without understanding their context.
                Use the feedback as a practice guide, not as a hiring decision.
            </p>
        </section>

        <!-- Shared footer with an automatically updated copyright year. -->
        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practise thoughtfully. Communicate confidently.</span>
        </footer>
    </main>
</body>
</html>
