<?php declare(strict_types=1);

session_start();

// Only logged-in admins can access this page.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Generate a CSRF token.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

$title = '';
$category = '';
$customerMessage = '';
$instructions = '';
$isActive = '1';

// Handle the form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $customerMessage = trim($_POST['customer_message'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $isActive = $_POST['is_active'] ?? '1';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Verify the CSRF token.
    if (
        !is_string($csrfToken) ||
        !hash_equals($_SESSION['admin_csrf'], $csrfToken)
    ) {
        $error = 'Invalid request. Please refresh the page and try again.';

    } elseif (
        $title === '' ||
        $category === '' ||
        $customerMessage === '' ||
        $instructions === ''
    ) {
        $error = 'Please complete all required fields.';

    } elseif (
        mb_strlen($title, 'UTF-8') > 150 ||
        mb_strlen($category, 'UTF-8') > 100
    ) {
        $error = 'The test title or category is too long.';

    } elseif (!in_array($isActive, ['0', '1'], true)) {
        $error = 'Please select a valid test status.';

    } else {

        try {
            // Start a transaction so the test and its criteria
            // are saved together.
            $pdo->beginTransaction();

            // Insert the new test.
            $scenarioStatement = $pdo->prepare(
                'INSERT INTO scenarios
                    (title, category, customer_message, instructions, is_active)
                 VALUES
                    (:title, :category, :customer_message, :instructions, :is_active)'
            );

            $scenarioStatement->execute([
                'title' => $title,
                'category' => $category,
                'customer_message' => $customerMessage,
                'instructions' => $instructions,
                'is_active' => (int) $isActive
            ]);

            // Get the ID of the newly created test.
            $scenarioId = (int) $pdo->lastInsertId();

            // Default criteria automatically assigned to every new test.
            $defaultCriteria = [
                [
                    'name' => 'Acknowledgement',
                    'description' => 'Acknowledges the customer concern or inconvenience.',
                    'weight' => 30,
                    'rule_type' => 'keyword',
                    'rule_value' => 'sorry;understand;concern;inconvenience;thank you'
                ],
                [
                    'name' => 'Clear next steps',
                    'description' => 'Provides clear and helpful next steps to the customer.',
                    'weight' => 40,
                    'rule_type' => 'keyword',
                    'rule_value' => 'next step;check;review;investigate;update;contact;assist;provide;help;resolve'
                ],
                [
                    'name' => 'Professional tone',
                    'description' => 'Uses polite, respectful and professional language.',
                    'weight' => 30,
                    'rule_type' => 'keyword',
                    'rule_value' => 'please;thank you;appreciate;happy to help;I will;we will;could you'
                ]
            ];

            // Prepare the criteria insert query.
            $criteriaStatement = $pdo->prepare(
                'INSERT INTO criteria
                    (scenario_id, name, description, weight, rule_type, rule_value)
                 VALUES
                    (:scenario_id, :name, :description, :weight, :rule_type, :rule_value)'
            );

            // Insert all three default criteria.
            foreach ($defaultCriteria as $criterion) {
                $criteriaStatement->execute([
                    'scenario_id' => $scenarioId,
                    'name' => $criterion['name'],
                    'description' => $criterion['description'],
                    'weight' => $criterion['weight'],
                    'rule_type' => $criterion['rule_type'],
                    'rule_value' => $criterion['rule_value']
                ]);
            }

            // Save the test and criteria together.
            $pdo->commit();

            // Redirect to the test list after successful creation.
            header('Location: manage-tests.php?created=1');
            exit;

        } catch (Throwable $exception) {

            // Roll back both inserts if anything fails.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Admin add test error: ' . $exception->getMessage());

            $error = 'Unable to create the test. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Test | ServiceReady Admin</title>

    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>

    <div class="admin-layout">

        <aside class="admin-sidebar">

            <a href="index.php" class="admin-brand">
                <span class="admin-brand-mark">S</span>
                <span>Service<span class="brand-accent">Ready</span></span>
            </a>

            <p class="sidebar-label">ADMIN MENU</p>

            <a href="manage-tests.php" class="sidebar-link active">
                <span>▤</span>
                Manage Tests
            </a>

            <a href="logout.php" class="sidebar-link logout-sidebar">
                <span>↪</span>
                Logout
            </a>

            <div class="sidebar-footer">
                Admin Panel
            </div>

        </aside>

        <main class="admin-main">

            <header class="admin-topbar">
                <div>
                    <h1>Add New Test</h1>
                    <p>Create a new practice test for ServiceReady users.</p>
                </div>

                <span class="admin-user">Admin</span>
            </header>

            <section class="admin-content">

                <div class="form-page-heading">
                    <div>
                        <h2>Test Details</h2>
                        <p>Enter the information required for the new test.</p>
                    </div>

                    <a href="manage-tests.php" class="back-button">
                        ← Back to Tests
                    </a>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="admin-message error-message">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add-test.php" class="admin-form">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <div class="form-group">
                        <label for="title">Test Title *</label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            maxlength="150"
                            placeholder="Example: Customer Complaint Handling"
                            value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>

                        <input
                            type="text"
                            id="category"
                            name="category"
                            maxlength="100"
                            placeholder="Example: Customer Support"
                            value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="customer_message">Customer Message *</label>

                        <textarea
                            id="customer_message"
                            name="customer_message"
                            rows="5"
                            placeholder="Enter the message or situation presented by the customer."
                            required
                        ><?= htmlspecialchars($customerMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="instructions">Instructions *</label>

                        <textarea
                            id="instructions"
                            name="instructions"
                            rows="5"
                            placeholder="Explain what the user should consider when responding."
                            required
                        ><?= htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="is_active">Test Status</label>

                        <select id="is_active" name="is_active">
                            <option value="1" <?= $isActive === '1' ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="0" <?= $isActive === '0' ? 'selected' : '' ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="default-criteria-note">
                        <h3>Default Evaluation Criteria</h3>

                        <p>
                            Three evaluation criteria will be automatically
                            assigned to this test.
                        </p>

                        <ul>
                            <li>Acknowledgement — 30%</li>
                            <li>Clear next steps — 40%</li>
                            <li>Professional tone — 30%</li>
                        </ul>

                        <p class="criteria-note-footer">
                            No manual criteria configuration is required.
                        </p>
                    </div>

                    <div class="form-actions">

                        <a href="manage-tests.php" class="cancel-button">
                            Cancel
                        </a>

                        <button type="submit" class="primary-button">
                            Create Test
                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>

</body>
</html>
