<?php declare(strict_types=1); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Define the character encoding and make the layout adapt to device width. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Describe the purpose of the page for search engines and previews. -->
    <meta
        name="description"
        content="Practise customer-service scenarios and review your progress with ServiceReady."
    >

    <title>ServiceReady | Customer Service Practice</title>

    <!-- Load the shared stylesheet for layout, colors, and responsive design. -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Load JavaScript after HTML parsing so the page elements are available. -->
    <script src="assets/js/app.js" defer></script>
    <!-- Load the recent assessment history functionality. -->
    <script src="assets/js/history.js" defer></script>
</head>

<body>
    <!-- Shared header containing the brand and navigation links. -->
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="ServiceReady home">
            <span class="brand-mark">S</span>
            <span>Service<span class="brand-accent">Ready</span></span>
        </a>

        <!-- The JavaScript file uses this button to open or close mobile navigation. -->
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

        <!-- These links navigate to the homepage or sections on this page. -->
        <nav class="main-nav" id="mainNav">
            <a class="nav-link active" href="index.php">Dashboard</a>
            <a class="nav-link" href="#scenarios">Practice</a>
            <a class="nav-link" href="history.php">My progress</a>
        </nav>
    </header>

    <main class="page-container">


            <!-- Summary cards show the scenario count and recent activity. -->
<section class="stats-grid" aria-label="Practice overview">
    <article class="stat-card">
        <span class="stat-label">Available scenarios</span>
        <strong class="stat-number" id="scenarioTotal">Loading...</strong>
        <span class="stat-note">Ready to practise</span>
    </article>

    <article class="stat-card">
    <span class="stat-label">Completed attempts</span>

    <strong class="stat-number" id="attemptTotal">
        Loading...
    </strong>

    <span class="stat-note" id="attemptNote">
        Loading practice history...
    </span>
</article>

    <article class="stat-card">
        <span class="stat-label">Your next step</span>
        <strong class="stat-number stat-text">Practise</strong>
        <span class="stat-note">Build skills through repetition</span>
    </article>
</section>

<!-- Scenario cards will be generated using data from the scenarios API. -->
<section class="content-section" id="scenarios">
    <div class="section-heading">
        <div>
            <h2>Practice scenarios</h2>
            <p>Choose a situation and write your response.</p>
        </div>

        <span class="count-pill" id="scenarioCount">Loading...</span>
    </div>

    <!-- JavaScript inserts the scenario cards returned by the API here. -->
    <div class="scenario-grid" id="scenarioGrid">
        <div class="empty-state">
            <p>Loading practice scenarios...</p>
        </div>
    </div>
</section>

            <!-- Display the five most recent assessments returned by the history API. -->
<section class="content-section recent-section" id="recent">

    <div class="section-heading">
    <div>
        <h2 class="eyebrow">Keep track of your recent assessments</h2>
        <p>Review your latest practice attempts.</p>
    </div>

    <!-- Open the separate page containing all saved assessments. -->
    <a class="button button-secondary" href="history.php">
        View All Tests →
    </a>
</div>

    <!-- JavaScript inserts the assessment history returned by the API here. -->
    <div class="attempt-list" id="attemptList">
        <div class="empty-state">
            <p>Loading your practice history...</p>
        </div>
    </div>

</section>


        <!-- Keep the footer consistent and update the copyright year automatically. -->
        <footer class="site-footer">
            <span>© <?= date('Y') ?> ServiceReady</span>
            <span>Practise thoughtfully. Communicate confidently.</span>
        </footer>

    </main>
</body>
</html>
