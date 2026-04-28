<?php
// Sidebar Component for Admin Panel
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_approval_page = ($current_dir === 'approval' && $current_page === 'index.php');

// Set base path for links
$base_path = $is_approval_page ? '../' : '';
?>
<div class="sidebar bg-dark text-light">
    <div class="sidebar-brand py-4 px-3 border-bottom">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-dharmachakra me-2"></i>BRCT पोर्टल
        </h5>
        <small class="text-secondary">Admin Panel</small>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="<?php echo $base_path; ?>index.php" class="nav-item <?php echo ($current_page === 'index.php' && !$is_approval_page) ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <!-- Content Management -->
        <div class="nav-section">
            <div class="nav-section-title">Content Management</div>
            
            <a href="<?php echo $base_path; ?>slider-manager.php" class="nav-item <?php echo ($current_page === 'slider-manager.php') ? 'active' : ''; ?>">
                <i class="fas fa-images"></i>
                <span>Slider Manager</span>
            </a>

            <a href="<?php echo $base_path; ?>member-stories.php" class="nav-item <?php echo ($current_page === 'member-stories.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>Member Stories</span>
            </a>
        </div>

        <!-- Team Management -->
        <div class="nav-section">
            <div class="nav-section-title">Team Management</div>
            
            <a href="<?php echo $base_path; ?>manage_core_team.php" class="nav-item <?php echo ($current_page === 'manage_core_team.php') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i>
                <span>Core Team</span>
            </a>
        </div>

        <!-- Admin Management -->
        <div class="nav-section">
            <div class="nav-section-title">Admin Management</div>
            
            <a href="<?php echo $base_path; ?>register.php" class="nav-item <?php echo ($current_page === 'register.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i>
                <span>Register Admin</span>
            </a>

            <a href="<?php echo $base_path; ?>admin-users.php" class="nav-item <?php echo ($current_page === 'admin-users.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>All Admins</span>
            </a>
        </div>

        <!-- Member Management -->
        <div class="nav-section">
            <div class="nav-section-title">Member Management</div>
            
            <a href="<?php echo $is_approval_page ? 'index.php' : 'approval/index.php'; ?>" class="nav-item <?php echo $is_approval_page ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i>
                <span>Member Approvals</span>
            </a>

            <a href="<?php echo $base_path; ?>edit-member.php" class="nav-item <?php echo ($current_page === 'edit-member.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-edit"></i>
                <span>Edit Member Details</span>
            </a>

            <a href="<?php echo $base_path; ?>update-member-created-date.php" class="nav-item <?php echo ($current_page === 'update-member-created-date.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Update Member Date</span>
            </a>
        </div>

        <!-- Application Management -->
        <div class="nav-section">
            <div class="nav-section-title">Application Management</div>
            
            <a href="<?php echo $base_path; ?>beti_vivah_applications.php" class="nav-item <?php echo ($current_page === 'beti_vivah_applications.php') ? 'active' : ''; ?>">
                <i class="fas fa-venus me-1"></i>
                <span>बेटी विवाह आवेदन</span>
            </a>

            <a href="<?php echo $base_path; ?>death_claims_management.php" class="nav-item <?php echo ($current_page === 'death_claims_management.php') ? 'active' : ''; ?>">
                <i class="fas fa-heart-broken text-danger"></i>
                <span>मृत्यु सहायता आवेदन</span>
            </a>

            <a href="<?php echo $base_path; ?>poll-management.php" class="nav-item <?php echo ($current_page === 'poll-management.php') ? 'active' : ''; ?>">
                <i class="fas fa-poll me-1"></i>
                <span>पोल प्रबंधन</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer py-3 px-3 border-top mt-auto">
        <small class="text-secondary d-block mb-2">
            <i class="fas fa-shield-alt"></i> Logged in as: <strong><?php echo $admin_username; ?></strong>
        </small>
        <small class="text-secondary">
            <i class="fas fa-server"></i> Status: <span class="text-success">● Online</span>
        </small>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 56px;
    height: calc(100vh - 56px);
    width: 250px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    z-index: 1000;
    background: #2d3748;
    color: #e2e8f0;
}

.sidebar-brand {
    background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
    color: white;
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid #495057;
}

.sidebar-brand h5 {
    margin: 0;
    font-size: 1.1rem;
}

.sidebar-brand small {
    display: block;
    margin-top: 5px;
    opacity: 0.8;
}

.sidebar-nav {
    flex: 1;
    padding: 15px 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #cbd5e0;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.1);
    border-left-color: #0d6efd;
}

.nav-item.active {
    color: white;
    background-color: rgba(13, 110, 253, 0.2);
    border-left-color: #0d6efd;
    font-weight: 600;
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.nav-section {
    padding: 15px 0;
}

.nav-section-title {
    padding: 10px 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #718096;
    letter-spacing: 0.5px;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid #495057;
    background: #1a202c;
}

/* Scrollbar styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: #2d3748;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #718096;
}

/* Responsive - Hide on mobile, show with toggle */
@media (max-width: 768px) {
    html,
    body {
        width: 100%;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    body.sidebar-open::before {
        opacity: 1;
        pointer-events: auto;
    }

    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        width: 250px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .sidebar-nav {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        overflow-x: hidden;
        min-height: 0;
    }

    .sidebar.show {
        transform: translateX(0);
    }

    /* Always show navigation items on mobile */
    .nav-item span {
        display: inline !important;
    }

    .nav-section-title {
        display: block !important;
    }

    .container-fluid {
        max-width: 100%;
        padding: 0 10px;
    }
}

/* Desktop - Always visible */
@media (min-width: 769px) {
    .sidebar {
        position: fixed;
        transform: translateX(0) !important;
    }

    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }
}
</style>
