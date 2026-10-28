# ServiceReady: Customer Service Practice & Assessment Platform

## 1. About the Project

ServiceReady is a full-stack web application that helps users practise written customer-service responses. Users can select a scenario, read a fictional customer message, write a response, submit it, and receive a score with criteria-level feedback.

The application demonstrates frontend development, PHP backend processing, MySQL database management, input validation, rule-based evaluation, and persistent assessment history.

## 2. Features

- Dashboard with a welcome section.
- Three customer-service practice scenarios.
- Delivery problems Management.
- Refund and Returns Processing.
- Technical Issue Resolution.
- Scenario-specific instructions and response forms.
- Server-side response evaluation.
- Weighted overall score calculation.
- Criteria-level feedback.
- Saved assessment results.
- Recent assessment history.
- Dark navy, charcoal, and teal interface- I prefer dark theme over light theme.
- PHP and MySQL database integration.

## 3. Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend processing and server-side validation |
| MySQL | Stores scenarios, criteria, attempts, and scores |
| PDO | Connects PHP to MySQL |
| HTML5 | Page structure |
| CSS3 | Styling and responsive layout |
| JavaScript | Client-side interactions |
| Apache | Local web server |
| XAMPP | Local PHP and MySQL development environment |
| Git and GitHub | Version control and source-code management |
| Visual Studio Code | Code editor |

## 4. Project Structure

```text
serviceready/
├── api/
│   └── submit-attempt.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── config/
│   ├── database.php
│   └── database.example.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── includes/
├── services/
├── index.php
├── assessment.php
├── results.php
├── README.md
└── .gitignore
```

Note: The folders and files listed above describe the intended project structure. Refer to the actual repository for the files currently implemented.

## 5. Application Workflow

1. The user opens the dashboard.
2. PHP retrieves active scenarios from MySQL.
3. The user selects a scenario.
4. The assessment page displays the customer message and instructions.
5. The user writes and submits a response.
6. PHP validates the submission.
7. The backend retrieves the relevant evaluation criteria.
8. The response is checked against configured keywords.
9. The backend calculates the criteria scores and weighted overall score.
10. The attempt and its criteria-level results are stored in MySQL.
11. The results page displays the submitted response, score, and feedback.
12. The dashboard can display recent assessments.

## 6. Installation Requirements

Install the following:

- XAMPP with Apache, PHP, MySQL/MariaDB, and phpMyAdmin.
- Visual Studio Code.
- Git.
- A web browser.

The original development environment uses Windows and XAMPP installed at `E:\xampp`.

## 7. Run the Project Locally

### Step 1: Clone the repository

Open PowerShell:

```powershell
cd E:\Projects
git clone YOUR_GITHUB_REPOSITORY_URL
```

Replace `YOUR_GITHUB_REPOSITORY_URL` with the repository's actual HTTPS URL.

If you already have the project locally, skip this step.

### Step 2: Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

Apache is configured to use port `8080` in the current development environment.

### Step 3: Make the project accessible to Apache

The project is stored at:

```text
E:\Projects\serviceready
```

The current setup uses a directory junction at:

```text
E:\xampp\htdocs\serviceready
```

If the junction does not already exist, open PowerShell and run:

```powershell
New-Item -ItemType Junction `
  -Path "E:\xampp\htdocs\serviceready" `
  -Target "E:\Projects\serviceready"
```

Do not run this command if the junction already exists.

### Step 4: Create the database

Open:

http://localhost:8080/phpmyadmin/

1. Select **New**.
2. Create a database named `serviceready`.
3. Select the new database.
4. Open the **Import** tab.
5. Import `database/schema.sql`.
6. Import `database/seed.sql` after the schema has been created.

The schema file must define the database tables and relationships. The seed file should contain the fictional scenarios and evaluation criteria.

### Step 5: Configure the database connection

Open `config/database.example.php`.

Use it as a template for your local file:

```text
config/database.php
```

Configure the database host, database name, username, and password for your local MySQL installation.

The PHP application expects the connection to be available as `$pdo`.

Do not commit real database credentials to GitHub.

### Step 6: Open the application

Open the dashboard in your browser:

http://localhost:8080/serviceready/

Use the dashboard to open a scenario, submit a response, and view its results.

## 8. Database Design

The application uses four main tables.

### Table 1: scenarios

Stores the available customer-service scenarios.

| Column | Meaning |
|---|---|
| `id` | Unique scenario ID and primary key |
| `title` | Scenario title |
| `category` | Scenario category |
| `customer_message` | Fictional customer's message |
| `instructions` | Instructions for the response |
| `is_active` | Determines whether the scenario is available |

### Table 2: criteria

Stores the evaluation criteria for each scenario.

