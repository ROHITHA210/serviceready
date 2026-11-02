"use strict";

// Wait until the HTML document has been parsed before accessing page elements.
document.addEventListener("DOMContentLoaded", () => {

    // MOBILE NAVIGATION

    // Find the button that opens and closes the mobile navigation menu.
    const menuToggle = document.getElementById("menuToggle");

    // Find the navigation container whose visibility changes on smaller screens.
    const mainNav = document.getElementById("mainNav");

    // Only add menu behavior if both required HTML elements exist.
    if (menuToggle && mainNav) {

        // Toggle the menu's CSS class when the user clicks the menu button.
        menuToggle.addEventListener("click", () => {
            const isOpen = mainNav.classList.toggle("is-open");

            // Keep accessibility attributes synchronized with menu visibility.
            menuToggle.setAttribute("aria-expanded", String(isOpen));

            menuToggle.setAttribute(
                "aria-label",
                isOpen ? "Close navigation" : "Toggle navigation"
            );
        });

        // Close the mobile menu after a navigation link is selected.
        mainNav.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                mainNav.classList.remove("is-open");

                menuToggle.setAttribute("aria-expanded", "false");
                menuToggle.setAttribute("aria-label", "Toggle navigation");
            });
        });
    }

// LOAD SCENARIOS FROM THE PHP API:

    // Find the homepage elements where scenario information will appear.
    const scenarioGrid = document.getElementById("scenarioGrid");
    const scenarioTotal = document.getElementById("scenarioTotal");
    const scenarioCount = document.getElementById("scenarioCount");

    // Load scenarios only on pages that contain the scenario grid.
    if (scenarioGrid) {
        loadScenarios();
    }

    // Retrieve active scenarios from the PHP backend.
    async function loadScenarios() {

        try {
            // Send a GET request to the scenarios API.
            const response = await fetch("api/scenarios.php");

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the HTTP request and API operation succeeded.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load scenarios."
                );
            }

            // Get the scenarios returned by the API.
            const scenarios = result.data;

            // Update the scenario count displayed on the homepage.
            if (scenarioTotal) {
                scenarioTotal.textContent = result.count;
            }

            if (scenarioCount) {
                scenarioCount.textContent = `${result.count} available`;
            }

            // Clear the loading message before displaying scenario cards.
            scenarioGrid.replaceChildren();

            // Show a helpful message when there are no active scenarios.
            if (scenarios.length === 0) {
                const emptyState = document.createElement("div");
                emptyState.className = "empty-state";

                const heading = document.createElement("h3");
                heading.textContent = "No active scenarios yet";

                const message = document.createElement("p");
                message.textContent =
                    "Practice scenarios will appear here when available.";

                emptyState.append(heading, message);
                scenarioGrid.appendChild(emptyState);

                return;
            }

            // Create one scenario card for every scenario returned by the API.
            scenarios.forEach((scenario) => {

                // Create the main scenario card.
                const card = document.createElement("article");
                card.className = "scenario-card";

                // Display the scenario title.
                const title = document.createElement("h3");
                title.textContent = scenario.title;

                // Display the customer message.
                const description = document.createElement("p");
                description.className = "scenario-description";
                description.textContent = scenario.customer_message;

                // Create the card footer.
                const footer = document.createElement("div");
                footer.className = "scenario-card-footer";

                // Display the practice type.
                const duration = document.createElement("span");
                duration.className = "duration-label";
                duration.textContent = "Written practice";

                // Create the link to the assessment page.
                const startLink = document.createElement("a");
                startLink.className = "text-link";

                // Pass the scenario ID to assessment.php.
                startLink.href =
                    `assessment.php?id=${encodeURIComponent(scenario.id)}`;

                startLink.textContent = "Start practice ";

                // Add the arrow after the link text.
                const arrow = document.createElement("span");
                arrow.setAttribute("aria-hidden", "true");
                arrow.textContent = "→";

                startLink.appendChild(arrow);

                // Add footer elements to the card.
                footer.append(duration, startLink);

                // Add all card elements to the scenario card.
                card.append(title, description, footer);

                // Display the completed card on the homepage.
                scenarioGrid.appendChild(card);
            });

        } catch (error) {

            // Log the technical error for debugging.
            console.error("Scenario loading error:", error);

            // Update the count indicators when the API request fails.
            if (scenarioTotal) {
                scenarioTotal.textContent = "—";
            }

            if (scenarioCount) {
                scenarioCount.textContent = "Unavailable";
            }

            // Display a friendly error message on the homepage.
            const errorPanel = document.createElement("div");
            errorPanel.className = "empty-state";
            errorPanel.setAttribute("role", "alert");

            const heading = document.createElement("h3");
            heading.textContent = "Unable to load scenarios";

            const message = document.createElement("p");
            message.textContent =
                "Please check your connection and try again.";

            errorPanel.append(heading, message);

            scenarioGrid.replaceChildren(errorPanel);
        }
    }

    // SMOOTH SECTION NAVIGATION

    // Find links that point to sections within the current page.
    document.querySelectorAll('a[href^="#"]').forEach((link) => {

        // Handle clicks on these section links.
        link.addEventListener("click", (event) => {

            // Extract the target element's ID from the link's href.
            const targetId = link.getAttribute("href").slice(1);

            // Find the corresponding HTML element.
            const target = document.getElementById(targetId);

            // Only override the default behavior when the target exists.
            if (target) {
                event.preventDefault();

                // Respect the visitor's reduced-motion preference.
                target.scrollIntoView({
                    behavior: window.matchMedia(
                        "(prefers-reduced-motion: reduce)"
                    ).matches ? "auto" : "smooth",
                    block: "start"
                });
            }
        });
    });
});
