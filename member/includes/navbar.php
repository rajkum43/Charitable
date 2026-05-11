<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$navbarMemberName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'सदस्य';
?>
<!-- Member Dashboard Navbar -->
<nav class="navbar navbar-member">
    <div class="navbar-container">
        <!-- Left Section: Brand -->
        <div class="navbar-left">
            <button class="navbar-toggle-mobile" id="mobileToggle" onclick="toggleMobileSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="index.php" class="navbar-brand">
                <i class="fas fa-heart text-danger me-2"></i>
                <span class="brand-text">BRCT Bharat Trust</span>
            </a>
        </div>

        <!-- Center Section: Title -->
        <div class="navbar-center">
            <h5 class="navbar-title mb-0" id="navbarTitle">डैशबोर्ड</h5>
        </div>

        <!-- Right Section: Actions & Profile -->
        <div class="navbar-right">
            <!-- Notifications -->
            <div class="navbar-item">
                <button class="notification-btn" id="notificationBell" title="सूचनाएं">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationCount">0</span>
                </button>
            </div>

            <!-- Member Status -->
            <div class="navbar-item">
                <small class="member-status" id="memberStatusBadge">
                    <span class="status-indicator"></span>
                    <span id="statusText">सक्रिय</span>
                </small>
            </div>

            <!-- Dropdown Menu -->
            <div class="navbar-item dropdown">
                <button class="user-profile-btn" type="button" id="userMenuBtn" data-bs-toggle="dropdown" title="प्रोफाइल">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="d-none d-md-inline-block ms-2">
                        <small id="navbarMemberName"><?php echo htmlspecialchars($navbarMemberName); ?></small>
                    </span>
                    <i class="fas fa-chevron-down ms-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
                    <li>
                        <h6 class="dropdown-header">
                            <i class="fas fa-user-circle me-2"></i>
                            <span id="dropdownMemberName"><?php echo htmlspecialchars($navbarMemberName); ?></span>
                        </h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="loadSection('profile')">
                            <i class="fas fa-user-edit me-2 text-primary"></i>प्रोफाइल संपादित करें
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="sahyog.php">
                            <i class="fas fa-hands-helping me-2 text-success"></i>सहयोग
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="loadSection('settings')">
                            <i class="fas fa-cog me-2 text-warning"></i>सेटिंग्स
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="application_status.php">
                            <i class="fas fa-certificate me-2 text-info"></i>आवेदन स्थिति
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="logoutMember()">
                            <i class="fas fa-sign-out-alt me-2"></i>लॉगआउट
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
