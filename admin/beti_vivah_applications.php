<?php
// Admin Dashboard for Beti Vivah Applications
require_once 'includes/auth.php';
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('डेटाबेस कनेक्शन विफल: ' . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $remarks = !empty($_POST['remarks']) ? htmlspecialchars(trim($_POST['remarks'])) : '';
    
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    
    $update_sql = "UPDATE beti_vivah_aavedan SET status = ?, remarks = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('ssi', $status, $remarks, $application_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "आवेदन $status किया गया";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'त्रुटि: ' . $stmt->error;
        $_SESSION['message_type'] = 'danger';
    }
    $stmt->close();
    
    header('Location: beti_vivah_applications.php');
    exit;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Build query
$query = "SELECT * FROM beti_vivah_aavedan WHERE 1=1";
$params = [];
$types = '';

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (application_number LIKE ? OR member_name LIKE ? OR bride_name LIKE ? OR mobile_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$query .= " ORDER BY created_at DESC LIMIT 50";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>बेटी विवाह आवेदन प्रबंधन - Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <style>
        .app-card {
            background: white;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .app-card.approved {
            border-left-color: #28a745;
            background-color: #f0f8f5;
        }
        .app-card.rejected {
            border-left-color: #dc3545;
            background-color: #fdf5f5;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        .status-rejected {
            background-color: #dc3545;
            color: white;
        }
    </style>
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
                <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>बेटी विवाह आवेदन प्रबंधन</h2>
                
                <!-- Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
                    <?php endif; ?>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="खोजें (नाम, Member ID, फोन)">
                                </div>
                                <div class="col-md-4">
                                    <select name="status" class="form-select">
                                        <option value="">-- सभी स्थिति --</option>
                                        <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>खोजें
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Applications List -->
                    <div class="applications-container">
                        <?php if (empty($applications)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>कोई आवेदन नहीं मिला
                            </div>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <div class="app-card <?php echo strtolower($app['status']); ?>">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="mb-2">
                                                <strong><?php echo $app['application_number']; ?></strong>
                                                <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                                    <?php echo $app['status']; ?>
                                                </span>
                                            </h5>
                                            <p class="mb-1"><strong>आवेदक:</strong> <?php echo $app['member_name']; ?> (ID: <?php echo $app['member_id']; ?>)</p>
                                            <p class="mb-1"><strong>बेटी:</strong> <?php echo $app['bride_name']; ?></p>
                                            <p class="mb-1"><strong>वर:</strong> <?php echo $app['groom_name']; ?></p>
                                            <p class="mb-1"><strong>आवेदन तिथि:</strong> <?php echo date('d-m-Y H:i', strtotime($app['created_at'])); ?></p>
                                            <p class="mb-0"><strong>पारिवारिक आय:</strong> ₹<?php echo number_format($app['family_income']); ?></p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <button class="btn btn-sm btn-info mb-2" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $app['id']; ?>">
                                                <i class="fas fa-eye me-1"></i>विवरण देखें
                                            </button>
                                            <?php if ($app['status'] === 'Pending'): ?>
                                                <div>
                                                    <button class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $app['id']; ?>">
                                                        <i class="fas fa-check me-1"></i>मंजूर करें
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $app['id']; ?>">
                                                        <i class="fas fa-times me-1"></i>अस्वीकार करें
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal<?php echo $app['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">आवेदन विवरण - <?php echo $app['application_number']; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6 class="mb-3"><strong>सदस्य जानकारी</strong></h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6"><p><strong>नाम:</strong> <?php echo $app['member_name']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>Member ID:</strong> <?php echo $app['member_id']; ?></p></div>
                                                </div>

                                                <h6 class="mb-3"><strong>बेटी की जानकारी</strong></h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6"><p><strong>नाम:</strong> <?php echo $app['bride_name']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>जन्म तिथि:</strong> <?php echo date('d-m-Y', strtotime($app['bride_dob'])); ?></p></div>
                                                    <div class="col-md-6"><p><strong>स्वास्थ्य:</strong> <?php echo $app['bride_health']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>शिक्षा:</strong> <?php echo $app['bride_education'] ?: 'N/A'; ?></p></div>
                                                </div>

                                                <h6 class="mb-3"><strong>पारिवारिक विवरण</strong></h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6"><p><strong>आय (वार्षिक):</strong> ₹<?php echo number_format($app['family_income']); ?></p></div>
                                                    <div class="col-md-6"><p><strong>सदस्य संख्या:</strong> <?php echo $app['family_members']; ?></p></div>
                                                    <div class="col-md-12"><p><strong>पता:</strong> <?php echo $app['address']; ?>, <?php echo $app['city']; ?>, <?php echo $app['district']; ?>, <?php echo $app['state']; ?></p></div>
                                                </div>

                                                <h6 class="mb-3"><strong>वर की जानकारी</strong></h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6"><p><strong>नाम:</strong> <?php echo $app['groom_name']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>उम्र:</strong> <?php echo $app['groom_age']; ?> वर्ष</p></div>
                                                    <div class="col-md-6"><p><strong>व्यवसाय:</strong> <?php echo $app['groom_occupation']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>शिक्षा:</strong> <?php echo $app['groom_education'] ?: 'N/A'; ?></p></div>
                                                </div>

                                                <h6 class="mb-3"><strong>बैंक विवरण</strong></h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6"><p><strong>IFSC:</strong> <?php echo $app['ifsc_code']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>बैंक:</strong> <?php echo $app['bank_name']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>शाखा:</strong> <?php echo $app['branch_name']; ?></p></div>
                                                    <div class="col-md-6"><p><strong>खाता:</strong> ****<?php echo substr($app['account_number'], -4); ?></p></div>
                                                    <div class="col-md-6"><p><strong>UPI:</strong> <?php echo $app['upi_id'] ?: 'N/A'; ?></p></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal<?php echo $app['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">आवेदन मंजूर करें</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <p>क्या आप इस आवेदन को मंजूर करना चाहते हैं?</p>
                                                    <textarea name="remarks" class="form-control" rows="3" placeholder="अनिवार्य टिप्पणियाँ (वैकल्पिक)"></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                                                    <button type="submit" class="btn btn-success">मंजूर करें</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal<?php echo $app['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">आवेदन अस्वीकार करें</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <p class="text-danger">⚠️ क्या आप सुनिश्चित हैं कि आप इस आवेदन को अस्वीकार करना चाहते हैं?</p>
                                                    <label class="form-label">अस्वीकार करने का कारण (अनिवार्य):</label>
                                                    <textarea name="remarks" class="form-control" rows="3" placeholder="अस्वीकार करने का विस्तृत कारण दर्ज करें" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">रद्द करें</button>
                                                    <button type="submit" class="btn btn-danger">अस्वीकार करें</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-common.js"></script>

</body>
</html>
<?php
$conn->close();
?>
