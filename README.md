# ServiceReady

ServiceReady is a web application for practising customer-service
responses. Users choose a practice scenario, read the customer's message
and the scenario instructions, write a response, submit it for
rule-based evaluation, and review the saved result. The application also
keeps an assessment history so users can revisit previous attempts.

## Features

-   Dashboard showing active practice scenarios.
-   Scenario details including the customer message and response
    instructions.
-   Written response form with a live character counter.
-   Server-side validation of submitted responses.
-   Rule-based keyword evaluation using scenario-specific criteria and
    weights.
-   Overall score and criterion-level feedback.
-   Saved assessment attempts in the database.
-   Recent assessment list on the dashboard.
-   Full assessment history page.
-   Responsive layout and mobile navigation.

> **Scoring note:** The current evaluator uses configured keyword
> matching. It does not use an AI model or understand the meaning of a
> response. A keyword may be detected without proving that the response
> is accurate, complete, empathetic or appropriate. Treat scores as
> practice guidance, not as a hiring decision.

## Technology

-   **PHP 8+** --- server-rendered page shells and backend API
    endpoints.
-   **MySQL / MariaDB** --- persistent storage for scenarios, criteria
    and assessment attempts.
-   **PDO** --- PHP database access using prepared statements.
-   **HTML** --- page structure.
-   **CSS** --- styling and responsive layout.
-   **JavaScript** --- browser interactions, API requests, form
    submission and dynamic rendering.
-   **XAMPP** --- local Apache, PHP and MySQL/MariaDB development
    environment.

## Application workflow

1.  The browser opens `index.php`, which displays the dashboard
    structure.
2.  `assets/js/app.js` loads the active scenarios from
    `api/scenarios.php` and renders the scenario cards.
3.  `assets/js/history.js` retrieves the attempt count and latest five
    attempts from `api/attempts.php`.
4.  When a user selects a scenario, the browser opens
    `assessment.php?id=SCENARIO_ID`.
5.  `assets/js/assessment.js` reads the scenario ID from the URL and
    requests `api/scenario.php?id=SCENARIO_ID`.
6.  The API returns the active scenario and its configured criteria.
    JavaScript displays the customer message, instructions and scenario
    title.
7.  The user writes a response. JavaScript updates the character count
    and handles the form submission.
8.  JavaScript sends a JSON `POST` request to `api/submit-attempt.php`.
    The endpoint validates the request and response, loads the scenario
    criteria, evaluates the response, calculates the weighted score and
    saves the attempt.
9.  The endpoint stores the overall attempt in `attempts` and the
    criterion-level results in `attempt_scores` within a database
    transaction.
10. After a successful submission, the browser navigates to
    `results.php?id=ATTEMPT_ID`.
11. `assets/js/results.js` requests `api/attempt.php?id=ATTEMPT_ID` and
    displays the saved score, submitted response and criterion feedback.
12. The dashboard displays the latest five attempts. `history.php` uses
    `assets/js/history-page.js` and `api/attempts.php?all=1` to display
    all saved attempts.

## Project structure

``` text
serviceready/
│
├── index.php
├── assessment.php
├── results.php
├── history.php
│
├── api/
│   ├── scenarios.php
│   ├── scenario.php
│   ├── submit-attempt.php
│   ├── attempt.php
│   └── attempts.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── app.js
│       ├── assessment.js
│       ├── history.js
│       ├── history-page.js
│       └── results.js
│
├── config/
│   ├── database.php
│   └── database.example.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── includes/
├── services/
├── .gitignore
└── README.md
```

### Root PHP pages

  -----------------------------------------------------------------------
  File                                Responsibility
  ----------------------------------- -----------------------------------
  `index.php`                         Main dashboard. Provides the
                                      scenario grid, summary statistics
                                      and recent-attempt area. JavaScript
                                      fills these areas with API data.

  `assessment.php`                    Practice page shell. Displays the
                                      selected scenario context and
                                      response form. The scenario is
                                      loaded using the ID in the URL.

  `results.php`                       Results page shell. JavaScript
                                      retrieves a saved attempt and
                                      displays its overall score,
                                      original response and criterion
                                      feedback.

  `history.php`                       Full assessment history page shell.
                                      JavaScript retrieves and displays
                                      all saved attempts.
  -----------------------------------------------------------------------

