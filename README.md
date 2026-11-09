# ServiceReady

ServiceReady is a web-based customer-service practice application. Users can practise customer-service situations, submit written responses, receive keyword-based scores and review their previous attempts. A simple admin panel is included to create and update practice tests.

## 1. Tutorial Video

**ServiceReady setup and application walkthrough:** 

The tutorial video will demonstrate the project setup and explain how to use the public application and admin panel.

## 2. Project Features

### Public application

- Dark-themed, responsive dashboard.
- Welcome section and practice overview cards.
- List of available practice scenarios.
- Scenario instructions and customer messages.
- Written response form with a character counter.
- Automatic response evaluation and overall score.
- Criterion-level scores and feedback.
- Recent practice attempts on the dashboard.
- Full practice history page.

### Admin panel

- Single admin login.
- Show/hide password option on the login page.
- Session-based access protection and logout.
- View all tests, including active and inactive tests.
- Add new tests.
- Automatically add three default evaluation criteria to each new test.
- Edit test title, category, customer message, instructions and active status.
- Activate or deactivate tests by changing their status in the Edit Test page.
- Inactive tests are hidden from the public practice dashboard.

## 3. Technologies Used

- **HTML5** — page structure and forms.
- **CSS3** — layout, dark theme and responsive design.
- **JavaScript** — page interactions, API requests and dynamic content.
- **PHP** — backend logic, API endpoints, login and admin operations.
- **MySQL / MariaDB** — stores scenarios, criteria, attempts, scores and admin details.
- **PDO** — PHP database connection and prepared SQL statements.
- **JSON** — data format used by the frontend and PHP API endpoints.
- **Apache** — local web server provided by XAMPP.
- **Git and GitHub** — optional version control and source-code hosting.

TypeScript, Node.js, npm and Composer are not required to run the current version of this project.

## 4. Requirements and Installation

### Software to install

1. **XAMPP** — https://www.apachefriends.org/download.html 
   - Provides Apache, PHP, MySQL/MariaDB and phpMyAdmin for local development.

Install XAMPP first. During installation, make sure Apache, PHP, MySQL and phpMyAdmin are included.

## 5. Project Setup

The following instructions use the Windows setup used during this project. The XAMPP installation folder is `E:\xampp`, and Apache is configured to use port `8080`.

### Step 1: Place the project in the web folder

- Extract the project ZIP.
- The project folder should be named `serviceready`.
- Place it inside XAMPP's `htdocs` folder.
- Example location: `E:\xampp\htdocs\serviceready`.

If you keep your working project in `E:\Projects\serviceready`, you can connect it to the XAMPP web folder using a Windows directory junction. Open PowerShell and run this command once:

```powershell
New-Item -ItemType Junction -Path "E:\xampp\htdocs\serviceready" -Target "E:\Projects\serviceready"
```

Only create the junction if `E:\xampp\htdocs\serviceready` does not already exist. If it already exists, do not run the command again.

### Step 2: Open the XAMPP Control Panel

If you cannot find the XAMPP Control Panel shortcut, open PowerShell and run:

```powershell
Start-Process "E:\xampp\xampp-control.exe"
```

This opens the XAMPP Control Panel.

- Click **Start** beside Apache.
- Click **Start** beside MySQL.
- Wait until both services show as running.

Keep Apache and MySQL running while using ServiceReady locally.

### Step 3: Open phpMyAdmin and create the database

- Open your browser and go to `http://localhost:8080/phpmyadmin/`.
- Create a database named `serviceready`.
- Select the `serviceready` database.
- Open the **Import** tab and import `database/schema.sql`.

The schema file creates the four main application tables: `scenarios`, `criteria`, `attempts` and `attempt_scores`.

### Step 4: Import the sample data

- In phpMyAdmin, select the `serviceready` database.
- Open the **Import** tab and choose `database/seed.sql`.
- The seed file contains the three original practice scenarios and their evaluation criteria.

**Important:** In the supplied `seed.sql`, the criteria insert statements appear before the scenario insert statements. Since criteria refer to scenarios through a foreign key, importing the file into a fresh database may fail. If that happens, move the complete `INSERT INTO scenarios` statement above the `INSERT INTO criteria` statement in `seed.sql`, save the file, and import it again. The scenario records must be inserted before their related criteria.

### Step 5: Configure the database connection

