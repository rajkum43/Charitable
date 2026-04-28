<!-- Member Dashboard Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Brand Section -->
    <div class="sidebar-brand">
        <a href="index.php" class="brand-logo">
            <i class="fas fa-heart text-danger"></i>
            <span class="brand-name">BRCT</span>
        </a>
    </div>

    <!-- Member Header -->
    <div class="sidebar-header">
        <div class="member-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="member-info">
            <h6 id="sidebarMemberName" class="mb-0">सदस्य</h6>
            <small id="sidebarMemberId" class="text-muted d-block">ID: ...</small>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <h6 class="section-title">मुख्य</h6>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="dashboard">
                        <i class="fas fa-home"></i>
                        <span>डैशबोर्ड</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="profile">
                        <i class="fas fa-user"></i>
                        <span>प्रोफाइल</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <h6 class="section-title">सदस्यता</h6>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="membership">
                        <i class="fas fa-id-card"></i>
                        <span>विवरण</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="payment">
                        <i class="fas fa-credit-card"></i>
                        <span>भुगतान</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sahyog.php" class="nav-link" data-section="sahyog">
                        <i class="fas fa-hands-helping"></i>
                        <span>सहयोग</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="member_donation.php" class="nav-link">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>दान करें</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="donation_history.php" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>दान का इतिहास</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-section">
            <h6 class="section-title">अन्य</h6>
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="documents">
                        <i class="fas fa-file"></i>
                        <span>दस्तावेज़</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link" data-section="settings">
                        <i class="fas fa-cog"></i>
                        <span>सेटिंग्स</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <button class="btn-logout w-100" onclick="logoutMember()">
            <i class="fas fa-sign-out-alt me-2"></i>
            <span>लॉगआउट</span>
        </button>
    </div>
</aside>

<!-- Sidebar Toggle Button (for mobile) -->
<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="साइडबार खोलें/बंद करें">
    <i class="fas fa-bars"></i>
</button>
