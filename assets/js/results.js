"use strict";

// Wait until the results page HTML has finished loading.
document.addEventListener("DOMContentLoaded", () => {

// GET THE ASSESSMENT ID

    // Read the assessment ID from the current page URL.
    // Example: results.php?id=15
    const urlParameters = new URLSearchParams(window.location.search);
    const attemptId = urlParameters.get("id");

    // Find the HTML elements used to display the assessment results.
    const scenarioTitle = document.getElementById("scenarioTitle");
    const totalScore = document.getElementById("totalScore");
    const totalScoreBar = document.getElementById("totalScoreBar");
    const submissionDate = document.getElementById("submissionDate");
    const submittedResponse = document.getElementById("submittedResponse");
    const criteriaFeedback = document.getElementById("criteriaFeedback");
    const practiceAgainLink = document.getElementById("practiceAgainLink");
    const resultsError = document.getElementById("resultsError");

// VALIDATE THE ASSESSMENT ID

    // Only accept a positive integer assessment ID.
    if (!attemptId || !/^[1-9]\d*$/.test(attemptId)) {
        showError("Please provide a valid assessment ID.");
        return;
    }

// LOAD THE ASSESSMENT RESULT FROM THE API

    async function loadAttempt() {

        try {
            // Request the selected assessment and its criterion feedback.
            const response = await fetch(
                `api/attempt.php?id=${encodeURIComponent(attemptId)}`
            );

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the API request succeeded.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load assessment results."
                );
            }

            // Store the assessment information returned by the API.
            const attempt = result.data;

            // Update the browser tab title.
            document.title = `Results: ${attempt.scenario_title} | ServiceReady`;

            // Display the scenario title.
            scenarioTitle.textContent = attempt.scenario_title;

            // Convert the overall score to a number and keep it between 0 and 100.
            const score = Math.max(
                0,
                Math.min(100, Number(attempt.total_score) || 0)
            );

            // Display the overall score with one decimal place.
            totalScore.replaceChildren(
                document.createTextNode(score.toFixed(1)),
                createScorePercent()
            );

            // Update the visual progress bar.
            totalScoreBar.style.width = `${score}%`;

            // Format the saved submission date for display.
            const submittedAt = new Date(
                String(attempt.created_at).replace(" ", "T")
            );

            const formattedDate = Number.isNaN(submittedAt.getTime())
                ? attempt.created_at
                : submittedAt.toLocaleString("en-IN", {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                    hour: "numeric",
                    minute: "2-digit"
                });

            // Display the submission date and scoring explanation.
            submissionDate.textContent =
                `Submitted ${formattedDate}. This score reflects configured keyword matches, not a complete judgement of communication quality.`;

            // Display the original submitted response.
            submittedResponse.textContent = attempt.response_text;

            // Update the link so the user can practise the same scenario again.
            practiceAgainLink.href =
                `assessment.php?id=${encodeURIComponent(attempt.scenario_id)}`;

            practiceAgainLink.removeAttribute("aria-disabled");

            // Display the individual criterion scores and feedback.
            displayCriteria(attempt.scores);

        } catch (error) {

            // Log technical details for debugging.
            console.error("Assessment results loading error:", error);

            // Show a friendly error message.
            showError(error.message);
        }
    }

// CREATE THE SCORE PERCENTAGE ELEMENT

    function createScorePercent() {

        // Create a span for the percentage symbol.
        const percent = document.createElement("span");

        percent.style.fontSize = "24px";
        percent.textContent = "%";

        return percent;
    }

// DISPLAY INDIVIDUAL CRITERION FEEDBACK

    function displayCriteria(scores) {

        // Remove the initial loading message.
        criteriaFeedback.replaceChildren();

        // Show a fallback if no criterion scores were returned.
        if (!Array.isArray(scores) || scores.length === 0) {
            const emptyState = document.createElement("div");
            emptyState.className = "empty-state";

            const message = document.createElement("p");
            message.textContent =
                "No criteria-level feedback was saved for this attempt.";

            emptyState.appendChild(message);
            criteriaFeedback.appendChild(emptyState);

            return;
        }

        // Create a feedback card for each saved criterion score.
        scores.forEach((item) => {

            // Convert the criterion score to a number and constrain it to 0–100.
            const score = Math.max(
                0,
                Math.min(100, Number(item.score) || 0)
            );

            // Create the main feedback card.
            const card = document.createElement("article");

            card.className = "scenario-card";

            card.style.minHeight = "auto";
            card.style.border = "0";
            card.style.borderBottom = "1px solid var(--border)";
            card.style.borderRadius = "0";

            // Create the row containing the criterion name and score.
            const headingRow = document.createElement("div");

            headingRow.style.display = "flex";
            headingRow.style.justifyContent = "space-between";
            headingRow.style.alignItems = "flex-start";
            headingRow.style.gap = "16px";

            // Create the section containing the criterion name and description.
            const details = document.createElement("div");

            const title = document.createElement("h3");
            title.textContent = item.name;

            const description = document.createElement("p");

            description.className = "scenario-description";
            description.style.display = "block";
            description.style.marginBottom = "12px";
            description.textContent = item.description || "";

            details.append(title, description);

            // Display the criterion score as a percentage.
            const scoreLabel = document.createElement("strong");

            scoreLabel.style.color = "var(--accent)";
            scoreLabel.style.whiteSpace = "nowrap";
            scoreLabel.textContent = `${score.toFixed(0)}%`;

            headingRow.append(details, scoreLabel);

            // Create the background of the criterion progress bar.
            const progressBackground = document.createElement("div");

            progressBackground.style.height = "6px";
            progressBackground.style.overflow = "hidden";
            progressBackground.style.borderRadius = "99px";
            progressBackground.style.background = "var(--border)";

            // Create the filled portion of the progress bar.
            const progressBar = document.createElement("div");

            progressBar.style.width = `${score}%`;
            progressBar.style.height = "100%";
            progressBar.style.background = "var(--accent)";

            progressBackground.appendChild(progressBar);

            // Display the feedback generated during assessment submission.
            const feedback = document.createElement("p");

            feedback.style.margin = "13px 0 0";
            feedback.style.color = "var(--text-secondary)";
            feedback.style.fontSize = "13px";
            feedback.textContent = item.feedback || "";

            // Add all elements to the feedback card.
            card.append(headingRow, progressBackground, feedback);

            // Add the completed card to the results page.
            criteriaFeedback.appendChild(card);
        });
    }

// DISPLAY API ERRORS

    function showError(message) {

        // Display a friendly error message.
        resultsError.textContent = message;
        resultsError.hidden = false;

        // Remove the loading placeholders.
        scenarioTitle.textContent = "Results unavailable";
        totalScore.textContent = "--";
        submissionDate.textContent = "";
        submittedResponse.textContent =
            "The assessment response could not be retrieved.";

        // Show an error in the criterion feedback area.
        criteriaFeedback.replaceChildren();

        const errorMessage = document.createElement("p");
        errorMessage.textContent =
            "Please return to the dashboard and check your assessment history.";

        criteriaFeedback.appendChild(errorMessage);

        // Do not allow the user to repeat an unknown scenario.
        practiceAgainLink.setAttribute("aria-disabled", "true");
        practiceAgainLink.removeAttribute("href");
    }

// START LOADING THE ASSESSMENT
    loadAttempt();
});
