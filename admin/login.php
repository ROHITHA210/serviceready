<?php declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';

// If the admin is already logged in, open the admin home page.
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Create a CSRF token for the login form.
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (
        !hash_equals($_SESSION['admin_csrf'], $csrfToken)
    ) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, email, password_hash
             FROM admins
             WHERE email = :email AND is_active = 1
             LIMIT 1"
        );

        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if (
            $admin &&
            password_verify($password, $admin['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];

            unset($_SESSION['admin_csrf']);

            header('Location: index.php');
            exit;
        }

        $error = 'Incorrect email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | ServiceReady</title>

    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/js/admin-login.js" defer></script>
</head>

<body>

    <main class="login-page">

        <section class="login-card">

            <div class="brand-mark">S</div>

            <h1>Service<span>Ready</span></h1>

            <p class="login-subtitle">Admin Panel</p>

            <div class="login-heading">
                <h2>Welcome back</h2>
                <p>Sign in to manage your tests.</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="login-error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>"
                >

                <label for="email">Email address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your admin email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="username"
                    required
                >

                <label for="password">Password</label>

                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="togglePassword"
                        aria-label="Show password"
                        aria-pressed="false"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <button type="submit" class="login-button">
                    Sign In
                </button>

            </form>

            <p class="login-footer">
                © <?= date('Y') ?> ServiceReady
            </p>

        </section>

    </main>

</body>
</html>
