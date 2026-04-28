// Common Admin Component JavaScript

// Navbar & Sidebar Functions
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Close sidebar when a link is clicked (mobile)
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebar && sidebarToggle && window.innerWidth <= 768) {
        // Close sidebar if clicking outside of it (but not on toggle button)
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});

async function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        try {
            const response = await fetch('api/logout.php', {
                method: 'POST'
            });
            const data = await response.json();
            if (data.success) {
                window.location.href = 'login.php';
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
}

// Footer Time Update
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update on page load and every minute
updateTime();
setInterval(updateTime, 60000);
