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
    <title>मृत्यु सहायता आवेदन प्रबंधन - Admin Panel</title>
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
                        <i class="fas fa-heart-broken me-2 text-danger"></i>मृत्यु सहायता आवेदन प्रबंधन
                    </h1>
                    <p class="text-secondary mb-0">सभी मृत्यु सहायता आवेदन यहाँ दिखेंगे। आप उन्हें स्वीकृति या अस्वीकार कर सकते हैं।</p>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value" id="pendingCount">0</div>
                                <div class="stat-label">प्रतीक्षारत</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-magnifying-glass"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value" id="reviewCount">0</div>
                                <div class="stat-label">समीक्षाधीन</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value" id="approvedCount">0</div>
                                <div class="stat-label">स्वीकृत</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value" id="rejectedCount">0</div>
                                <div class="stat-label">अस्वीकृत</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">स्थिति फ़िल्टर करें</label>
                                <select id="filterStatus" class="form-select" onchange="loadClaims()">
                                    <option value="">-- सभी --</option>
                                    <option value="Pending">प्रतीक्षारत</option>
                                    <option value="Under Review">समीक्षाधीन</option>
                                    <option value="Approved">स्वीकृत</option>
                                    <option value="Rejected">अस्वीकृत</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">खोजें</label>
                                <input type="text" id="searchInput" class="form-control" placeholder="Claim ID, Member ID या नाम से खोजें" onkeyup="loadClaims()">
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

                <!-- Claims List -->
                <div class="card">
                    <div class="card-body p-0">
                        <div id="claimsContainer">
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

    <!-- Claim Detail Modal -->
    <div class="modal fade" id="claimModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalTitle">मृत्यु सहायता आवेदन विवरण</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="claimDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">बंद करें</button>
                    <button type="button" class="btn btn-danger" id="rejectBtn" onclick="rejectClaim()">
                        <i class="fas fa-times me-2"></i>अस्वीकार करें
                    </button>
                    <button type="button" class="btn btn-success" id="approveBtn" onclick="approveClaim()">
                        <i class="fas fa-check me-2"></i>स्वीकृति दें
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-common.js"></script>
    <script src="js/death-claims-management.js"></script>
</body>
</html>
