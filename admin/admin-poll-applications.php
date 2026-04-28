<?php
// Admin - Poll Application Approval Page
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Get all pending applications
$applications = [];
$result = $conn->query("
    SELECT pa.*, m.full_name, m.member_id 
    FROM poll_applications pa
    JOIN members m ON pa.member_id = m.member_id
    WHERE pa.status = 'Pending'
    ORDER BY pa.created_at DESC
");

if ($result) {
    $applications = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आवेदन अनुमोदन - BRCT प्रशासन</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-poll-applications.css">
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>आवेदन अनुमोदन</h1>
                <p class="text-muted mt-2">विवाह और मृत्यु लाभ आवेदनों को अनुमोदित करें</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">लंबित आवेदन (<?php echo count($applications); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>कोई लंबित आवेदन नहीं है
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>आवेदन ID</th>
                                            <th>सदस्य नाम</th>
                                            <th>सदस्य ID</th>
                                            <th>आवेदन प्रकार</th>
                                            <th>आवेदन तिथि</th>
                                            <th>कार्रवाई</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                            <tr>
                                                <td>#<?php echo $app['id']; ?></td>
                                                <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                                <td><?php echo $app['member_id']; ?></td>
                                                <td>
                                                    <?php if ($app['type'] === 'vivah'): ?>
                                                        <span class="badge bg-pink">विवाह सहायता</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-dark">मृत्यु लाभ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d-M-Y', strtotime($app['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success view-details" 
                                                            data-app-id="<?php echo $app['id']; ?>"
                                                            data-bs-toggle="modal" data-bs-target="#approvalModal">
                                                        <i class="fas fa-eye me-1"></i>देखें
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">आवेदन विवरण और अनुमोदन</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="applicationDetails">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">बंद करें</button>
                    <button type="button" class="btn btn-success" id="approveBtn">
                        <i class="fas fa-check me-1"></i>अनुमोदित करें
                    </button>
                    <button type="button" class="btn btn-danger" id="rejectBtn">
                        <i class="fas fa-times me-1"></i>अस्वीकार करें
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-poll-applications.js"></script>
</body>
</html>
