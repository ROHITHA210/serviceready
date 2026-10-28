<?php declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$scenarios = [];
$recentAttempts = [];
$pageError = false;

try {
    // Retrieve active practice scenarios.
    $scenarioQuery = $pdo->query(
        'SELECT id, title, category, customer_message, instructions
         FROM scenarios
         WHERE is_active = 1
         ORDER BY id ASC'
    );

    $scenarios = $scenarioQuery->fetchAll();

    // Retrieve the five most recent assessment attempts.
    $attemptQuery = $pdo->query(
        'SELECT a.id, a.total_score, a.created_at, s.title AS scenario_title
         FROM attempts AS a
         INNER JOIN scenarios AS s ON s.id = a.scenario_id
         ORDER BY a.created_at DESC, a.id DESC
         LIMIT 5'
    );

    $recentAttempts = $attemptQuery->fetchAll();
} catch (Throwable $exception) {
    error_log('ServiceReady homepage error: ' . $exception->getMessage());
    $pageError = true;
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function scenarioIcon(string $category): string
{
    return match ($category) {
        'Order Management' => '📦',
        'Refunds and Returns' => '↩️',
        'Technical Support' => '💻',
        default => '💬',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Practise customer-service scenarios and review your progress with ServiceReady.">
    <title>ServiceReady | Customer Service Practice</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="ServiceReady home">
            <span class="brand-mark">S</span>
            <span>Service<span class="brand-accent">Ready</span></span>
        </a>

        <button class="menu-toggle" id="menuToggle"
                type="button" aria-label="Toggle navigation"
                aria-expanded="false" aria-controls="mainNav">
            ☰
        </button>

        <nav class="main-nav" id="mainNav">
            <a class="nav-link active" href="index.php">Dashboard</a>
            <a class="nav-link" href="#scenarios">Practice</a>
            <a class="nav-link" href="#recent">My progress</a>
        </nav>

        
    </header>

    <main class="page-container">
        <?php if ($pageError): ?>
            <section class="error-panel" role="alert">
                <h1>We couldn't load your dashboard</h1>
                <p>Please check your local database connection and try again.</p>
            </section>
        <?php else: ?>

            <section class="welcome-section">
                <div class="welcome-copy">
                    <!-- <span class="eyebrow">
                        <span class="status-dot"></span>
                        YOUR PRACTICE SPACE
                    </span> -->

                    <h1>Build confidence in <span>every conversation.</span></h1>

                    <p>
                        Practise realistic customer-service situations,
                        strengthen your communication, and learn from
                        structured feedback.
                    </p>

                    <a class="button button-primary" href="#scenarios">
                        Choose a scenario <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="welcome-visual" aria-hidden="true">
                    <div class="visual-orbit orbit-one"></div>
                    <div class="visual-orbit orbit-two"></div>
                    <div class="visual-center">SR</div>
                    <div class="floating-card floating-card-top">
                        <span class="floating-icon">✓</span>
                        <span>Practice with purpose</span>
                    </div>
                    <div class="floating-card floating-card-bottom">
                        <span class="floating-icon">↗</span>
                        <span>Improve one response at a time</span>
                    </div>
                </div>
            </section>

            <section class="stats-grid" aria-label="Practice overview">
                <article class="stat-card">
                    <span class="stat-label">Available scenarios</span>
                    <strong class="stat-number"><?= count($scenarios) ?></strong>
                    <span class="stat-note">Ready to practise</span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Completed attempts</span>
                    <strong class="stat-number">
                        <?= count($recentAttempts) ?>
                    </strong>
                    <span class="stat-note">
                        <?= count($recentAttempts) === 5
                            ? 'Showing your latest five'
                            : 'Recorded in your practice history' ?>
                    </span>
                </article>

                <article class="stat-card">
                    <span class="stat-label">Your next step</span>
                    <strong class="stat-number stat-text">Practise</strong>
                    <span class="stat-note">Build skills through repetition</span>
                </article>
            </section>

            <section class="content-section" id="scenarios">
                <div class="section-heading">
                    <div>
                        <!-- <span class="eyebrow">LEARN BY DOING</span> -->
                        <h2>Practice scenarios</h2>
                        <p>Choose a situation and write your response.</p>
                    </div>
                    <span class="count-pill">
                        <?= count($scenarios) ?> available
                    </span>
                </div>

                <?php if (count($scenarios) > 0): ?>
                    <div class="scenario-grid">
                        <?php foreach ($scenarios as $scenario): ?>
                            <article class="scenario-card">
                                

                                <h3><?= escapeHtml($scenario['title']) ?></h3>

                                <p class="scenario-description">
                                    <?= escapeHtml($scenario['customer_message']) ?>
                                </p>

                                <div class="scenario-card-footer">
                                    <span class="duration-label">Written practice</span>
                                    <a class="text-link"
                                       href="assessment.php?id=<?= (int) $scenario['id'] ?>">
                                        Start practice
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No active scenarios yet</h3>
                        <p>Practice scenarios will appear here when available.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="content-section recent-section" id="recent">
                <div class="section-heading">
                    <div>
                        <!-- <span class="eyebrow">KEEP TRACK</span> -->
                        <h2 class="eyebrow">Keep track of your recent assessments</h2>
                        <p>Review your latest practice attempts.</p>
                    </div>
                </div>

                <?php if (count($recentAttempts) > 0): ?>
                    <div class="attempt-list">
                        <?php foreach ($recentAttempts as $attempt): ?>
                            <article class="attempt-row">
                                <div class="attempt-icon" aria-hidden="true">✓</div>

                                <div class="attempt-details">
                                    <h3>
                                        <?= escapeHtml($attempt['scenario_title']) ?>
                                    </h3>
                                    <p>
                                        <?= escapeHtml(
                                            date('M j, Y · g:i A',
                                                strtotime($attempt['created_at']))
                                        ) ?>
                                    </p>
                                </div>

                                
                                <div class="attempt-score">
                                    <span>Rule-based score</span>
                                    <strong>
                                        <?= number_format(
                                            (float) $attempt['total_score'], 1
                                        ) ?>%
                                    </strong>
                                </div>


                                <a class="text-link"
                                   href="results.php?id=<?= (int) $attempt['id'] ?>">
                                    View result <span aria-hidden="true">→</span>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state recent-empty">
                        <span class="empty-icon" aria-hidden="true">◷</span>
                        <h3>Your practice history starts here</h3>
                        <p>
                            You haven't submitted an assessment yet.
                            Choose a scenario above to get started.
                        </p>
                        <a class="button button-secondary" href="#scenarios">
                            Explore scenarios
                        </a>
                    </div>
                <?php endif; ?>
            </section>

        <?php endif; ?>

        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practise thoughtfully. Communicate confidently.</span>
        </footer>
    </main>
</body>
</html>
