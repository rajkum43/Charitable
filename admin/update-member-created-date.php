<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';
require_once '../includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_date') {
        $member_id = isset($_POST['member_id']) ? trim($_POST['member_id']) : '';
        $new_date = isset($_POST['new_date']) ? trim($_POST['new_date']) : '';

        if (empty($member_id) || empty($new_date)) {
            echo json_encode(['success' => false, 'message' => 'Member ID और Date दोनों आवश्यक हैं']);
            exit;
        }

        // Validate date format
        $date_obj = DateTime::createFromFormat('Y-m-d H:i:s', $new_date);
        if (!$date_obj || $date_obj->format('Y-m-d H:i:s') !== $new_date) {
            echo json_encode(['success' => false, 'message' => 'Invalid date format. कृपया YYYY-MM-DD HH:MM:SS format में डेट दें']);
            exit;
        }

        // Check if member exists
        $check_stmt = $conn->prepare("SELECT member_id FROM members WHERE member_id = ?");
        $check_stmt->bind_param("s", $member_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Member record नहीं मिला']);
            exit;
        }

        // Update the created_at date
        $update_stmt = $conn->prepare("UPDATE members SET created_at = ? WHERE member_id = ?");
        $update_stmt->bind_param("ss", $new_date, $member_id);

        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Created date successfully updated',
                'member_id' => htmlspecialchars($member_id),
                'new_date' => htmlspecialchars($new_date)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
        }
        exit;
    }

    if ($action === 'get_member') {
        $member_id = isset($_POST['member_id']) ? trim($_POST['member_id']) : '';

        if (empty($member_id)) {
            echo json_encode(['success' => false, 'message' => 'Member ID आवश्यक है']);
            exit;
        }

        $stmt = $conn->prepare("SELECT member_id, full_name, created_at FROM members WHERE member_id = ?");
        $stmt->bind_param("s", $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $member = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'member' => [
                    'member_id' => htmlspecialchars($member['member_id']),
                    'full_name' => htmlspecialchars($member['full_name']),
                    'created_at' => $member['created_at']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Member record नहीं मिला']);
        }
        exit;
    }
}

