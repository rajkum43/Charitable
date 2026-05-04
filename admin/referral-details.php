<?php
// Admin Referral Details Page
require_once 'includes/auth.php';
require_once '../includes/config.php';

$referrer = isset($_GET['referrer']) ? preg_replace('/\D/', '', $_GET['referrer']) : '';
if (!$referrer) {
    header('Location: referred-members.php');
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$referrerStmt = $conn->prepare("SELECT member_id, full_name FROM members WHERE RIGHT(member_id, 7) = ? LIMIT 1");
$referrerStmt->bind_param('s', $referrer);
$referrerStmt->execute();
$referrerResult = $referrerStmt->get_result();
$referrerInfo = $referrerResult->fetch_assoc();
$referrerStmt->close();

$referralsStmt = $conn->prepare("SELECT member_id, full_name, father_husband_name, mobile_number, permanent_address, created_at FROM members WHERE referrer_member_id = ? ORDER BY created_at DESC");
$referralsStmt->bind_param('s', $referrer);
$referralsStmt->execute();
$referralsResult = $referralsStmt->get_result();
$referrals = [];
while ($row = $referralsResult->fetch_assoc()) {
    $referrals[] = $row;
}
$referralsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Details - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper">
        <div class="main-content p-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Referral Details</h1>
                        <p class="text-muted mb-0">List of members referred by this member.</p>
                    </div>
                    <a href="referred-members.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Report
                    </a>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <h5 class="mb-1">Referrer Information</h5>
                            <?php if ($referrerInfo): ?>
                                <p class="mb-0"><strong>Name:</strong> <?php echo htmlspecialchars($referrerInfo['full_name']); ?></p>
                                <p class="mb-0"><strong>Member ID:</strong> <?php echo htmlspecialchars($referrerInfo['member_id']); ?></p>
                                <p class="mb-0"><strong>Stored Referrer ID:</strong> <?php echo htmlspecialchars($referrer); ?></p>
                            <?php else: ?>
                                <p class="text-warning">Referrer member information not found for this ID.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">क्र.सं.</th>
                                        <th>सदस्य ID</th>
                                        <th>नाम</th>
                                        <th>पिता/पति का नाम</th>
                                        <th>मोबाइल</th>
                                        <th>पता</th>
                                        <th>शामिल होने की तारीख</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($referrals) === 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">कोई रेफरल सदस्य नहीं मिला।</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($referrals as $index => $member): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($member['member_id']); ?></td>
                                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['father_husband_name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['mobile_number']); ?></td>
                                                <td><?php echo htmlspecialchars($member['permanent_address']); ?></td>
                                                <td><?php echo htmlspecialchars($member['created_at']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>