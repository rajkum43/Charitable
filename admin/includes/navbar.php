<?php
// Navbar Component for Admin Panel
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <!-- Sidebar Toggle for Mobile -->
        <button class="btn btn-light btn-sm me-2 d-lg-none" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar Toggle for Desktop -->
        <button class="btn btn-light btn-sm me-2 d-none d-lg-block" id="desktopSidebarToggle" onclick="toggleDesktopSidebar()" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-chart-line me-2"></i>BRCT Admin
        </a>

        <!-- Toggle Button for Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Notification Icon -->
                <li class="nav-item me-3">
                    <a href="#" class="nav-link position-relative" id="notificationIcon" title="नई सदस्य सूचनाएं">
                        <i class="fas fa-bell" style="font-size: 1.3rem;"></i>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="notificationBadge" style="display: none;">0</span>
                    </a>
                </li>

                <!-- Current User Info -->
                <li class="nav-item me-3">
                    <span class="text-light">
                        <i class="fas fa-user-circle"></i> <?php echo $admin_username; ?>
                    </span>
                </li>

                <!-- Logout Button -->
                <li class="nav-item">
                    <button class="btn btn-outline-light btn-sm" onclick="handleLogout()">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const body = document.body;
    if (sidebar) {
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
    }
}

function toggleDesktopSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainWrapper = document.querySelector('.main-wrapper');
    const adminFooter = document.querySelector('.admin-footer');
    
    if (sidebar && mainWrapper) {
        sidebar.classList.toggle('collapsed');
        mainWrapper.classList.toggle('sidebar-collapsed');
        if (adminFooter) {
            adminFooter.classList.toggle('sidebar-collapsed');
        }
        // Store preference in localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
}

// Restore sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        const sidebar = document.querySelector('.sidebar');
        const mainWrapper = document.querySelector('.main-wrapper');
        const adminFooter = document.querySelector('.admin-footer');
        
        if (sidebar) sidebar.classList.add('collapsed');
        if (mainWrapper) mainWrapper.classList.add('sidebar-collapsed');
        if (adminFooter) adminFooter.classList.add('sidebar-collapsed');
    }
});

// Close sidebar when a link is clicked (mobile)
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.body;
    
    if (sidebar && sidebarToggle && window.innerWidth <= 768) {
        // Close sidebar if clicking outside of it (but not on toggle button)
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('show');
            body.classList.remove('sidebar-open');
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

// Notification System
let notificationCheckInterval;

function updateNotificationCountGlobal() {
    updateNotificationCount();
}

function updateNotificationCount() {
    // Get the base path for API calls
    const basePath = window.location.pathname.includes('/approval/') ? 
        'api/get_stats.php' : 
        'approval/api/get_stats.php';

    fetch(basePath)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const pendingCount = data.data.pending || 0;
                const badge = document.getElementById('notificationBadge');
                const icon = document.getElementById('notificationIcon');

                if (pendingCount > 0) {
                    badge.textContent = pendingCount;
                    badge.style.display = 'flex';
                    badge.setAttribute('data-pending-count', pendingCount);
                    icon.setAttribute('title', `${pendingCount} नई सदस्य प्रतीक्षा में हैं`);
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error updating notification count:', error);
        });
}

// Add click handler for notification icon
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function(e) {
            e.preventDefault();
            showNotificationPopup();
        });

        // Update notification count on page load
        updateNotificationCount();

        // Update notification count every 30 seconds
        notificationCheckInterval = setInterval(updateNotificationCount, 30000);

        // Clean up interval when page unloads
        window.addEventListener('beforeunload', function() {
            if (notificationCheckInterval) {
                clearInterval(notificationCheckInterval);
            }
        });
    }
});

function showNotificationPopup() {
    // Remove existing popup if any
    const existingPopup = document.getElementById('notificationPopup');
    if (existingPopup) {
        existingPopup.remove();
        return; // Toggle off
    }

    const basePath = window.location.pathname.includes('/approval/') ? 
        'api/get_pending_members.php?status=0&page=1&limit=5' : 
        'approval/api/get_pending_members.php?status=0&page=1&limit=5';

    const approvalLink = window.location.pathname.includes('/approval/') ?
        '#' : 'approval/index.php';

    fetch(basePath)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success && data.data.members.length > 0) {
                const popup = document.createElement('div');
                popup.id = 'notificationPopup';
                popup.className = 'notification-popup';

                let html = `
                    <div class="notification-popup-header">
                        नई सदस्य आवेदन
                        <span class="notification-count" style="float: right;">${data.data.total}</span>
                    </div>
                    <div class="notification-popup-body">
                `;

                data.data.members.slice(0, 5).forEach(member => {
                    html += `
                        <div class="notification-item">
                            <div class="notification-text">
                                <strong>${member.full_name}</strong>
                            </div>
                            <div style="font-size: 0.85rem; color: #999;">
                                📱 ${member.mobile_number}
                            </div>
                            <div style="font-size: 0.85rem; color: #999;">
                                📅 ${member.created_at}
                            </div>
                        </div>
                    `;
                });

                if (data.data.total > 5) {
                    html += `
                        <div class="notification-item" style="text-align: center; padding: 10px 0; margin-top: 10px; border-top: 1px solid #dee2e6;">
                            <a href="${approvalLink}" style="color: #0d6efd; text-decoration: none; font-weight: 600;">
                                सभी देखें (${data.data.total} कुल)
                            </a>
                        </div>
                    `;
                }

                html += `</div>`;

                popup.innerHTML = html;
                document.body.appendChild(popup);

                // Close popup when clicking outside
                setTimeout(() => {
                    document.addEventListener('click', function closePopup(e) {
                        if (!popup.contains(e.target) && !document.getElementById('notificationIcon').contains(e.target)) {
                            popup.remove();
                            document.removeEventListener('click', closePopup);
                        }
                    });
                }, 100);
            } else {
                alert('कोई नई सदस्य सूचना नहीं है');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('सूचना लोड करने में त्रुटि: ' + error.message);
        });
}
</script>