### API endpoints

All API endpoints are PHP files under `api/`. They communicate with the
browser using JSON.

  --------------------------------------------------------------------------
  Endpoint                   Method / parameters     Responsibility
  -------------------------- ----------------------- -----------------------
  `api/scenarios.php`        `GET`                   Returns active
                                                     scenarios for the
                                                     dashboard.

  `api/scenario.php`         `GET`, `id` query       Validates the scenario
                             parameter               ID and returns one
                                                     active scenario with
                                                     its criteria.

  `api/submit-attempt.php`   `POST` with JSON body   Validates a submitted
                                                     response, evaluates it,
                                                     saves the attempt and
                                                     criterion scores, and
                                                     returns the saved
                                                     attempt ID and score.

  `api/attempt.php`          `GET`, `id` query       Returns one saved
                             parameter               attempt with scenario
                                                     details and
                                                     criterion-level
                                                     results.

  `api/attempts.php`         `GET`; optional `all=1` Returns the attempt
                                                     count and recent
                                                     attempts. By default,
                                                     the list is limited to
                                                     the latest five. With
                                                     `all=1`, it returns all
                                                     attempts.
  --------------------------------------------------------------------------

### Frontend assets

  -----------------------------------------------------------------------
  File                                Responsibility
  ----------------------------------- -----------------------------------
  `assets/css/style.css`              Application colours, typography,
                                      layout, cards, forms, navigation,
                                      buttons and responsive styling.

  `assets/js/app.js`                  Shared navigation behaviour and
                                      dashboard scenario loading. It
                                      requests active scenarios and
                                      builds scenario cards in the
                                      browser.

  `assets/js/assessment.js`           Loads one scenario, updates the
                                      response character count, submits
                                      the response as JSON and navigates
                                      to the results page.

  `assets/js/history.js`              Loads the attempt count and latest
                                      five attempts for the dashboard.

  `assets/js/history-page.js`         Loads all attempts for the full
                                      history page.

  `assets/js/results.js`              Loads a saved attempt and renders
                                      its overall score, submitted
                                      response and criterion feedback.
  -----------------------------------------------------------------------

### Configuration and database files

  -----------------------------------------------------------------------
  File                                Responsibility
  ----------------------------------- -----------------------------------
  `config/database.php`               Private environment-specific
                                      database connection configuration
                                      used by the application. It creates
                                      the PDO connection. This file
                                      should not be committed to a public
                                      repository.

  `config/database.example.php`       Example connection configuration
                                      showing the settings required to
                                      create a local `database.php`.

  `database/schema.sql`               Creates the four application
                                      tables, primary keys, indexes,
                                      auto-increment IDs and foreign-key
                                      relationships.

  `database/seed.sql`                 Inserts the sample scenarios and
                                      their evaluation criteria.
  -----------------------------------------------------------------------

The `includes/` and `services/` directories are reserved for reusable
PHP components and application services. They do not currently contain
application source files.

## Database design

The application uses four tables:

  -----------------------------------------------------------------------
  Table                               Purpose
  ----------------------------------- -----------------------------------
  `scenarios`                         Stores scenario title, category,
                                      customer message, instructions and
                                      active status.

  `criteria`                          Stores the criteria used to
                                      evaluate a scenario, including
                                      name, description, weight, rule
                                      type and rule value.

  `attempts`                          Stores the submitted response,
                                      scenario ID, overall score and
                                      submission timestamp.

  `attempt_scores`                    Stores the score and feedback for
                                      each criterion evaluated in an
                                      attempt.
  -----------------------------------------------------------------------

### Relationships

-   One scenario can have multiple criteria. `criteria.scenario_id`
    references `scenarios.id`.
-   One scenario can have multiple assessment attempts.
    `attempts.scenario_id` references `scenarios.id`.