| Column | Meaning |
|---|---|
| `id` | Unique criteria ID and primary key |
| `scenario_id` | ID of the associated scenario |
| `name` | Criteria name |
| `description` | Explanation of the criteria |
| `weight` | Relative importance of the criteria |
| `rule_type` | Evaluation rule type |
| `rule_value` | Configured keywords or phrases separated by semicolons |

### Table 3: attempts

Stores submitted responses and overall scores.

| Column | Meaning |
|---|---|
| `id` | Unique attempt ID and primary key |
| `scenario_id` | ID of the attempted scenario |
| `response_text` | User's submitted response |
| `total_score` | Calculated overall score |
| `created_at` | Date and time of submission |

### Table 4: attempt_scores

Stores the feedback and score for each criteria in an attempt.

| Column | Meaning |
|---|---|
| `id` | Unique criteria-result ID and primary key |
| `attempt_id` | ID of the submitted attempt |
| `criterion_id` | ID of the evaluated criteria |
| `score` | Criteria score |
| `feedback` | Explanation of detected or missing configured keywords |

### Table Relationships

- One scenario can have many criteria.
- One scenario can have many attempts.
- One attempt can have many criteria scores.
- One criteria can be used in many attempt scores.

Foreign keys connect these records and help maintain reference integrity.

## 9. How the Evaluation Works

ServiceReady currently uses rule-based keyword matching. It does not use an AI model to evaluate responses.

For each criteria:

```text
Criterion Score =
(Matched Keywords / Total Configured Keywords) × 100
```

The weighted overall score is:

```text
Overall Score =
Sum of (Criterion Score × Criterion Weight)
--------------------------------------------
Total Criterion Weight
```

The backend performs the evaluation and stores the result in MySQL.

### Limitations

Keyword matching does not fully understand meaning, context, empathy, or correctness. It may miss a valid response that uses different wording or reward a keyword used incorrectly.

Scores are intended as practice feedback, not as a validated hiring decision or a definitive measurement of communication quality.

## 10. Security and Validation

The application should:

- Validate submitted scenario IDs and response lengths on the server.
- Use PDO prepared statements for queries involving user input.
- Escape output before displaying user-submitted text.
- Calculate scores on the server rather than trusting frontend values.
- Save related database records in a transaction.
- Keep local database credentials out of the public repository.

The current local demonstration does not provide individual user accounts. It should not store real customer information or sensitive production data without additional security controls.

## 11. Testing Checklist

- [ ] Dashboard loads successfully.
- [ ] All active scenarios appear.
- [ ] Each scenario opens correctly.
- [ ] The customer message and instructions are displayed.
- [ ] A valid response can be submitted.
- [ ] Invalid input is rejected.
- [ ] The results page displays the correct attempt.
- [ ] Criteria-level scores and feedback are displayed.
- [ ] The submitted response is saved.
- [ ] The attempt is stored in MySQL.
- [ ] Criteria scores are stored in MySQL.
- [ ] Recent assessments appear on the dashboard.
- [ ] Database connection failures are handled appropriately.
- [ ] The layout works on smaller screens.

### Check PHP syntax

Run these commands from PowerShell:

```powershell
& "E:\xampp\php\php.exe" -l "E:\Projects\serviceready\index.php"
& "E:\xampp\php\php.exe" -l "E:\Projects\serviceready\assessment.php"
& "E:\xampp\php\php.exe" -l "E:\Projects\serviceready\results.php"
& "E:\xampp\php\php.exe" -l "E:\Projects\serviceready\api\submit-attempt.php"
```

Each command should report that no syntax errors were detected.

## 12. Future Improvements

Possible improvements include:

- User registration and login.
- Individual assessment histories.
- Improved response evaluation.
- Automated backend tests.
- Scenario and criteria management.
- Pagination and filtering for assessment history.
- Deployment to a PHP-compatible hosting environment.

These are future improvements unless they have already been implemented.

## 13. Git and GitHub

Check repository status:

```powershell
git status
```

Stage and commit documentation and configuration changes:

```powershell
git add README.md .gitignore config/database.example.php database/schema.sql database/seed.sql
git commit -m "docs: document ServiceReady setup and database"
git push origin main
```

Only stage files that exist and have been reviewed. If a file is not yet created, prepare and verify it before adding it.

Never commit passwords, private configuration, or sensitive data.

## 14. Summary

ServiceReady is a full-stack customer-service practice platform built using PHP, MySQL, HTML, CSS, and JavaScript.

Users select a scenario, write a customer-service response, and receive a weighted score with criteria-level feedback. PHP handles validation, database access, evaluation, and result retrieval, while MySQL stores the scenarios, criteria, attempts, and scores.

The project demonstrates relational database design, prepared statements, server-side processing, frontend/backend integration, and persistent data storage.

The main limitation is that the current keyword-based evaluation cannot understand the full meaning of a response. Future improvements could include individual user accounts, automated tests, and more advanced evaluation techniques.


