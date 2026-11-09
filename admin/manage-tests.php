<?php session_start();

// Allow access only to logged-in admins.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Retrieve all tests, including inactive ones.
    $statement = $pdo->query(
        'SELECT id, title, category, is_active
         FROM scenarios
         ORDER BY id DESC'
    );

    $tests = $statement->fetchAll();

} catch (Throwable $exception) {
    error_log('Admin manage tests error: ' . $exception->getMessage());
    $tests = [];
    $pageError = 'Unable to load tests. Please try again.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Tests | ServiceReady Admin</title>

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

        </aside>

        <main class="admin-main">

            <header class="admin-topbar">
                <div>
                    <h1>Manage Tests</h1>
                    <p>Create, view and manage your practice tests.</p>
                </div>
            </header>

            <section class="admin-content">

                <div class="content-heading">
                    <div>
                        <h2>All Tests</h2>
                        <p>
                            <?= count($tests) ?>
                            <?= count($tests) === 1 ? 'test' : 'tests' ?>
                            in total
                        </p>
                    </div>

                    <a href="add-test.php" class="primary-button">
                        + Add New Test
                    </a>
                </div>

                <?php if (!empty($pageError)): ?>

                    <div class="admin-message error-message">
                        <?= htmlspecialchars($pageError) ?>
                    </div>

                <?php elseif (empty($tests)): ?>

                    <div class="empty-state">
                        <h3>No tests found</h3>
                        <p>Start by adding your first practice test.</p>
                    </div>

                <?php else: ?>

                    <div class="tests-table-wrapper">

                        <table class="tests-table">

                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($tests as $test): ?>

                                    <tr>
                                        <td>
                                            <div class="test-title">
                                                <?= htmlspecialchars($test['title']) ?>
                                            </div>
                                            <div class="test-id">
                                                Test #<?= (int) $test['id'] ?>
                                            </div>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($test['category']) ?>
                                        </td>

                                        <td>
                                            <?php if ((int) $test['is_active'] === 1): ?>
                                                <span class="status-badge active-status">
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge inactive-status">
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <a
                                                href="edit-test.php?id=<?= (int) $test['id'] ?>"
                                                class="table-action"
                                            >
                                                Edit
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>

        </main>

    </div>

</body>
</html>

