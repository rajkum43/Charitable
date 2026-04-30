/**
 * Alert Beti Vivah Sahyog Suchi - JavaScript
 * Handles interactions for alert cards page
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAlertCards();
});

/**
 * Initialize Alert Cards
 */
function initializeAlertCards() {
    const alertCards = document.querySelectorAll('.alert-card');
    
    // Add click event listeners to view details buttons
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Can add custom handling here if needed
            console.log('Viewing alert details:', this.href);
        });
    });

    // Add keyboard navigation
    alertCards.forEach((card, index) => {
        card.setAttribute('tabindex', '0');
        
        card.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const link = this.querySelector('.view-details-btn');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });
    });
}

/**
 * Optional: Add smooth scroll behavior
 */
function smoothScrollToElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

/**
 * Optional: Analytics tracking for card views
 */
function trackCardView(alertNumber) {
    if (window.gtag) {
        gtag('event', 'alert_card_view', {
            'alert_number': alertNumber
        });
    }
}
