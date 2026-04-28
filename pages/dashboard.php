<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get member details
$member_id = $_SESSION['member_id'];
$login_id = $_SESSION['login_id'];
$full_name = $_SESSION['full_name'];

// Fetch complete member data
$stmt = $conn->prepare("
    SELECT m.*, 
           (SELECT COUNT(*) FROM payment_receipts WHERE member_id = m.member_id) as receipt_count
    FROM members m
    WHERE m.member_id = ?
");
$stmt->bind_param("s", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            font-size: 1.3rem;
        }

        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }

        .sidebar h5 {
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }

        .sidebar p {
            margin-bottom: 12px;
            color: #666;
            font-size: 0.95rem;
        }

        .sidebar p strong {
            color: #333;
            font-weight: 600;
        }

        .main-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }

        .main-content h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }

        .stat-card {
            background: linear-gradient(135deg, #e6f0ff 0%, #f0f5ff 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }

        .stat-card h6 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .stat-card h3 {
            color: #0d6efd;
            font-weight: 700;
            font-size: 2rem;
        }

        .badge-status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding: 20px;
            }

            .main-content {
                padding: 20px;
            }
        }

        .navbar-nav .nav-link {
            color: #666 !important;
            margin-right: 20px;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #0d6efd !important;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 20px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            margin-top: -30px;
            margin-left: -30px;
            margin-right: -30px;
        }

        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include '../components/navbar.php'; ?>

    <!-- Dashboard Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="sidebar">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($member['full_name']); ?></h5>
                        
                        <p><strong>Member ID:</strong><br>
                        <span style="font-family: 'Courier New', monospace; font-weight: bold;"><?php echo htmlspecialchars($member['member_id']); ?></span></p>
                        
                        <p><strong>Login ID:</strong><br>
                        <span style="font-family: 'Courier New', monospace; font-weight: bold;"><?php echo htmlspecialchars($member['login_id']); ?></span></p>
                        
                        <p><strong>स्थिति:</strong><br>
                        <?php
                            if ($member['status'] == 0) {
                                echo '<span class="badge-status badge-pending"><i class="fas fa-clock me-2"></i>अनुमोदित नहीं</span>';
                            } elseif ($member['status'] == 1) {
                                echo '<span class="badge-status badge-approved"><i class="fas fa-check-circle me-2"></i>अनुमोदित</span>';
                            } else {
                                echo '<span class="badge-status badge-rejected"><i class="fas fa-times-circle me-2"></i>अस्वीकृत</span>';
                            }
                        ?>
                        </p>

                        <p><strong>पंजीकरण तारीख:</strong><br>
                        <?php echo date('d.m.Y', strtotime($member['created_at'])); ?></p>

                        <div style="margin-top: 20px; border-top: 1px solid #e9ecef; padding-top: 20px;">
                            <a href="logout.php" class="btn-logout w-100 text-center text-white text-decoration-none">
                                <i class="fas fa-sign-out-alt me-2"></i>लॉगआउट करें
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="main-content">
                        <div class="welcome-section">
                            <h1>स्वागत है, <?php echo htmlspecialchars(explode(' ', $member['full_name'])[0]); ?>!</h1>
                            <p>आपने BRCT Bharat Trust में सफलतापूर्वक पंजीकरण किया है।</p>
                        </div>

                        <h2>आपकी व्यक्तिगत जानकारी</h2>

                        <div class="info-grid">
                            <div class="stat-card">
                                <h6><i class="fas fa-phone me-2"></i>मोबाइल नंबर</h6>
                                <h3><?php echo htmlspecialchars($member['mobile_number']); ?></h3>
                            </div>

                            <div class="stat-card">
                                <h6><i class="fas fa-id-card me-2"></i>आधार नंबर</h6>
                                <h3><?php echo 'XXXX XXXX ' . substr($member['aadhar_number'], -4); ?></h3>
                            </div>

                            <div class="stat-card">
                                <h6><i class="fas fa-calendar me-2"></i>जन्म तारीख</h6>
                                <h3><?php echo date('d.m.Y', strtotime($member['date_of_birth'])); ?></h3>
                            </div>

                            <div class="stat-card">
                                <h6><i class="fas fa-file-upload me-2"></i>भुगतान रसीदें</h6>
                                <h3><?php echo $member['receipt_count']; ?></h3>
                            </div>
                        </div>

                        <h2 style="margin-top: 40px;">अधिक विवरण</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>पिता/पति का नाम:</strong> <?php echo htmlspecialchars($member['father_husband_name']); ?></p>
                                <p><strong>लिंग:</strong> <?php echo htmlspecialchars($member['gender']); ?></p>
                                <p><strong>व्यवसाय:</strong> <?php echo htmlspecialchars($member['occupation']); ?></p>
                                <p><strong>राज्य:</strong> <?php echo htmlspecialchars($member['state']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>जिला:</strong> <?php echo htmlspecialchars($member['district']); ?></p>
                                <p><strong>ब्लॉक:</strong> <?php echo htmlspecialchars($member['block']); ?></p>
                                <p><strong>ईमेल:</strong> <?php echo htmlspecialchars($member['email'] ?? 'न दर्ज'); ?></p>
                                <p><strong>अपडेट की तारीख:</strong> <?php echo date('d.m.Y H:i', strtotime($member['updated_at'])); ?></p>
                            </div>
                        </div>

                        <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e9ecef;">
                            <h6><i class="fas fa-info-circle me-2 text-info"></i>नोट</h6>
                            <p style="color: #666; font-size: 0.95rem;">
                                <?php if ($member['status'] == 0): ?>
                                    आपका पंजीकरण अभी अनुमोदित नहीं हुआ है। कृपया व्यवस्थापक से संपर्क करें। एक बार अनुमोदित होने के बाद, आप सभी सुविधाओं का उपयोग कर सकेंगे।
                                <?php elseif ($member['status'] == 1): ?>
                                    आपका पंजीकरण सफलतापूर्वक अनुमोदित हो गया है। BRCT Bharat Trust का सदस्य बनने के लिए बधाई!
                                <?php else: ?>
                                    आपका पंजीकरण अस्वीकार कर दिया गया है। विवरण के लिए व्यवस्थापक से संपर्क करें।
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
