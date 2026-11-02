"use strict";

// Wait until the history page HTML has finished loading.
document.addEventListener("DOMContentLoaded", () => {

    // Find the elements used to display assessment history.
    const historyList = document.getElementById("historyList");
    const historyCount = document.getElementById("historyCount");
    const historyError = document.getElementById("historyError");

// LOAD ALL ASSESSMENT ATTEMPTS

    async function loadAllAttempts() {

        try {
            // Request all saved attempts using the API's all=1 option.
            const response = await fetch("api/attempts.php?all=1");

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the API request succeeded.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load assessment history."
                );
            }

            // Display the total number of saved attempts.
            historyCount.textContent = `${result.count} attempts`;

            // Remove the initial loading message.
            historyList.replaceChildren();

            // Display a message when no assessments have been completed.
            if (!Array.isArray(result.data) || result.data.length === 0) {
                displayEmptyHistory();
                return;
            }

            // Create one history row for every returned attempt.
            result.data.forEach((attempt) => {
                const attemptCard = createAttemptCard(attempt);
                historyList.appendChild(attemptCard);
            });

        } catch (error) {

            // Log technical details for debugging.
            console.error("Full assessment history error:", error);

            // Display a friendly error message.
            historyCount.textContent = "Unavailable";
            historyError.textContent =
                "Unable to load your assessment history. Please refresh and try again.";

            historyError.hidden = false;

            // Display an error message in the history list.
            historyList.replaceChildren();

            const errorMessage = document.createElement("div");
            errorMessage.className = "empty-state";

            const heading = document.createElement("h3");
            heading.textContent = "History unavailable";

            const description = document.createElement("p");
            description.textContent =
                "Please check your connection and try again.";

            errorMessage.append(heading, description);
            historyList.appendChild(errorMessage);
        }
    }

 // CREATE A HISTORY CARD

    function createAttemptCard(attempt) {

        // Create the main assessment history row.
        const card = document.createElement("article");
        card.className = "attempt-row";

        // Create the completion icon.
        const icon = document.createElement("div");
        icon.className = "attempt-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.textContent = "✓";

        // Create the scenario details container.
        const details = document.createElement("div");
        details.className = "attempt-details";

        // Display the scenario name.
        const title = document.createElement("h3");
        title.textContent = attempt.scenario_title;

        // Format the date and time of the assessment.
        const date = document.createElement("p");

        const submittedAt = new Date(
            String(attempt.created_at).replace(" ", "T")
        );

        date.textContent = Number.isNaN(submittedAt.getTime())
            ? attempt.created_at
            : submittedAt.toLocaleString("en-IN", {
                day: "numeric",
                month: "short",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit"
            });

        details.append(title, date);

        // Create the overall score container.
        const scoreContainer = document.createElement("div");
        scoreContainer.className = "attempt-score";

        const scoreLabel = document.createElement("span");
        scoreLabel.textContent = "Rule-based score";

        // Convert the score to a number and keep it between 0 and 100.
        const score = Math.max(
            0,
            Math.min(100, Number(attempt.total_score) || 0)
        );

        const scoreValue = document.createElement("strong");
        scoreValue.textContent = `${score.toFixed(1)}%`;

        scoreContainer.append(scoreLabel, scoreValue);

        // Create a link to the detailed result for this attempt.
        const resultLink = document.createElement("a");

        resultLink.className = "text-link";
        resultLink.href =
            `results.php?id=${encodeURIComponent(attempt.id)}`;

        resultLink.textContent = "View result ";

        // Add an arrow to the result link.
        const arrow = document.createElement("span");
        arrow.setAttribute("aria-hidden", "true");
        arrow.textContent = "→";

        resultLink.appendChild(arrow);

        // Add all elements to the history card.
        card.append(icon, details, scoreContainer, resultLink);

        return card;
    }

// DISPLAY EMPTY HISTORY

    function displayEmptyHistory() {

        // Create the empty-state container.
        const emptyState = document.createElement("div");
        emptyState.className = "empty-state";

        const icon = document.createElement("span");
        icon.className = "empty-icon";
        icon.textContent = "◷";

        const heading = document.createElement("h3");
        heading.textContent = "No assessments yet";

        const description = document.createElement("p");
        description.textContent =
            "Complete your first practice scenario to see your assessment history here.";

        // Provide a link back to the available practice scenarios.
        const practiceLink = document.createElement("a");
        practiceLink.className = "button button-primary";
        practiceLink.href = "index.php#scenarios";
        practiceLink.textContent = "Start practising";

        emptyState.append(
            icon,
            heading,
            description,
            practiceLink
        );

        historyList.appendChild(emptyState);
    }

// LOAD HISTORY WHEN THE PAGE OPENS

    loadAllAttempts();
});
