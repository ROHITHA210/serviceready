<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Configure page encoding and responsive layout. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the assessment history page. -->
    <meta
        name="description"
        content="View all your completed ServiceReady practice assessments."
    >

    <title>Assessment History | ServiceReady</title>

    <!-- Reuse the existing ServiceReady stylesheet. -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Load shared navigation functionality. -->
    <script src="assets/js/app.js" defer></script>

    <!-- Load all assessment attempts from the history API. -->
    <script src="assets/js/history-page.js" defer></script>
</head>

<body>
    <!-- Shared header used throughout ServiceReady. -->
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="ServiceReady home">
            <span class="brand-mark">S</span>
            <span>Service<span class="brand-accent">Ready</span></span>
        </a>

        <!-- Mobile navigation toggle. -->
        <button
            class="menu-toggle"
            id="menuToggle"
            type="button"
            aria-label="Toggle navigation"
            aria-expanded="false"
            aria-controls="mainNav"
        >
            ☰
        </button>

        <!-- Navigation links. -->
        <nav class="main-nav" id="mainNav">
            <a class="nav-link" href="index.php">Dashboard</a>
            <a class="nav-link" href="index.php#scenarios">Practice</a>
            <a class="nav-link active" href="history.php">My progress</a>
        </nav>
    </header>

    <main class="page-container">

        <!-- Heading for the complete assessment history. -->
        <section class="content-section" style="margin-top: 36px;">

            <!-- Return to the homepage. -->
            <a class="text-link" href="index.php">
                ← Back to dashboard
            </a>

            <div class="section-heading" style="margin-top: 24px;">
                <div>
                    <span class="eyebrow">YOUR PROGRESS</span>
                    <h2>Assessment History</h2>
                    <p>Review all your completed practice attempts.</p>
                </div>

                <!-- Display the total number of saved assessments. -->
                <span class="count-pill" id="historyCount">
                    Loading...
                </span>
            </div>

            <!-- JavaScript inserts every assessment returned by the API. -->
            <div class="attempt-list" id="historyList">
                <div class="empty-state">
                    <p>Loading your assessment history...</p>
                </div>
            </div>

            <!-- Display an error if the history API request fails. -->
            <p id="historyError" role="alert" hidden></p>

        </section>

        <!-- Shared footer. -->
        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practise thoughtfully. Communicate confidently.</span>
        </footer>

    </main>
</body>
</html>
