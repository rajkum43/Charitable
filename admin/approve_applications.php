<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>बेटी विवाह आवेदन प्रबंधन - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin-common.css">
    <link rel="stylesheet" href="css/approve-applications.css">
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
                        <i class="fas fa-check-circle me-2 text-success"></i>बेटी विवाह आवेदन प्रबंधन
                    </h1>
                    <p class="text-secondary mb-0">सभी आवेदन यहाँ दिखेंगे। आप उन्हें स्वीकृति या अस्वीकार कर सकते हैं।</p>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">स्थिति फ़िल्टर करें</label>
                                <select id="filterStatus" class="form-select" onchange="loadApplications()">
                                    <option value="">-- सभी --</option>
                                    <option value="Pending">प्रतीक्षारत</option>
                                    <option value="Under Review">समीक्षाधीन</option>
                                    <option value="Approved">स्वीकृत</option>
                                    <option value="Rejected">अस्वीकृत</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">खोजें</label>
                                <input type="text" id="searchInput" class="form-control" placeholder="Member ID या नाम से खोजें" onkeyup="loadApplications()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-outline-secondary w-100" onclick="location.reload()">
                                    <i class="fas fa-sync me-2"></i>रीफ्रेश करें
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applications List -->
                <div class="card">
                    <div class="card-body p-0">
                        <div id="applicationsContainer">
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">आवेदन लोड हो रहे हैं...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Application Detail Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">आवेदन विवरण</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="applicationDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">बंद करें</button>
                    <button type="button" class="btn btn-danger" id="rejectBtn" onclick="rejectApplication()">
                        <i class="fas fa-times me-2"></i>अस्वीकार करें
                    </button>
                    <button type="button" class="btn btn-success" id="approveBtn" onclick="approveApplication()">
                        <i class="fas fa-check me-2"></i>स्वीकृति दें
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-common.js"></script>
    <script src="js/approve-applications.js"></script>
</body>
</html>
