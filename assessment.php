<?php declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Please select a valid scenario.');
}

$stmt = $pdo->prepare(
    'SELECT id, title, category, customer_message, instructions
     FROM scenarios
     WHERE id = :id AND is_active = 1'
);

$stmt->execute(['id' => $id]);
$scenario = $stmt->fetch();

if (!$scenario) {
    http_response_code(404);
    exit('Scenario not found.');
}

function escapeHtml(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Practise a customer-service scenario with ServiceReady.">
    <title><?= escapeHtml($scenario['title']) ?> | ServiceReady</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
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
        <section class="content-section" style="margin-top: 36px;">
            <a class="text-link" href="index.php">← Back to dashboard</a>

            <div class="section-heading" style="margin-top: 22px;">
                <div>
                    <span class="eyebrow">PRACTICE SCENARIO</span>
                    <h2><?= escapeHtml($scenario['title']) ?></h2>
                </div>
                <span class="count-pill">Written practice</span>
            </div>

            
<div class="scenario-context-grid">
    <article class="scenario-context-panel">
        <span class="eyebrow">CUSTOMER MESSAGE</span>
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


            <section class="scenario-card" style="min-height: auto;">
                <span class="eyebrow">YOUR RESPONSE</span>
                <h3 style="margin-top: 12px;">How would you reply to this customer?</h3>
                <p class="scenario-description" style="display: block;">
                    Write a professional response that acknowledges the concern
                    and explains the next steps clearly.
                </p>

                <form action="api/submit-attempt.php" method="post">
                    <input type="hidden" name="scenario_id"
                           value="<?= (int) $scenario['id'] ?>">

                    <label for="response" style="display: block; margin-bottom: 8px;">
                        Your message
                    </label>

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

                    <div style="display: flex; justify-content: space-between;
                                gap: 12px; margin-top: 8px;">
                        <span class="duration-label">
                            10–5000 characters
                        </span>
                        <span class="duration-label" id="characterCount">
                            0 / 5000
                        </span>
                    </div>

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

        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practice thoughtfully. Communicate confidently.</span>
        </footer>
    </main>
</body>
</html>
