// Referrals Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    initializeSidebarToggle();

    // Load referrals data
    loadReferralsData();

    // Close sidebar when clicking outside on mobile
    closeSidebarOnClickOutside();
});

// Load referrals data
function loadReferralsData() {
    fetch('../api/get_member_referrals.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            document.getElementById('loadingIndicator').style.display = 'none';

            if (data.success) {
                populateReferralsTable(data.data);
            } else {
                if (data.message === 'कृपया लॉगिन करें' || response.status === 401) {
                    window.location.href = '../pages/login.php';
                } else {
                    showAlert(data.message || 'डेटा लोड करने में त्रुटि', 'danger');
                    document.getElementById('noDataMessage').classList.remove('d-none');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('noDataMessage').classList.remove('d-none');
            showAlert('डेटा लोड करने में त्रुटि: ' + error.message, 'danger');
        });
}

// Populate referrals table
function populateReferralsTable(referrals) {
    const tbody = document.querySelector('#referralsTable tbody');

    if (referrals.length === 0) {
        document.getElementById('noDataMessage').classList.remove('d-none');
        return;
    }

    referrals.forEach((referral, index) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${referral.member_id || 'N/A'}</td>
            <td>${referral.full_name || 'N/A'}</td>
            <td>${referral.father_husband_name || 'N/A'}</td>
            <td>${referral.mobile_number || 'N/A'}</td>
            <td>${referral.permanent_address || 'N/A'}</td>
            <td>${referral.created_at || 'N/A'}</td>
        `;

        tbody.appendChild(row);
    });
}

// Initialize sidebar toggle functionality
function initializeSidebarToggle() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navbarToggleMobile = document.querySelector('.navbar-toggle-mobile');

    // Sidebar toggle button click
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }

    // Navbar mobile toggle button click
    if (navbarToggleMobile && sidebar) {
        navbarToggleMobile.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }
}

// Close sidebar when clicking outside on mobile
function closeSidebarOnClickOutside() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar) return;

    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = event.target.closest('.sidebar-toggle') ||
                               event.target.closest('.navbar-toggle-mobile');

        if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 768) {
            sidebar.classList.remove('active');
        }
    });
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.remove();
        }
    }, 5000);
}