// Fetch all members for the list
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where_clause = '';
$where_params = [];
$param_types = '';

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_clause = "WHERE member_id LIKE ? OR full_name LIKE ? OR email LIKE ?";
    $where_params = [$search_term, $search_term, $search_term];
    $param_types = 'sss';
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM members $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($where_params)) {
    $stmt->bind_param($param_types, ...$where_params);
}
$stmt->execute();
$count_result = $stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Get members with pagination
$query = "SELECT member_id, full_name, email, created_at FROM members $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
if (!empty($where_params)) {
    $all_params = array_merge($where_params, [$limit, $offset]);
    $stmt->bind_param($param_types . 'ii', ...$all_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$members = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Created Date अपडेट करें - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <style>
        .date-row:hover {
            background-color: #f8f9fa;
        }
        .edit-date-btn {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .member-info-badge {
            background-color: #e7f3ff;
            color: #0066cc;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
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
                <!-- Page Header -->
                <div class="mb-4">
                    <h1 class="fw-bold mb-2">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Member Created Date अपडेट
                    </h1>
                    <p class="text-secondary mb-0">Members के `created_at` timestamp को अपडेट करने के लिए नीचे दिए गए form का उपयोग करें।</p>
                </div>

                <!-- Quick Update Form -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Quick Update Form</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Member ID</strong></label>
                                <input type="text" id="quickMemberId" class="form-control" placeholder="e.g., MEM001" autocomplete="off">
                                <small class="text-muted d-block mt-2">Member की ID दर्ज करें</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>New Created Date & Time</strong></label>
                                <input type="datetime-local" id="quickNewDate" class="form-control">
                                <small class="text-muted d-block mt-2">नया date-time select करें</small>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary w-100" onclick="quickUpdateDate()">
                                    <i class="fas fa-sync me-2"></i>अपडेट करें
                                </button>
                            </div>
                        </div>
                        <div id="quickUpdateResult" class="mt-3"></div>
                    </div>
                </div>

                <!-- Search and List -->
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Members List</h5>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Member ID, नाम या Email से खोजें..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-outline-secondary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <?php if ($total === 0): ?>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">कोई record नहीं मिला</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 15%;">Member ID</th>
                                            <th style="width: 25%;">Full Name</th>
                                            <th style="width: 25%;">Email</th>
                                            <th style="width: 20%;">Created Date</th>
                                            <th style="width: 15%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($member = $members->fetch_assoc()): ?>
                                            <tr class="date-row">
                                                <td>
                                                    <span class="member-info-badge"><?php echo htmlspecialchars($member['member_id']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                                <td>
                                                    <small class="text-muted"><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold"><?php echo date('d/m/Y H:i:s', strtotime($member['created_at'])); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning edit-date-btn" 
                                                            onclick="openEditModal('<?php echo htmlspecialchars($member['member_id']); ?>', '<?php echo htmlspecialchars($member['full_name']); ?>')">
                                                        <i class="fas fa-edit me-1"></i>Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted">Total: <?php echo $total; ?> members (Page <?php echo $page; ?> of <?php echo $total_pages; ?>)</small>
                                <nav>
                                    <ul class="pagination mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">First</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>

                                        <?php 
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        
                                        for ($i = $start_page; $i <= $end_page; $i++): 
                                        ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Next</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Last</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editDateModalLabel">
                        <i class="fas fa-calendar-edit me-2"></i>Update Created Date
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="memberDetailBox" class="mb-3 p-3 bg-light rounded"></div>

                    <div class="mb-3">
                        <label for="editCurrentDate" class="form-label"><strong>Current Created Date</strong></label>
                        <input type="text" id="editCurrentDate" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>

                    <div class="mb-3">
                        <label for="editNewDate" class="form-label"><strong>New Created Date & Time</strong></label>
                        <input type="datetime-local" id="editNewDate" class="form-control" required>
                        <small class="text-muted d-block mt-2">Format: YYYY-MM-DD HH:MM (24-hour format)</small>
                    </div>

                    <div id="editModalMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitEditDate()">
                        <i class="fas fa-check me-2"></i>Update Date
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-common.js"></script>

    <script>
        let editingMemberId = null;

        function quickUpdateDate() {
            const memberId = document.getElementById('quickMemberId').value.trim();
            const newDate = document.getElementById('quickNewDate').value;

            if (!memberId || !newDate) {
                showResult('quickUpdateResult', 'error', 'कृपया Member ID और Date दोनों दर्ज करें');
                return;
            }

            // Convert datetime-local format to MySQL format
            const [datePart, timePart] = newDate.split('T');
            const mysqlDate = `${datePart} ${timePart}:00`;

            const formData = new FormData();
            formData.append('action', 'update_date');
            formData.append('member_id', memberId);
            formData.append('new_date', mysqlDate);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('quickUpdateResult', 'success', 
                        `✓ Successfully Updated! Member: ${data.member_id} - New Date: ${data.new_date}`);
                    document.getElementById('quickMemberId').value = '';
                    document.getElementById('quickNewDate').value = '';
                    
                    // Reload page after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showResult('quickUpdateResult', 'error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showResult('quickUpdateResult', 'error', 'Request failed. कृपया फिर से कोशिश करें');
            });
        }

        function openEditModal(memberId, fullName) {
            editingMemberId = memberId;
            const modal = new bootstrap.Modal(document.getElementById('editDateModal'));

            const formData = new FormData();
            formData.append('action', 'get_member');
            formData.append('member_id', memberId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const member = data.member;
                    document.getElementById('memberDetailBox').innerHTML = 
                        `<strong>Member ID:</strong> ${member.member_id}<br>
                         <strong>Full Name:</strong> ${member.full_name}`;
                    
                    const currentDate = new Date(member.created_at);
                    document.getElementById('editCurrentDate').value = 
                        currentDate.toLocaleString('en-IN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    
                    // Set current date in the input
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const hours = String(currentDate.getHours()).padStart(2, '0');
                    const minutes = String(currentDate.getMinutes()).padStart(2, '0');
                    
                    document.getElementById('editNewDate').value = `${year}-${month}-${day}T${hours}:${minutes}`;
                    
                    document.getElementById('editModalMessage').innerHTML = '';
                    modal.show();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to fetch member data');
            });
        }

        function submitEditDate() {
            const newDate = document.getElementById('editNewDate').value;

            if (!newDate) {
                showEditResult('error', 'कृपया नया date-time सेलेक्ट करें');
                return;
            }

            // Convert datetime-local format to MySQL format
            const [datePart, timePart] = newDate.split('T');
            const mysqlDate = `${datePart} ${timePart}:00`;

            const formData = new FormData();
            formData.append('action', 'update_date');
            formData.append('member_id', editingMemberId);
            formData.append('new_date', mysqlDate);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showEditResult('success', '✓ Date successfully updated!');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showEditResult('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showEditResult('error', 'Request failed. कृपया फिर से कोशिश करें');
            });
        }

        function showResult(elementId, type, message) {
            const element = document.getElementById(elementId);
            element.innerHTML = `<div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show mb-0" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        }

        function showEditResult(type, message) {
            showResult('editModalMessage', type, message);
        }
    </script>
</body>
</html>
