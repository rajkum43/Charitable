/**
 * Alert Death Sahyog Suchi - JavaScript
 * Client-side interactions for alert list
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAlertCards();
});

/**
 * Initialize alert card interactions
 */
function initializeAlertCards() {
    const alertCards = document.querySelectorAll('.alert-card');
    
    // Add keyboard navigation
    alertCards.forEach((card, index) => {
        card.setAttribute('tabindex', '0');
        
        // Keyboard event for Enter key
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const link = this.querySelector('a.view-details-btn');
                if (link) {
                    window.location.href = link.href;
                }
            }
        });

        // Click handler for view details button
        const viewBtn = card.querySelector('.view-details-btn');
        if (viewBtn) {
            viewBtn.addEventListener('click', function(e) {
                const alertNumber = this.href.split('alert=')[1];
                trackCardView(alertNumber);
            });
        }

        // Hover smooth scroll effect
        card.addEventListener('mouseenter', function() {
            this.style.cursor = 'pointer';
        });
    });
}

/**
 * Track card view for analytics (optional)
 * @param {string} alertNumber - Alert number being viewed
 */
function trackCardView(alertNumber) {
    // Optional: Send tracking data to analytics
    // if (typeof gtag !== 'undefined') {
    //     gtag('event', 'view_alert', {
    //         'alert_number': alertNumber
    //     });
    // }
    console.log('Viewing Alert:', alertNumber);
}

/**
 * Smooth scroll to element
 * @param {string} selector - CSS selector of element to scroll to
 */
function smoothScrollToElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