- Open the project's `config` folder.
- Copy `database.example.php` and rename the copy to `database.php`.
- Open `config/database.php` and check the database settings.

For the default local XAMPP configuration, use:

- Host: `localhost`
- Database: `serviceready`
- Username: `root`
- Password: blank, unless you configured a MySQL password in XAMPP.

The `config/database.php` file is for local credentials and is excluded from Git. Do not commit it or share it publicly.

### Step 6: Create the admin table and admin login

The supplied `database/schema.sql` creates the four application tables, but the admin table is created separately. Run the following SQL in phpMyAdmin after selecting the `serviceready` database:

```sql
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Create a password hash using PHP rather than storing a plain-text password:

1. Temporarily create `generate-password.php` in the project root.
2. Add the following code and replace the sample password with the password you want to use:

```php
<?php
echo password_hash('your-admin-password', PASSWORD_DEFAULT);
```

3. Open `http://localhost:8080/serviceready/generate-password.php` in your browser.
4. Copy the generated hash.
5. In phpMyAdmin, run this query. Replace the hash placeholder with the hash you copied:

```sql
INSERT INTO admins (email, password_hash, is_active)
VALUES ('admin@serviceready.com', 'PASTE_GENERATED_HASH_HERE', 1);
```

6. Delete `generate-password.php` after creating the hash.

The admin login verifies the password using PHP's `password_verify()` function. Do not put the admin password or generated hash in this README or in Git.

### Step 7: Open ServiceReady

With Apache and MySQL running, open:

- **Public application:** `http://localhost:8080/serviceready/`
- **Admin login:** `http://localhost:8080/serviceready/admin/login.php`
- **phpMyAdmin:** `http://localhost:8080/phpmyadmin/`

If your Apache installation uses a different port, replace `8080` in the URLs with your configured port.

## 6. What to Do After Setup

- Open the public application and check that the active practice scenarios are displayed.
- Open a scenario, read the customer message and instructions, and submit a sample response.
- Check that the result page displays the overall score and criterion-level feedback.
- Open **My progress** to view saved attempts.
- Open the admin login page and sign in.
- Use **Manage Tests** to view existing tests, add a new test or edit an existing test.
- Change a test to Inactive and confirm that it is hidden from the public practice page.
- Change it back to Active and confirm that it appears again.

## 7. Application Workflow

### Public application workflow

1. The user opens the ServiceReady dashboard.
2. The frontend JavaScript requests the available active scenarios from the PHP API.
3. The user selects a scenario and opens the assessment page.
4. The frontend requests the selected scenario and its evaluation criteria from the API.
5. The user writes a response and submits it.
6. The PHP submission API validates the response and loads the scenario's criteria from the database.
7. The backend checks the response against the configured keywords and calculates criterion scores and a weighted overall score.
8. The attempt and criterion scores are saved in the database.
9. The frontend opens the result page and retrieves the saved result from the API.
10. The user can review the result and view previous attempts on the history page.

### Admin panel workflow

1. The admin opens the admin login page and enters the registered email and password.
2. PHP checks the admin record and verifies the password hash.
3. A successful login creates an admin session and opens Manage Tests.
4. The admin can view all tests, add a new test or edit an existing test.
5. When a new test is added, three default evaluation criteria are created automatically.
6. The admin can set a test as Active or Inactive from the Edit Test page.
7. Active tests are available to public users; inactive tests are not shown in the practice list.
8. The admin logs out to end the session.

## 8. Project Structure and File Overview

```text
serviceready/
│
├── admin/
│   ├── assets/
│   │   ├── css/
│   │   │   └── admin.css
│   │   └── js/
│   │       └── admin-login.js
│   ├── add-test.php
│   ├── edit-test.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── manage-tests.php
│
├── api/
│   ├── attempt.php
│   ├── attempts.php
│   ├── scenario.php
│   ├── scenarios.php
│   └── submit-attempt.php
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
│   ├── database.example.php
│   └── database.php  (create this local file)
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── assessment.php
├── db-test.php
├── history.php
├── index.php
├── results.php
├── test.php
├── .gitignore
└── README.md
```

### Main PHP pages

- `index.php` — public dashboard, welcome section, summary cards, practice scenarios and recent attempts.
- `assessment.php` — page where the user reads a scenario and writes a response.
- `results.php` — page that displays a saved assessment result.
- `history.php` — page that displays the user's saved practice attempts.

