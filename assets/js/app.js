
"use strict";

// Wait until the HTML document has been parsed before accessing page elements.
document.addEventListener("DOMContentLoaded", () => {
    // Find the button that opens and closes the mobile navigation menu.
    const menuToggle = document.getElementById("menuToggle");

    // Find the navigation container whose visibility changes on smaller screens.
    const mainNav = document.getElementById("mainNav");

    // Only add menu behavior if both required HTML elements exist.
    // This allows the script to load safely on pages without this menu.
    if (menuToggle && mainNav) {

        // Toggle the menu's CSS class when the user clicks the menu button.
        menuToggle.addEventListener("click", () => {
            const isOpen = mainNav.classList.toggle("is-open");

            // Keep the accessibility state synchronized with the menu's visibility.
            menuToggle.setAttribute("aria-expanded", String(isOpen));

            // Update the accessible label to describe the current menu action.
            menuToggle.setAttribute(
                "aria-label",
                isOpen ? "Close navigation" : "Toggle navigation"
            );
        });

        // Close the mobile menu after the user selects one of its navigation links.
        mainNav.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {

                // Remove the open class so the menu returns to its closed state.
                mainNav.classList.remove("is-open");

                // Reset accessibility attributes to match the closed menu.
                menuToggle.setAttribute("aria-expanded", "false");
                menuToggle.setAttribute("aria-label", "Toggle navigation");
            });
        });
    }

    // Find links that point to sections within the current page,
    // such as href="#scenarios" or href="#recent".
    document.querySelectorAll('a[href^="#"]').forEach((link) => {

        // Handle clicks on these section links.
        link.addEventListener("click", (event) => {

            // Extract the target element's ID from the link's href.
            // For example, "#scenarios" becomes "scenarios".
            const targetId = link.getAttribute("href").slice(1);

            // Find the corresponding HTML element using its ID.
            const target = document.getElementById(targetId);

            // Only override the default link behavior when the target exists.
            if (target) {
                event.preventDefault();

                // Scroll to the target section.
                // Respect the visitor's reduced-motion accessibility preference:
                // use instant scrolling if reduced motion is enabled, otherwise smooth scrolling.
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