-   One attempt can have multiple criterion-score records.
    `attempt_scores.attempt_id` references `attempts.id`.
-   Each criterion-score record refers to a criterion.
    `attempt_scores.criterion_id` references `criteria.id`.

## How the scoring works

The current scoring rules are configured in the `criteria` table. The
sample criteria use `rule_type = 'keyword'` and a semicolon-separated
list of keywords in `rule_value`.

For each criterion:

1.  The response is normalised to lowercase.

2.  The criterion's keyword list is split using semicolons.

3.  The evaluator checks which configured keywords occur in the
    response.

4.  The criterion score is calculated as:

    `criterion score = (matched keywords / configured non-empty keywords) × 100`

5.  The criterion score is combined with the criterion weight to
    calculate the overall weighted score:

    `overall score = sum(criterion score × criterion weight) / sum(criterion weights)`

The criterion results and overall score are stored when the attempt is
submitted. Keyword matching is a simple rule-based method; it does not
assess the meaning of the response.

## Local setup with XAMPP (Windows)

The local development setup used for this project is:

-   Project folder: `E:\Projects\serviceready`
-   XAMPP folder: `E:\xampp`
-   XAMPP Control Panel: `E:\xampp\xampp-control.exe`
-   Apache port: `8080`
-   Database name: `serviceready`
-   PHP executable: `E:\xampp\php\php.exe`

### 1. Start the XAMPP Control Panel from PowerShell

Open PowerShell and run:

``` powershell
Start-Process "E:\xampp\xampp-control.exe"
```

The XAMPP Control Panel opens. In the panel, click **Start** for:

-   **Apache**
-   **MySQL**

Wait until both modules show that they are running. The database service
used here is MySQL/MariaDB.

### 2. Make the project available to Apache

The project is stored at `E:\Projects\serviceready` and is served
through this directory junction:

``` text
E:\xampp\htdocs\serviceready  →  E:\Projects\serviceready
```

This allows Apache to serve the project from its `htdocs` directory
while the working project files remain in `E:\Projects\serviceready`.

If setting up the same arrangement on another Windows computer, create
the junction from a terminal with the required permissions:

``` powershell
New-Item -ItemType Junction `
  -Path "E:\xampp\htdocs\serviceready" `
  -Target "E:\Projects\serviceready"
```

If the junction already exists, do not create it again.

### 3. Open the application

With Apache and MySQL running, open:

-   **ServiceReady:** <http://localhost:8080/serviceready/>
-   **phpMyAdmin:** <http://localhost:8080/phpmyadmin/>

Apache is configured for port `8080` in this local setup. If the port is
changed, update the browser URL to match the Apache configuration.

### 4. Create the database

1.  Open phpMyAdmin.
2.  Create a database named `serviceready`.
3.  Use `utf8mb4` character encoding/collation where the interface asks
    for it.
4.  Select the `serviceready` database.
5.  Open the **Import** tab.
6.  Import `database/schema.sql` to create the tables.
7.  Import `database/seed.sql` to insert the sample scenarios and
    criteria.

**Important seed import order:** The current `database/seed.sql` file
inserts rows into `criteria` before inserting the referenced rows into
`scenarios`. Because `criteria.scenario_id` has a foreign key to
`scenarios.id`, a fresh import with foreign-key checks enabled can fail
at the criteria insert. Before importing into a new database, ensure the
scenario INSERT runs before the criteria INSERT (or otherwise arrange a
valid parent-before-child import). The intended sample data contains
three scenarios and nine criteria.

### 5. Configure the database connection

The application reads its PDO connection from `config/database.php`.

Use `config/database.example.php` as a template. Create
`config/database.php` and set the values for the local MySQL/MariaDB
service:

``` php
<?php

$host = '127.0.0.1';
$dbname = 'serviceready';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    throw new RuntimeException('Database connection failed.');
}
```

Use the username and password configured in your own XAMPP database. The
values above are an example for a default local setup; they are not
appropriate for a public production server.

`config/database.php` is excluded by `.gitignore`. Keep real database
credentials private and do not commit them.

