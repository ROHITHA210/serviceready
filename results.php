<?php declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$attemptId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$attemptId || $attemptId < 1) {
    http_response_code(400);
    exit('Please provide a valid assessment ID.');
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

try {
    $attemptStatement = $pdo->prepare(
        'SELECT a.id, a.scenario_id, a.response_text, a.total_score, a.created_at,
                s.title AS scenario_title, s.category
         FROM attempts AS a
         INNER JOIN scenarios AS s ON s.id = a.scenario_id
         WHERE a.id = :id'
    );
    $attemptStatement->execute(['id' => $attemptId]);
    $attempt = $attemptStatement->fetch();

    if (!$attempt) {
        http_response_code(404);
        exit('Assessment result not found.');
    }

    $scoresStatement = $pdo->prepare(
        'SELECT c.name, c.description, c.weight, sc.score, sc.feedback
         FROM attempt_scores AS sc
         INNER JOIN criteria AS c ON c.id = sc.criterion_id
         WHERE sc.attempt_id = :attempt_id
         ORDER BY c.id ASC'
    );
    $scoresStatement->execute(['attempt_id' => $attemptId]);
    $scores = $scoresStatement->fetchAll();
} catch (Throwable $exception) {
    error_log('ServiceReady results error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Unable to load this assessment result.');
}

$totalScore = max(0, min(100, (float) $attempt['total_score']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Review your ServiceReady practice assessment.">
    <title>Assessment Results | ServiceReady</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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
        <section class="content-section" style="margin-top: 36px;">
            <span class="eyebrow">ASSESSMENT COMPLETE</span>
            <h1 style="font-size: clamp(30px, 4vw, 42px); margin-bottom: 8px;">
                Your practice results
            </h1>
            <p style="color: var(--text-secondary);">
    <?= escapeHtml($attempt['scenario_title']) ?>
</p>

            <article class="scenario-card"
                     style="min-height: auto; margin: 24px 0;">
                <span class="stat-label">Overall rule-based score</span>
                <strong style="display: block; margin: 10px 0;
                               color: var(--accent); font-size: 54px;
                               line-height: 1.2;">
                    <?= number_format($totalScore, 1) ?><span style="font-size: 24px;">%</span>
                </strong>

                <div style="height: 9px; overflow: hidden; border-radius: 99px;
                            background: var(--border);">
                    <div style="width: <?= $totalScore ?>%; height: 100%;
                                border-radius: 99px; background: var(--accent);">
                    </div>
                </div>

                <p class="scenario-description" style="display: block; margin: 16px 0 0;">
                    Submitted <?= escapeHtml(
                        date('M j, Y · g:i A', strtotime($attempt['created_at']))
                    ) ?>.
                    This score reflects configured keyword matches, not a
                    complete judgement of communication quality.
                </p>
            </article>

            <section class="scenario-card" style="min-height: auto;">
                <h2 style="font-size: 19px;">Your submitted response</h2>
                <p style="color: var(--text-secondary); white-space: pre-wrap;
                          overflow-wrap: anywhere;"><?= escapeHtml($attempt['response_text']) ?></p>
            </section>

            <section class="content-section">
                <div class="section-heading">
                    <div>
                        <h2>Criteria feedback</h2>
                        <p>Review what the configured rules detected.</p>
                    </div>
                </div>

                <?php if ($scores): ?>
                    <div class="attempt-list">
                        <?php foreach ($scores as $item): ?>
                            <?php
                            $criterionScore = max(
                                0,
                                min(100, (float) $item['score'])
                            );
                            ?>
                            <article class="scenario-card"
                                     style="min-height: auto; border: 0;
                                            border-bottom: 1px solid var(--border);
                                            border-radius: 0;">
                                <div style="display: flex; justify-content: space-between;
                                            align-items: flex-start; gap: 16px;">
                                    <div>
                                        <h3><?= escapeHtml($item['name']) ?></h3>
                                        <p class="scenario-description"
                                           style="display: block; margin-bottom: 12px;">
                                            <?= escapeHtml($item['description']) ?>
                                        </p>
                                    </div>
                                    <strong style="color: var(--accent); white-space: nowrap;">
                                        <?= number_format($criterionScore, 0) ?>%
                                    </strong>
                                </div>

                                <div style="height: 6px; overflow: hidden;
                                            border-radius: 99px; background: var(--border);">
                                    <div style="width: <?= $criterionScore ?>%;
                                                height: 100%; background: var(--accent);">
                                    </div>
                                </div>

                                <p style="margin: 13px 0 0; color: var(--text-secondary);
                                          font-size: 13px;">
                                    <?= escapeHtml($item['feedback']) ?>
                                </p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No criteria-level feedback was saved for this attempt.</p>
                    </div>
                <?php endif; ?>
            </section>

            

            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin: 24px 0;">
                <a class="button button-primary"
   href="assessment.php?id=<?= (int) $attempt['scenario_id'] ?>">
    Practise again
</a>
                <a class="button button-secondary" href="index.php">
                    Back to dashboard
                </a>
            </div>

            <p class="stat-note">
                The evaluation uses configured keyword rules. It may miss
                valid answers or reward keywords without understanding their context.
                Use the feedback as a practice guide, not as a hiring decision.
            </p>
        </section>

        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practise thoughtfully. Communicate confidently.</span>
        </footer>
    </main>
</body>
</html>
