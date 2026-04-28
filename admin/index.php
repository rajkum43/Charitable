<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch statistics
$stats = [
    'total_members' => 5234,
    'beti_vivah' => 287,
    'death_sahyog' => 168,
    'sahayata_total' => 85
];

// Fetch counts from database
$sliders_count = $conn->query("SELECT COUNT(*) as count FROM sliders")->fetch_assoc()['count'];
$stories_count = $conn->query("SELECT COUNT(*) as count FROM member_stories")->fetch_assoc()['count'];
$admins_count = $conn->query("SELECT COUNT(*) as count FROM admin_users")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BRCT Bharat Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <!-- Dynamic Base URL Configuration (MUST be before other scripts) -->
    <script src="../assets/js/config.js"></script>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <div class="main-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="mb-5">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
                    </h1>
                    <p class="text-secondary mb-0">Welcome back, <strong><?php echo $admin_username; ?></strong>! Here's an overview of your admin panel.</p>
                </div>

                <!-- Statistics -->
                <div class="row mb-5">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box bg-blue">
                            <i class="fas fa-images fa-2x mb-3"></i>
                            <h3><?php echo $sliders_count; ?></h3>
                            <p>Slider Images</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box bg-green">
                            <i class="fas fa-user-circle fa-2x mb-3"></i>
                            <h3><?php echo $stories_count; ?></h3>
                            <p>Member Stories</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box bg-orange">
                            <i class="fas fa-users fa-2x mb-3"></i>
                            <h3><?php echo $admins_count; ?></h3>
                            <p>Admin Users</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box bg-red">
                            <i class="fas fa-hand-holding-heart fa-2x mb-3"></i>
                            <h3><?php echo $stats['sahayata_total']; ?></h3>
                            <p>People Assisted</p>
                        </div>
                    </div>
                </div>

                <!-- Main Menu -->
                <div class="mb-5">
                    <h2 class="fw-bold mb-4">
                        <i class="fas fa-cogs me-2 text-primary"></i>Management Tools
                    </h2>
                    <div class="row g-4">
                        <!-- Beti Vivah Applications -->
                        <div class="col-md-6 col-lg-4">
                            <a href="approve_applications.php" class="menu-card dashboard-card text-center">
                                <div class="menu-card-icon text-danger">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 class="fw-bold">बेटी विवाह आवेदन</h3>
                                <p class="text-secondary mb-3">आवेदनों को स्वीकृति/अस्वीकार करें</p>
                                <span class="badge bg-danger">प्रबंधन</span>
                            </a>
                        </div>

                        <!-- Slider Manager -->
                        <div class="col-md-6 col-lg-4">
                            <a href="slider-manager.php" class="menu-card dashboard-card text-center">
                                <div class="menu-card-icon text-primary">
                                    <i class="fas fa-images"></i>
                                </div>
                                <h3 class="fw-bold">Slider Manager</h3>
                                <p class="text-secondary mb-3">Manage hero slider images and content</p>
                                <span class="badge bg-primary"><?php echo $sliders_count; ?> Images</span>
                            </a>
                        </div>

                        <!-- Member Stories -->
                        <div class="col-md-6 col-lg-4">
                            <a href="member-stories.php" class="menu-card dashboard-card text-center">
                                <div class="menu-card-icon text-success">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <h3 class="fw-bold">Member Stories</h3>
                                <p class="text-secondary mb-3">सदस्यों की कहानियां प्रबंधित करें</p>
                                <span class="badge bg-success"><?php echo $stories_count; ?> Stories</span>
                            </a>
                        </div>

                        <!-- Register New Admin -->
                        <div class="col-md-6 col-lg-4">
                            <a href="register.php" class="menu-card dashboard-card text-center">
                                <div class="menu-card-icon text-info">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <h3 class="fw-bold">Register Admin</h3>
                                <p class="text-secondary mb-3">Create new admin user account</p>
                                <span class="badge bg-info">User Management</span>
                            </a>
                        </div>

                        <!-- Update Member Created Date -->
                        <div class="col-md-6 col-lg-4">
                            <a href="update-member-created-date.php" class="menu-card dashboard-card text-center">
                                <div class="menu-card-icon text-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h3 class="fw-bold">Update Member Date</h3>
                                <p class="text-secondary mb-3">Member के created_at date को अपडेट करें</p>
                                <span class="badge bg-info">Utility Tool</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="dashboard-card">
                    <h2 class="fw-bold mb-4">📋 Usage Instructions</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="text-primary mb-3">🖼️ Slider Manager</h4>
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Add Slider:</strong> Click "Add Slider" and fill in heading, description, button text/link, and upload image</li>
                                <li class="mb-2"><strong>Recommended Size:</strong> 1920x600px</li>
                                <li class="mb-2"><strong>Edit:</strong> Click edit button to modify existing sliders</li>
                                <li class="mb-2"><strong>Delete:</strong> Remove sliders permanently (image also deleted)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h4 class="text-success mb-3">👤 Member Stories</h4>
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>Add Story:</strong> Click "Add Story" and fill member details</li>
                                <li class="mb-2"><strong>Passport Photo:</strong> Upload square passport-sized photo (200x250px)</li>
                                <li class="mb-2"><strong>Rating:</strong> Select star rating (1-5 stars)</li>
                                <li class="mb-2"><strong>Edit:</strong> Click edit to modify member information</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-common.js"></script>
