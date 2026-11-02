"use strict";

// Wait until the assessment page has finished loading its HTML.
document.addEventListener("DOMContentLoaded", () => {

// GET THE SELECTED SCENARIO ID

    // Read the current page URL parameters.
    const urlParameters = new URLSearchParams(window.location.search);

    // Extract the scenario ID from the URL.
    const scenarioId = urlParameters.get("id");

    // Find the elements that display scenario information.
    const scenarioTitle = document.getElementById("scenarioTitle");
    const customerMessage = document.getElementById("customerMessage");
    const scenarioInstructions = document.getElementById("scenarioInstructions");

    // Find the response form and its related elements.
    const assessmentForm = document.getElementById("assessmentForm");
    const scenarioIdInput = document.getElementById("scenarioId");
    const responseInput = document.getElementById("response");
    const characterCount = document.getElementById("characterCount");
    const submitButton = document.getElementById("submitButton");
    const assessmentError = document.getElementById("assessmentError");

// VALIDATE THE SCENARIO ID

    // Only allow a positive integer scenario ID.
    if (!scenarioId || !/^[1-9]\d*$/.test(scenarioId)) {
        showError("Please select a valid scenario.");
        return;
    }

// LOAD THE SCENARIO FROM THE API

    async function loadScenario() {

        try {
            // Send a GET request to retrieve the selected scenario.
            const response = await fetch(
                `api/scenario.php?id=${encodeURIComponent(scenarioId)}`
            );

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the API request succeeded.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to load the scenario."
                );
            }

            // Store the scenario returned by the API.
            const scenario = result.data;

            // Update the browser tab title.
            document.title = `${scenario.title} | ServiceReady`;

            // Display the scenario information.
            scenarioTitle.textContent = scenario.title;
            customerMessage.textContent = scenario.customer_message;
            scenarioInstructions.textContent = scenario.instructions;

            // Store the selected scenario ID in the hidden form field.
            scenarioIdInput.value = scenario.id;

            // Enable the response form after successful scenario loading.
            responseInput.disabled = false;
            submitButton.disabled = false;

        } catch (error) {

            // Log technical details for debugging.
            console.error("Scenario loading error:", error);

            // Display a friendly error message.
            showError(error.message);
        }
    }

// DISPLAY ERRORS

    function showError(message) {

        // Display the error message.
        assessmentError.textContent = message;
        assessmentError.hidden = false;

        // Update the scenario information area.
        scenarioTitle.textContent = "Scenario unavailable";

        customerMessage.textContent =
            "The requested scenario could not be loaded.";

        scenarioInstructions.textContent =
            "Please return to the dashboard and select an available scenario.";

        // Keep the response form disabled if the scenario is unavailable.
        responseInput.disabled = true;
        submitButton.disabled = true;
    }

// UPDATE THE CHARACTER COUNT

    // Update the character count whenever the user types.
    responseInput.addEventListener("input", () => {

        // Read the current response length.
        const currentLength = responseInput.value.length;

        // Display the current and maximum character counts.
        characterCount.textContent = `${currentLength} / 5000`;
    });

// SUBMIT THE ASSESSMENT USING THE JSON API

    assessmentForm.addEventListener("submit", async (event) => {

        // Stop the browser's normal form submission.
        event.preventDefault();

        // Read and trim the user's response.
        const responseText = responseInput.value.trim();

        // Perform basic client-side validation.
        if (responseText.length < 10 || responseText.length > 5000) {
            assessmentError.textContent =
                "Your response must contain between 10 and 5000 characters.";

            assessmentError.hidden = false;
            return;
        }

        // Clear any previous error message.
        assessmentError.textContent = "";
        assessmentError.hidden = true;

        // Prevent multiple submissions while the request is processing.
        submitButton.disabled = true;
        submitButton.textContent = "Submitting...";

        try {
            // Send the assessment data to the PHP API as JSON.
            const response = await fetch("api/submit-attempt.php", {
                method: "POST",

                // Tell PHP that the request body contains JSON.
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },

                // Convert the JavaScript object into a JSON string.
                body: JSON.stringify({
                    scenario_id: Number(scenarioIdInput.value),
                    response: responseText
                })
            });

            // Convert the JSON response into a JavaScript object.
            const result = await response.json();

            // Check whether the API returned an error.
            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to submit your assessment."
                );
            }

            // Retrieve the ID of the saved assessment attempt.
            const attemptId = result.data.attempt_id;

            // Redirect to the results page for this assessment.
            window.location.href =
                `results.php?id=${encodeURIComponent(attemptId)}`;

        } catch (error) {

            // Log technical details for debugging.
            console.error("Assessment submission error:", error);

            // Display the error without clearing the user's response.
            assessmentError.textContent = error.message;
            assessmentError.hidden = false;

            // Allow the user to try submitting again.
            submitButton.disabled = false;
            submitButton.textContent = "Submit response →";
        }
    });

// LOAD THE SCENARIO WHEN THE PAGE OPENS

    loadScenario();
});
