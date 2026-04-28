<?php
// Transparency Dashboard - Poll Progress
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Get active polls and their statistics
$pollStats = [];
$result = $conn->query("
    SELECT 
        p.*,
        m.full_name as beneficiary_full_name,
        COUNT(pm.id) as total_poll_members,
        SUM(CASE WHEN pm.payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
        SUM(pm.paid_amount) as total_collected
    FROM polls p
    LEFT JOIN members m ON p.beneficiary_id = m.member_id
    LEFT JOIN poll_members pm ON p.id = pm.poll_id
    WHERE p.status = 'Active'
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

if ($result) {
    $pollStats = $result->fetch_all(MYSQLI_ASSOC);
}

// Overall statistics
$totalStats = $conn->query("
    SELECT 
        COUNT(DISTINCT p.id) as total_polls,
        COUNT(DISTINCT m.member_id) as total_members,
        SUM(pm.paid_amount) as total_amount_collected,
        COUNT(DISTINCT CASE WHEN pm.payment_status = 'Paid' THEN pm.id END) as total_payments
    FROM polls p
    LEFT JOIN poll_members pm ON p.id = pm.poll_id
    CROSS JOIN members m
    WHERE p.status = 'Active'
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पारदर्शिता डैशबोर्ड - BRCT भारत ट्रस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/poll-transparency.css">
</head>
<body>
    <?php include '../components/top-header.php'; ?>
    <?php $navbar_sticky = true; include '../components/navbar.php'; ?>

    <div class="transparency-section py-5">
        <div class="container">
            <!-- Header -->
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h1 class="mb-3">
                        <i class="fas fa-chart-pie text-danger"></i>
                        <br>पारदर्शिता डैशबोर्ड
                    </h1>
                    <p class="text-muted">पोल प्रगति और संग्रह की जानकारी</p>
                </div>
            </div>

            <!-- Overall Statistics Cards -->
            <div class="row mb-5">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-poll"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalStats['total_polls'] ?? 0; ?></h3>
                            <p>सक्रिय पोल</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalStats['total_members'] ?? 0; ?></h3>
                            <p>कुल सदस्य</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <h3>₹<?php echo number_format($totalStats['total_amount_collected'] ?? 0); ?></h3>
                            <p>कुल संग्रह</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalStats['total_payments'] ?? 0; ?></h3>
                            <p>भुगतान दर्ज</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Polls Section -->
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-4"><i class="fas fa-list me-2"></i>सक्रिय पोल</h2>

                    <?php if (empty($pollStats)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>कोई सक्रिय पोल नहीं है।
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($pollStats as $poll): 
                                $paidCount = $poll['paid_count'] ?? 0;
                                $totalMembers = $poll['total_poll_members'] ?? 0;
                                $percentage = $totalMembers > 0 ? ($paidCount / $totalMembers) * 100 : 0;
                                $collected = $poll['total_collected'] ?? 0;
                            ?>
                                <div class="col-lg-6 mb-4">
                                    <div class="poll-card">
                                        <div class="poll-card-header">
                                            <h5><?php echo $poll['poll_name']; ?></h5>
                                            <span class="poll-code">पोल: <?php echo $poll['poll_code']; ?></span>
                                        </div>

                                        <div class="poll-card-body">
                                            <!-- Beneficiary Info -->
                                            <div class="beneficiary-box">
                                                <strong>लाभार्थी:</strong><br>
                                                <?php echo $poll['beneficiary_full_name'] ?? 'N/A'; ?>
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="mt-4">
                                                <div class="progress-label">
                                                    <span>भुगतान प्राप्त</span>
                                                    <span><?php echo round($percentage); ?>%</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                                </div>
                                                <div class="progress-text">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?php echo $paidCount; ?> / <?php echo $totalMembers; ?> सदस्यों ने भुगतान किया
                                                </div>
                                            </div>

                                            <!-- Amount Collected -->
                                            <div class="amount-box mt-4">
                                                <p class="mb-2">संग्रह की गई राशि</p>
                                                <h3 class="text-success">₹<?php echo number_format($collected); ?></h3>
                                            </div>

                                            <!-- Poll Details -->
                                            <div class="poll-details mt-4">
                                                <div class="detail-item">
                                                    <span class="label">पोल प्रकार:</span>
                                                    <span class="value">
                                                        <?php echo $poll['poll_type'] === 'vivah' ? 'विवाह सहायता' : 'मृत्यु लाभ'; ?>
                                                    </span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="label">दान राशि:</span>
                                                    <span class="value">₹<?php echo $poll['donation_amount']; ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="label">शुरुआत तिथि:</span>
                                                    <span class="value"><?php echo date('d-M-Y', strtotime($poll['start_date'])); ?></span>
                                                </div>
                                                <div class="detail-item">
                                                    <span class="label">स्थिति:</span>
                                                    <span class="value">
                                                        <span class="badge bg-success">सक्रिय</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/poll-transparency.js"></script>
</body>
</html>
