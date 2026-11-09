<?php declare(strict_types=1);

session_start();

// Only logged-in admins can access this page.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get the test ID from the URL.
$testId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$testId || $testId < 1) {
    header('Location: manage-tests.php');
    exit;
}

// Generate a CSRF token.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

// Fetch the existing test.
$stmt = $pdo->prepare(
    'SELECT id, title, category, customer_message, instructions, is_active
     FROM scenarios
     WHERE id = :id
     LIMIT 1'
);

$stmt->execute(['id' => $testId]);
$test = $stmt->fetch();

if (!$test) {
    header('Location: manage-tests.php');
    exit;
}

// Load the existing test details into the form.
$title = $test['title'];
$category = $test['category'];
$customerMessage = $test['customer_message'];
$instructions = $test['instructions'];
$isActive = (string) $test['is_active'];

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $customerMessage = trim($_POST['customer_message'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $isActive = $_POST['is_active'] ?? '1';
    $csrfToken = $_POST['csrf_token'] ?? '';

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
            // Update only the test details.
            // Existing evaluation criteria are not modified.
            $update = $pdo->prepare(
                'UPDATE scenarios
                 SET title = :title,
                     category = :category,
                     customer_message = :customer_message,
                     instructions = :instructions,
                     is_active = :is_active
                 WHERE id = :id'
            );

            $update->execute([
                'title' => $title,
                'category' => $category,
                'customer_message' => $customerMessage,
                'instructions' => $instructions,
                'is_active' => (int) $isActive,
                'id' => $testId
            ]);

            header('Location: manage-tests.php?updated=1');
            exit;

        } catch (Throwable $exception) {
            error_log('Admin edit test error: ' . $exception->getMessage());
            $error = 'Unable to update the test. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Test | ServiceReady Admin</title>

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
                    <h1>Edit Test</h1>
                    <p>Update your existing practice test details.</p>
                </div>

                <span class="admin-user">Admin</span>
            </header>

            <section class="admin-content">

                <div class="form-page-heading">
                    <div>
                        <h2>Edit Test Details</h2>
                        <p>Test #<?= (int) $testId ?></p>
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

                <form method="POST"
                      action="edit-test.php?id=<?= (int) $testId ?>"
                      class="admin-form">

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
                            required
                        ><?= htmlspecialchars($customerMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="instructions">Instructions *</label>

                        <textarea
                            id="instructions"
                            name="instructions"
                            rows="5"
                            required
                        ><?= htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="is_active">Test Status</label>

                        <select id="is_active" name="is_active">

                            <option value="1"
                                <?= $isActive === '1' ? 'selected' : '' ?>>
                                Active
                            </option>

                            <option value="0"
                                <?= $isActive === '0' ? 'selected' : '' ?>>
                                Inactive
                            </option>

                        </select>
                    </div>

                    <div class="default-criteria-note">
                        <h3>Evaluation Criteria</h3>

                        <p>
                            Existing evaluation criteria will remain unchanged
                            when you update this test.
                        </p>
                    </div>

                    <div class="form-actions">

                        <a href="manage-tests.php" class="cancel-button">
                            Cancel
                        </a>

                        <button type="submit" class="primary-button">
                            Save Changes
                        </button>

                    </div>

                </form>

            </section>

        </main>

    </div>

</body>
</html>