### API files

The PHP files in `api/` provide backend endpoints. They receive requests from the frontend and return data in JSON format.

- `scenarios.php` — returns the active practice scenarios.
- `scenario.php` — returns one active scenario and its criteria.
- `submit-attempt.php` — validates and evaluates a response, calculates scores and saves the attempt.
- `attempt.php` — returns one saved attempt and its criterion scores.
- `attempts.php` — returns the attempt count and recent attempts, or the full attempt history when requested.

### Frontend files

- `assets/css/style.css` — public application's styling, layout, colours, forms, cards and responsive rules.
- `assets/js/app.js` — dashboard behaviour, navigation and loading of practice scenarios.
- `assets/js/assessment.js` — loads a selected scenario, handles the character counter and submits the response.
- `assets/js/results.js` — loads and displays the saved assessment result.
- `assets/js/history.js` — loads the dashboard's attempt count and recent attempts.
- `assets/js/history-page.js` — loads and displays the full practice history.

### Admin panel files

- `admin/login.php` — admin login form and password verification.
- `admin/index.php` — checks the admin session and redirects to Manage Tests.
- `admin/logout.php` — ends the admin session and redirects to the login page.
- `admin/manage-tests.php` — lists all tests, including active and inactive tests.
- `admin/add-test.php` — creates a test and automatically adds three default evaluation criteria.
- `admin/edit-test.php` — updates test details and active/inactive status. Existing criteria are not changed by this page.
- `admin/assets/css/admin.css` — styling for the admin login and admin pages.
- `admin/assets/js/admin-login.js` — controls the password show/hide button.

### Configuration and database files

- `config/database.php` — local database connection settings; create it from the example file.
- `config/database.example.php` — example PDO database connection settings.
- `database/schema.sql` — creates the four main application tables and their relationships.
- `database/seed.sql` — inserts the three original practice scenarios and their evaluation criteria.
- `.gitignore` — excludes local credentials, temporary test files and other files that should not be committed.
- `test.php` and `db-test.php` — temporary development/testing files; they are not required to run the application and should be removed or kept out of a public deployment.

## 9. Database Overview

The project uses MySQL/MariaDB. The main application has four tables:

- `scenarios` — stores test titles, categories, customer messages, instructions and active status.
- `criteria` — stores each scenario's evaluation criteria, weights, rule types and keywords.
- `attempts` — stores submitted responses, overall scores and submission dates.
- `attempt_scores` — stores the score and feedback for each criterion in an attempt.

The admin panel also uses an `admins` table to store the admin email, password hash, account status and creation date.

### Table relationships

- One scenario can have multiple criteria.
- One scenario can have multiple attempts.
- One attempt can have multiple criterion scores.
- Each criterion score is linked to an attempt and an evaluation criterion.

## 10. Scoring Method

ServiceReady currently uses rule-based keyword matching, not AI evaluation.

- Each scenario has configured evaluation criteria.
- Each criterion has a weight and a list of keywords.
- The backend checks which configured keywords appear in the submitted response.
- A criterion score is calculated from the proportion of matched keywords.
- The overall score is calculated using the weighted average of the criterion scores.
- The result page displays the overall score and criterion-level feedback.

This method checks for configured words and phrases. It does not understand the full meaning, accuracy, completeness or empathy of a response. Scores should be treated as practice guidance.

## 11. Future Scope

### Public application

- User accounts and individual practice profiles.
- More practice scenarios and categories.
- Search and filtering for scenarios.
- Improved progress charts and performance summaries.
- More detailed and helpful feedback.
- AI-assisted evaluation of response meaning, tone and quality.

### Admin panel

- Admin interface for editing evaluation criteria.
- View, search and filter submitted practice attempts.
- View detailed user responses and assessment scores.
- Dashboard statistics for tests and practice activity.
- Export test and assessment data.

## 12. Project Summary

ServiceReady is a PHP and MySQL/MariaDB web application for practising customer-service communication. The public application lets users respond to practice scenarios, receive keyword-based scores and review their progress. The admin panel provides a simple way to add tests, edit test details and control which tests are available to users. The project uses HTML, CSS, JavaScript, PHP, JSON APIs and a relational database, and can be run locally using XAMPP.
