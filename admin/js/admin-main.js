// Admin Panel Main JavaScript

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initNotifications();
    initUserDropdown();
    initDateTime();
    initLogout();
    loadSidebarState();
});

// Sidebar Functions
function initSidebar() {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.getElementById('adminSidebar');
    const mainWrapper = document.querySelector('.main-content-wrapper');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (sidebar && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
}

function loadSidebarState() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    const sidebar = document.getElementById('adminSidebar');
    const mainWrapper = document.querySelector('.main-content-wrapper');
    
    if (isCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        mainWrapper.classList.add('sidebar-collapsed');
    }
}

// Notification Functions
let notificationInterval;

function initNotifications() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            loadNotifications();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationBtn.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });
    }
    
    // Load notifications periodically
    loadNotifications();
    notificationInterval = setInterval(loadNotifications, 30000);
}

function loadNotifications() {
    const basePath = window.location.pathname.includes('/approval/') ? 
        '../api/get_pending_members.php' : 
        'api/get_pending_members.php';
    
    fetch(basePath + '?status=0&limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pendingCount = data.data.total || 0;
                updateNotificationBadge(pendingCount);
                updateNotificationList(data.data.members || []);
                updateSidebarBadge(pendingCount);
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    const pendingCountSpan = document.querySelector('.pending-count');
    
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
    
    if (pendingCountSpan) {
        pendingCountSpan.textContent = count > 0 ? `${count} Pending` : 'No Pending';
    }
}

function updateNotificationList(members) {
    const container = document.getElementById('notificationList');
    if (!container) return;
    
    if (members.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4">No pending applications</div>';
        return;
    }
    
    let html = '';
    members.forEach(member => {
        html += `
            <div class="notification-item" onclick="window.location.href='approval/index.php'">
                <div class="member-name">${escapeHtml(member.full_name)}</div>
                <div class="member-details">
                    <i class="fas fa-phone"></i> ${member.mobile_number}<br>
                    <i class="fas fa-calendar"></i> ${member.created_at || 'New'}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function updateSidebarBadge(count) {
    const badge = document.getElementById('sidebarBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// User Dropdown
function initUserDropdown() {
    const userBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userBtn) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!userBtn.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
    }
}

// Live Date Time
function initDateTime() {
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

function updateDateTime() {
    const container = document.getElementById('liveDateTime');
    if (!container) return;
    
    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    
    container.textContent = now.toLocaleString('en-IN', options);
}

// Logout Function
function initLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        });
    }
    
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function() {
            fetch('api/logout.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'login.php';
                });
        });
    }
}

// Utility Functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove('show');
    }
});