### 6. Stop the local services

When you finish working, return to the XAMPP Control Panel and click
**Stop** for MySQL and Apache.

## Running the application

1.  Start XAMPP using the PowerShell command above.
2.  Start Apache and MySQL in the XAMPP Control Panel.
3.  Confirm the `serviceready` database and tables exist.
4.  Open `http://localhost:8080/serviceready/`.
5.  Select a practice scenario.
6.  Read the customer message and instructions.
7.  Enter a response between 10 and 5000 characters.
8.  Submit the response.
9.  Review the overall score and criterion feedback.
10. Return to the dashboard to see the latest attempts, or open **View
    All Tests** to see the complete history.

## Validation and data handling

-   The browser provides a character counter and basic form constraints.
-   The submit API validates the request on the server as well.
    Browser-side checks alone are not trusted.
-   The API uses PDO prepared statements for database values.
-   The attempt and its criterion-score rows are saved in a database
    transaction so the related records are committed together.
-   API endpoints return JSON for the frontend to process.
-   The frontend uses JavaScript to render API data into the page.

## Production deployment considerations

This project is currently structured for local practice and development.
Before exposing it publicly:

-   Use HTTPS.
-   Create a dedicated database and restricted database user; do not use
    a privileged local root account.
-   Store credentials in secure environment-specific configuration
    outside the public web root where practical.
-   Add authentication and authorisation if attempts must be private or
    associated with individual users.
-   Check access permissions for every attempt lookup; do not expose
    private submitted responses to unauthorised visitors.
-   Add CSRF protection to state-changing requests where cookie-based
    sessions are used.
-   Add rate limiting and abuse protection to submission endpoints.
-   Keep detailed exception messages in server logs and show generic
    errors to visitors.
-   Remove temporary diagnostic scripts such as `test.php` and
    `db-test.php` from the public web root.
-   Configure automated database backups and test the restore process.
-   Test the complete scenario, submission, results and history flow in
    the production environment.
-   Add automated tests for validation, scoring edge cases and database
    failures.

## Troubleshooting

  -----------------------------------------------------------------------
  Problem                             Things to check
  ----------------------------------- -----------------------------------
  The site cannot be opened           Confirm Apache is running, port
                                      `8080` is correct, and the
                                      `serviceready` folder/junction
                                      exists under `htdocs`.

  Database connection fails           Confirm MySQL is running, the
                                      database exists, and
                                      `config/database.php` has the
                                      correct host, database name,
                                      username and password.

  No scenarios appear                 Confirm the `scenarios` table
                                      contains active records and check
                                      the browser Network tab for
                                      `api/scenarios.php` errors.

  A scenario does not load            Check the scenario ID in the URL,
                                      whether the scenario is active and
                                      the response from
                                      `api/scenario.php`.

  Submission fails                    Check the browser Network tab,
                                      request JSON, server validation
                                      response, criteria configuration
                                      and PHP error log.

  Result does not appear              Check the attempt ID in the URL and
                                      the response from
                                      `api/attempt.php`.

  History is empty                    Confirm that rows exist in
                                      `attempts` and check
                                      `api/attempts.php` or
                                      `api/attempts.php?all=1`.
  -----------------------------------------------------------------------

For browser-side problems, open Developer Tools and inspect the
**Console** and **Network** tabs. For server-side problems, inspect the
Apache/PHP error log.

## Git and repository hygiene

Before committing or sharing the project:

``` powershell
git status
git diff
```

Review the changes, then stage only the intended files and commit them
with a clear message. Confirm that private configuration and local test
files are not tracked:

``` powershell
git status --ignored
```

The `.gitignore` file excludes `config/database.php`, environment files,
temporary test scripts, editor/OS files, logs and local database backup
files. Never commit real passwords, API keys or private customer data.

## Current scope

ServiceReady currently provides scenario browsing, written response
submission, rule-based evaluation, saved results, recent attempts and
full attempt history. The `includes/` and `services/` directories are
reserved for future reusable PHP components and services. There is
currently no admin panel or user-account system described in this
application workflow.
