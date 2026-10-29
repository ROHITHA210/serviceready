<?php declare(strict_types=1);

// Load the database connection so we can retrieve a saved assessment
// and its criterion-level feedback.
require_once __DIR__ . '/config/database.php';

// Read and validate the assessment ID from the URL.
// Example: results.php?id=12
$attemptId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Reject missing, invalid, or non-positive assessment IDs.
if (!$attemptId || $attemptId < 1) {
    http_response_code(400);
    exit('Please provide a valid assessment ID.');
}

// Escape dynamic values before placing them in HTML.
// This helps prevent stored text from being interpreted as HTML or JavaScript.
function escapeHtml(?string $value): string
{
    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

try {
    // Retrieve the selected attempt and its related scenario information.
    // INNER JOIN connects each attempt to the scenario it belongs to.
    // The scenario title and category help identify the assessment.
    $attemptStatement = $pdo->prepare(
        'SELECT a.id, a.scenario_id, a.response_text, a.total_score, a.created_at,
                s.title AS scenario_title, s.category
         FROM attempts AS a
         INNER JOIN scenarios AS s ON s.id = a.scenario_id
         WHERE a.id = :id'
    );

    // Pass the validated assessment ID into the prepared statement.
    $attemptStatement->execute(['id' => $attemptId]);

    // Fetch the matching assessment as an associative array.
    $attempt = $attemptStatement->fetch();

    // Return a 404 response if this assessment ID does not exist.
    if (!$attempt) {
        http_response_code(404);
        exit('Assessment result not found.');
    }

    // Retrieve the individual criterion scores and feedback saved
    // when the assessment was submitted.
    // JOIN connects saved scores to their corresponding criteria.
    $scoresStatement = $pdo->prepare(
        'SELECT c.name, c.description, c.weight, sc.score, sc.feedback
         FROM attempt_scores AS sc
         INNER JOIN criteria AS c ON c.id = sc.criterion_id
         WHERE sc.attempt_id = :attempt_id
         ORDER BY c.id ASC'
    );

    // Use the current attempt ID to retrieve only its saved criterion scores.
    $scoresStatement->execute(['attempt_id' => $attemptId]);

    // Fetch all criterion results for display on the page.
    $scores = $scoresStatement->fetchAll();
} catch (Throwable $exception) {
    // Log the technical error for debugging without exposing database details.
    error_log('ServiceReady results error: ' . $exception->getMessage());

    // Show a generic error if the assessment results cannot be loaded.
    http_response_code(500);
    exit('Unable to load this assessment result.');
}

// Convert the stored overall score to a number and keep the displayed value
// between 0 and 100. This also keeps the progress bar within its expected range.
$totalScore = max(0, min(100, (float) $attempt['total_score']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Configure the page encoding and responsive layout. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the purpose of the results page. -->
    <meta name="description" content="Review your ServiceReady practice assessment.">

    <!-- Set the browser tab title. -->
    <title>Assessment Results | ServiceReady</title>

    <!-- Reuse the application's shared stylesheet. -->
    <link rel="stylesheet" href="assets/css/style.css">
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

            <!-- Show which scenario was evaluated. Escape database content before display. -->
            <p style="color: var(--text-secondary);">
                <?= escapeHtml($attempt['scenario_title']) ?>
            </p>

            <!-- Display the overall score and a visual progress indicator. -->
            <article class="scenario-card"
                     style="min-height: auto; margin: 24px 0;">
                <span class="stat-label">Overall rule-based score</span>

                <!-- Format the overall score to one decimal place. -->
                <strong style="display: block; margin: 10px 0;
                               color: var(--accent); font-size: 54px;
                               line-height: 1.2;">
                    <?= number_format($totalScore, 1) ?><span style="font-size: 24px;">%</span>
                </strong>

                <!-- Set the progress bar width from the validated 0–100 score. -->
                <div style="height: 9px; overflow: hidden; border-radius: 99px;
                            background: var(--border);">
                    <div style="width: <?= $totalScore ?>%; height: 100%;
                                border-radius: 99px; background: var(--accent);">
                    </div>
                </div>

                <!-- Display the submission date and explain what the score represents. -->
                <p class="scenario-description" style="display: block; margin: 16px 0 0;">
                    Submitted <?= escapeHtml(
                        date('M j, Y · g:i A', strtotime($attempt['created_at']))
                    ) ?>.
                    This score reflects configured keyword matches, not a
                    complete judgement of communication quality.
                </p>
            </article>

            <!-- Display the original response so the user can review what they submitted. -->
            <section class="scenario-card" style="min-height: auto;">
                <h2 style="font-size: 19px;">Your submitted response</h2>

                <!-- Escape the saved response and preserve its line breaks. -->
                <p style="color: var(--text-secondary); white-space: pre-wrap;
                          overflow-wrap: anywhere;"><?= escapeHtml($attempt['response_text']) ?></p>
            </section>

            <!-- Show feedback for each evaluation criterion. -->
            <section class="content-section">
                <div class="section-heading">
                    <div>
                        <h2>Criteria feedback</h2>
                        <p>Review what the configured rules detected.</p>
                    </div>
                </div>

                <!-- Render a feedback card for every saved criterion score. -->
                <?php if ($scores): ?>
                    <div class="attempt-list">
                        <?php foreach ($scores as $item): ?>

                            <?php
                            // Convert this criterion's score to a number and
                            // constrain the displayed value to the 0–100 range.
                            $criterionScore = max(
                                0,
                                min(100, (float) $item['score'])
                            );
                            ?>

                            <article class="scenario-card"
                                     style="min-height: auto; border: 0;
                                            border-bottom: 1px solid var(--border);
                                            border-radius: 0;">

                                <!-- Display the criterion name, description, and score. -->
                                <div style="display: flex; justify-content: space-between;
                                            align-items: flex-start; gap: 16px;">
                                    <div>
                                        <h3><?= escapeHtml($item['name']) ?></h3>

                                        <p class="scenario-description"
                                           style="display: block; margin-bottom: 12px;">
                                            <?= escapeHtml($item['description']) ?>
                                        </p>
                                    </div>

                                    <!-- Show the criterion score as a percentage. -->
                                    <strong style="color: var(--accent); white-space: nowrap;">
                                        <?= number_format($criterionScore, 0) ?>%
                                    </strong>
                                </div>

                                <!-- Visualize the score for this individual criterion. -->
                                <div style="height: 6px; overflow: hidden;
                                            border-radius: 99px; background: var(--border);">
                                    <div style="width: <?= $criterionScore ?>%;
                                                height: 100%; background: var(--accent);">
                                    </div>
                                </div>

                                <!-- Show the feedback generated during submission. -->
                                <p style="margin: 13px 0 0; color: var(--text-secondary);
                                          font-size: 13px;">
                                    <?= escapeHtml($item['feedback']) ?>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>

                    <!-- Provide a fallback when no criterion scores were saved. -->
                    <div class="empty-state">
                        <p>No criteria-level feedback was saved for this attempt.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Allow the user to repeat this scenario or return to the dashboard. -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin: 24px 0;">
                <a class="button button-primary"
                   href="assessment.php?id=<?= (int) $attempt['scenario_id'] ?>">
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
