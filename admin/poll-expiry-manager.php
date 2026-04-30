<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';
require_once '../includes/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all polls
$polls = [];
$result = $conn->query("SELECT * FROM poll ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $polls[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Date Manager - Admin - BRCT Bharat Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/poll-expiry-manager.css">
    
    <!-- Dynamic Base URL Configuration -->
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
                        <i class="fas fa-poll me-2 text-primary"></i>Poll Date Manager
                    </h1>
                    <p class="text-secondary mb-0">Claim Number से poll records को search करके start और expiry dates को अपडेट करें।</p>
                </div>

                <!-- Success/Error Alert -->
                <div id="alert-container"></div>

                <!-- Search Section -->
                <div class="card search-section">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-search me-2"></i>Claim Number से Search करें
                        </h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search-claim-number" 
                                        placeholder="Claim Number दर्ज करें (उदा: BRCT-D202604250013 या BVA202604196995)">
                                    <button class="btn btn-primary" id="search-btn" type="button">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                    <button class="btn btn-secondary" id="show-all-btn" type="button">
                                        <i class="fas fa-list me-1"></i>Show All
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Count -->
                <div id="results-info" class="mb-3" style="display: none;">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        <span id="result-count">0</span> poll record(s) मिले।
                    </p>
                </div>

                <!-- Polls Table -->
                <div class="poll-table" id="polls-table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Claim Number</th>
                                    <th>Application Type</th>
                                    <th>Poll</th>
                                    <th>Alert</th>
                                    <th>Start Date</th>
                                    <th>Expiry Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="polls-table-body" data-polls="<?php echo htmlspecialchars(json_encode($polls), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php if (empty($polls)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Search किया हुआ record यहाँ दिखेगा या सभी polls को "Show All" बटन से देखें।
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($polls as $poll): ?>
                                        <tr class="poll-row" data-poll-id="<?php echo $poll['id']; ?>">
                                            <td><small class="text-muted">#<?php echo $poll['id']; ?></small></td>
                                            <td>
                                                <code class="bg-light p-1"><?php echo htmlspecialchars($poll['claim_number']); ?></code>
                                            </td>
                                            <td>
                                                <?php if ($poll['application_type'] == 'Death_Claims'): ?>
                                                    <span class="badge badge-death">मृत्यु सहयोग</span>
                                                <?php else: ?>
                                                    <span class="badge badge-vivah">बेटी विवाह</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($poll['poll']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?php echo $poll['alert']; ?></span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="date" class="form-control date-input start-date-input" 
                                                        data-poll-id="<?php echo $poll['id']; ?>"
                                                        value="<?php echo ($poll['start_poll_date'] != '0000-00-00' ? $poll['start_poll_date'] : ''); ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="date" class="form-control date-input expire-date-input" 
                                                        data-poll-id="<?php echo $poll['id']; ?>"
                                                        value="<?php echo $poll['expire_poll_date']; ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('d-M-Y H:i', strtotime($poll['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-success update-btn" 
                                                        data-poll-id="<?php echo $poll['id']; ?>" type="button">
                                                        <i class="fas fa-save"></i> Update
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-btn" 
                                                        data-poll-id="<?php echo $poll['id']; ?>" type="button">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/poll-expiry-manager.js"></script>
</body>
</html>
