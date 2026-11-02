"use strict";

// Wait until the homepage HTML has finished loading.
document.addEventListener("DOMContentLoaded", () => {

// FIND THE HOMEPAGE HISTORY ELEMENTS

    // Find the elements used to display the total attempt count.
    const attemptTotal = document.getElementById("attemptTotal");
    const attemptNote = document.getElementById("attemptNote");

    // Find the container where recent assessment cards will appear.
    const attemptList = document.getElementById("attemptList");

    // Run this script only on pages containing the history section.
    if (!attemptList) {
        return;
    }

// LOAD RECENT ASSESSMENTS FROM THE API

    async function loadHistory() {

        try {
            // Request the total count and five most recent attempts.
            const response = await fetch("api/attempts.php");

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the API request succeeded.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load assessment history."
                );
            }

            // Read the total number of saved assessment attempts.
            const totalCount = Number(result.count) || 0;

            // Update the completed-attempt statistics card.
            if (attemptTotal) {
                attemptTotal.textContent = totalCount;
            }

            // Explain how many attempts are currently displayed.
            if (attemptNote) {
                if (totalCount === 0) {
                    attemptNote.textContent = "No completed attempts yet";
                } else if (totalCount > result.showing) {
                    attemptNote.textContent = "Showing your latest five";
                } else {
                    attemptNote.textContent = "Your practice history";
                }
            }

            // Retrieve the recent attempts returned by the API.
            const attempts = result.data;

            // Clear the loading message before displaying the results.
            attemptList.replaceChildren();

            // Display an empty state when there are no saved attempts.
            if (!Array.isArray(attempts) || attempts.length === 0) {
                displayEmptyState();
                return;
            }

            // Create a history card for each returned assessment.
            attempts.forEach((attempt) => {
                const card = createAttemptCard(attempt);
                attemptList.appendChild(card);
            });

        } catch (error) {

            // Log technical details for debugging.
            console.error("Assessment history loading error:", error);

            // Display a friendly message if the API request fails.
            if (attemptTotal) {
                attemptTotal.textContent = "—";
            }

            if (attemptNote) {
                attemptNote.textContent = "History unavailable";
            }

            displayError(error.message);
        }
    }

// CREATE AN ASSESSMENT HISTORY CARD

    function createAttemptCard(attempt) {

        // Create the main history row.
        const card = document.createElement("article");
        card.className = "attempt-row";

        // Create the completion icon.
        const icon = document.createElement("div");
        icon.className = "attempt-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.textContent = "✓";

        // Create the section containing scenario and date information.
        const details = document.createElement("div");
        details.className = "attempt-details";

        // Display the scenario title.
        const title = document.createElement("h3");
        title.textContent = attempt.scenario_title;

        // Format the assessment submission date.
        const date = document.createElement("p");

        const submittedAt = new Date(
            String(attempt.created_at).replace(" ", "T")
        );

        date.textContent = Number.isNaN(submittedAt.getTime())
            ? attempt.created_at
            : submittedAt.toLocaleString("en-IN", {
                month: "short",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit"
            });

        details.append(title, date);

        // Create the section displaying the overall score.
        const scoreContainer = document.createElement("div");
        scoreContainer.className = "attempt-score";

        const scoreLabel = document.createElement("span");
        scoreLabel.textContent = "Rule-based score";

        const scoreValue = document.createElement("strong");

        const score = Math.max(
            0,
            Math.min(100, Number(attempt.total_score) || 0)
        );

        scoreValue.textContent = `${score.toFixed(1)}%`;

        scoreContainer.append(scoreLabel, scoreValue);

        // Create a link to the detailed results page.
        const resultLink = document.createElement("a");
        resultLink.className = "text-link";

        resultLink.href =
            `results.php?id=${encodeURIComponent(attempt.id)}`;

        resultLink.textContent = "View result ";

        // Add the arrow to the result link.
        const arrow = document.createElement("span");
        arrow.setAttribute("aria-hidden", "true");
        arrow.textContent = "→";

        resultLink.appendChild(arrow);

        // Add all elements to the completed history row.
        card.append(icon, details, scoreContainer, resultLink);

        return card;
    }

// DISPLAY AN EMPTY HISTORY MESSAGE

    function displayEmptyState() {

        // Create the empty-state container.
        const emptyState = document.createElement("div");
        emptyState.className = "empty-state recent-empty";

        // Add the history icon.
        const icon = document.createElement("span");
        icon.className = "empty-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.textContent = "◷";

        // Add the heading.
        const heading = document.createElement("h3");
        heading.textContent = "Your practice history starts here";

        // Explain that no assessments have been submitted yet.
        const message = document.createElement("p");
        message.textContent =
            "You haven't submitted an assessment yet. Choose a scenario above to get started.";

        // Add a link to the scenario section.
        const link = document.createElement("a");
        link.className = "button button-secondary";
        link.href = "#scenarios";
        link.textContent = "Explore scenarios";

        // Assemble the empty-state section.
        emptyState.append(icon, heading, message, link);

        attemptList.appendChild(emptyState);
    }

// DISPLAY HISTORY API ERRORS

    function displayError(message) {

        // Create an error container.
        const errorPanel = document.createElement("div");
        errorPanel.className = "empty-state";
        errorPanel.setAttribute("role", "alert");

        // Add an error heading.
        const heading = document.createElement("h3");
        heading.textContent = "Unable to load practice history";

        // Display a friendly message.
        const description = document.createElement("p");
        description.textContent =
            "Please refresh the page and try again.";

        errorPanel.append(heading, description);

        // Replace the history area with the error message.
        attemptList.replaceChildren(errorPanel);
    }

// START LOADING THE HISTORY

    loadHistory();
});
