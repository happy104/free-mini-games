/**
 * Google AdSense Ad Management Script
 * Handles dynamic insertion of ads into ad containers
 */
document.addEventListener("DOMContentLoaded", function() {
    // Replace ad placeholders with actual ads
    replaceAdPlaceholders();
});

/**
 * Finds ad containers in the page and inserts AdSense ads
 */
function replaceAdPlaceholders() {
    // Select all ad containers
    const adContainers = document.querySelectorAll(".ad-container");
    
    // If no ad containers found, exit
    if (adContainers.length === 0) {
        console.log("No ad containers found on page");
        return;
    }
    
    // For each ad container, create an AdSense ad
    adContainers.forEach(function(container, index) {
        // Clear existing content
        container.innerHTML = "";
        
        // Create AdSense ad element
        const adInsElement = document.createElement("ins");
        adInsElement.className = "adsbygoogle";
        adInsElement.style.display = "block";
        adInsElement.setAttribute("data-ad-client", "ca-pub-2406571508028686"); // Your publisher ID
        
        // Set ad size and format based on container class
        if (container.classList.contains("sidebar-ad") || container.parentElement.classList.contains("sidebar-ad")) {
            // Sidebar ad - 300x250
            adInsElement.style.width = "300px";
            adInsElement.style.height = "250px";
            adInsElement.setAttribute("data-ad-slot", "1234567890"); // Replace with actual ad slot ID
            adInsElement.setAttribute("data-ad-format", "rectangle");
        } else if (container.classList.contains("game-bottom-ad")) {
            // Game page bottom ad - 728x90
            adInsElement.style.width = "728px";
            adInsElement.style.height = "90px";
            adInsElement.setAttribute("data-ad-slot", "2345678901"); // Replace with actual ad slot ID
            adInsElement.setAttribute("data-ad-format", "horizontal");
        } else {
            // Default banner ad - 728x90
            adInsElement.style.width = "728px";
            adInsElement.style.height = "90px";
            adInsElement.setAttribute("data-ad-slot", "3456789012"); // Replace with actual ad slot ID
            adInsElement.setAttribute("data-ad-format", "horizontal");
        }
        
        // Add ad element to container
        container.appendChild(adInsElement);
        
        // Load the ad
        try {
            (adsbygoogle = window.adsbygoogle || []).push({});
        } catch (e) {
            console.error("Failed to load AdSense ad", e);
        }
    });
}
