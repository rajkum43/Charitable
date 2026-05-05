<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Toggle Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            height: 56px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            color: white;
            padding: 0 20px;
            gap: 10px;
        }

        .navbar button {
            background: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .content-wrapper {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: #2d3748;
            color: white;
            overflow-y: auto;
            transition: width 0.3s ease;
            flex-shrink: 0;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .menu-text {
            display: none;
        }

        .sidebar-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            border-left: 3px solid transparent;
        }

        .sidebar.collapsed .sidebar-item {
            justify-content: center;
            padding: 15px;
        }

        .sidebar-item:hover {
            background: rgba(13, 110, 253, 0.1);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .page-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .footer {
            background: #1a202c;
            color: white;
            padding: 20px 30px;
            border-top: 1px solid #495057;
            flex-shrink: 0;
        }

        .content-box {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <button onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h6 style="margin: 0; color: white;">Admin Panel - Sidebar Test</h6>
        <div style="margin-left: auto;">
            <span><?php echo htmlspecialchars($admin_username); ?></span>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-item">
                <i class="fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </div>
            <div class="sidebar-item">
                <i class="fas fa-images"></i>
                <span class="menu-text">Sliders</span>
            </div>
            <div class="sidebar-item">
                <i class="fas fa-user-circle"></i>
                <span class="menu-text">Stories</span>
            </div>
            <div class="sidebar-item">
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Update Date</span>
            </div>
            <div class="sidebar-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Users</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-content">
                <h1 class="mb-4">Sidebar Toggle Test</h1>
                
                <div class="content-box">
                    <h5>Instructions:</h5>
                    <p>Click the hamburger menu button (☰) in the navbar to collapse/expand the sidebar.</p>
                    <p class="text-success">✓ Sidebar should animate smoothly</p>
                    <p class="text-success">✓ Content should adjust accordingly</p>
                    <p class="text-success">✓ Footer should stay at the bottom</p>
                </div>

                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <div class="content-box">
                        <h6>Content Section <?php echo $i; ?></h6>
                        <p>This is test content. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="mb-0">© 2026 BRCT Bharat Trust. All rights reserved. | Last updated: <?php echo date('Y-m-d H:i'); ?></p>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                // Save preference
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                console.log('Sidebar toggled:', sidebar.classList.contains('collapsed'));
            }
        }

        // Restore sidebar state on load
        window.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
</body>
</html>
