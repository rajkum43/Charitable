// Poll Transparency Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations on scroll
    observerElements();
    
    // Add any interactive features
    attachEventListeners();
});

// Observe elements for scroll animations
function observerElements() {
    const cards = document.querySelectorAll('.stat-card, .poll-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Attach event listeners
function attachEventListeners() {
    // Add hover effects to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add data export functionality
    const exportBtn = document.querySelector('[data-export]');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportData);
    }
}

// Export data to CSV
function exportData() {
    // Get all poll data
    const pollCards = document.querySelectorAll('.poll-card');
    let csvContent = 'data:text/csv;charset=utf-8,';
    
    csvContent += 'पोल नाम,लाभार्थी,पोल कोड,भुगतान प्राप्त,कुल सदस्य,प्रतिशत,संग्रह राशि\n';
    
    pollCards.forEach(card => {
        const name = card.querySelector('.poll-card-header h5')?.textContent || '';
        const code = card.querySelector('.poll-code')?.textContent || '';
        const beneficiary = card.querySelector('.beneficiary-box')?.textContent.replace('लाभार्थी:', '').trim() || '';
        
        // Extract progress info
        const progressText = card.querySelector('.progress-text')?.textContent || '';
        const amountBox = card.querySelector('.amount-box h3')?.textContent || '';
        
        csvContent += `"${name}","${beneficiary}","${code}","${progressText}","${amountBox}"\n`;
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `poll_data_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Real-time progress animation
function animateProgress() {
    const progressBars = document.querySelectorAll('.progress-bar');
    
    progressBars.forEach(bar => {
        const percentage = parseFloat(bar.style.width);
        bar.style.width = '0%';
        
        setTimeout(() => {
            bar.style.transition = 'width 2s ease';
            bar.style.width = percentage + '%';
        }, 100);
    });
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        currency: 'INR',
        style: 'currency',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

// Update dashboard in real-time (optional)
function startAutoRefresh(intervalSeconds = 60) {
    setInterval(() => {
        // You can add API call here to refresh data
        console.log('Refreshing poll data...');
    }, intervalSeconds * 1000);
}

// Initialize animations on page load
window.addEventListener('load', function() {
    animateProgress();
});

// Print functionality
function printDashboard() {
    window.print();
}

// Share functionality
function shareDashboard() {
    const currentUrl = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: 'Trust Sahyog Poll System - Transparency Dashboard',
            text: 'इस Dashboard को देखें और पोल की प्रगति जानें।',
            url: currentUrl
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(currentUrl).then(() => {
            alert('Link कॉपी हो गया!');
        });
    }
}
