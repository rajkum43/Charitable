<?php
// Admin Referral Report Page
require_once 'includes/auth.php';
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "
    SELECT
        RIGHT(m.member_id, 7) AS referrer_member_id,
        m.full_name AS referrer_name,
        m.member_id AS full_member_id,
        COUNT(r.id) AS referral_count
    FROM members m
    JOIN members r ON r.referrer_member_id = RIGHT(m.member_id, 7)
    GROUP BY referrer_member_id
    HAVING referral_count > 0
    ORDER BY referral_count DESC
";
$result = $conn->query($query);
$referrers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $referrers[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Report - Admin Panel</title>
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
                        <h1 class="h3 mb-1">Referral Report</h1>
                        <p class="text-muted mb-0">List of members who have referred other members.</p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">क्र.सं.</th>
                                        <th>Refer करने वाले सदस्य का नाम</th>
                                        <th>Member ID</th>
                                        <th class="text-center">Referral Count</th>
                                        <th class="text-center">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($referrers) === 0): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">कोई रेफरल रिकॉर्ड नहीं मिला।</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($referrers as $index => $referrer): ?>
                                            <tr>
                                                <td class="text-center"><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($referrer['referrer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($referrer['full_member_id']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($referrer['referral_count']); ?></td>
                                                <td class="text-center">
                                                    <a href="referral-details.php?referrer=<?php echo urlencode($referrer['referrer_member_id']); ?>" class="btn btn-sm btn-primary">
                                                        Details
                                                    </a>
                                                </td>
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