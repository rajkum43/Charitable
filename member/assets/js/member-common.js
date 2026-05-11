// Member Common JavaScript
// Shared helpers for sidebar toggle and basic member page behavior.

function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

function toggleSidebar() {
    toggleMobileSidebar();
}

function initializeSidebarToggle() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navbarToggleMobile = document.querySelector('.navbar-toggle-mobile');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }

    if (navbarToggleMobile && sidebar) {
        navbarToggleMobile.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }

    closeSidebarOnClickOutside();
}

function closeSidebarOnClickOutside() {
    const sidebar = document.querySelector('.sidebar');
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

function logoutMember() {
    if (!confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
        return;
    }
    
    fetch('api/logout.php', {
        method: 'POST'
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'लॉगआउट विफल');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('लॉगआउट में त्रुटि: ' + error.message);
    });
}
