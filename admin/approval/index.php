<?php
// Security Check - Required for all admin pages
require_once '../includes/auth.php';
require_once '../../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सदस्य अनुमोदन - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../css/admin-common.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/approval.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <div class="main-content">
            <div class="container-fluid">
            <!-- Page Header -->
            <div class="mb-3 mb-md-4 mb-lg-5">
                <h1 class="fw-bold mb-2">
                    <i class="fas fa-check-circle me-2 text-primary"></i>सदस्य अनुमोदन
                </h1>
                <p class="text-secondary mb-0">पंजीकृत सदस्यों का विवरण देखें और अनुमोदन या अस्वीकार करें।</p>
            </div>

<!-- Statistics -->
            <div class="row mb-3 mb-md-4 mb-lg-5 row-gap-3">
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="stat-box bg-orange">
                        <i class="fas fa-hourglass-half fa-2x mb-3"></i>
                        <h3 id="pendingCount">0</h3>
                        <p>लंबित सदस्य</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="stat-box bg-green">
                        <i class="fas fa-check-circle fa-2x mb-3"></i>
                        <h3 id="approvedCount">0</h3>
                        <p>अनुमोदित सदस्य</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="stat-box bg-red">
                        <i class="fas fa-times-circle fa-2x mb-3"></i>
                        <h3 id="rejectedCount">0</h3>
                        <p>अस्वीकृत सदस्य</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="stat-box bg-blue">
                        <i class="fas fa-users fa-2x mb-3"></i>
                        <h3 id="totalCount">0</h3>
                        <p>कुल सदस्य</p>
                    </div>
                </div>
            </div>

            <!-- Management Card -->
            <div class="dashboard-card mb-3 mb-md-4">
                <!-- Filter Section -->
                <div class="filter-section">
            <div class="filter-controls">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="नाम, ID, मोबाइल या ईमेल से खोजें..."
                        class="form-control"
                    >
                </div>

                <div class="filter-group">
                    <label>स्थिति</label>
                    <select id="statusFilter" class="form-select">
                        <option value="0">लंबित</option>
                        <option value="1">अनुमोदित</option>
                        <option value="2">अस्वीकृत</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button id="filterBtn" class="btn btn-primary">
                        <i class="fas fa-filter"></i> लागू करें
                    </button>
                    <button id="resetBtn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> रीसेट करें
                    </button>
                </div>
            </div>
        </div>

        <!-- Members Table Section -->
        <div class="table-section">
            <div class="table-title">
                <h2>पंजीकृत सदस्य</h2>
            </div>

            <div class="table-responsive">
                <table class="members-table table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>नाम</th>
                            <th>मोबाइल</th>
                            <th>ईमेल</th>
                            <th>आधार</th>
                            <th>भुगतान</th>
                            <th>तारीख</th>
                            <th>क्रिया</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <tr>
                            <td colspan="8" class="loading">
                                <div class="spinner"></div> सदस्य लोड हो रहे हैं...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Member Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>सदस्य विवरण</h2>
                <button type="button" class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Details will be loaded via JavaScript -->
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">बंद करें</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="assets/js/approval.js"></script>

    <!-- Override logout for approval page -->
    <script>
    function handleLogout() {
        if (confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
            try {
                fetch('api/logout.php', {
                    method: 'POST'
                }).then(response => response.json())
                  .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || '../index.php';
                    }
                  });
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
    }
    </script>

    <style>
        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #dc3545;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        /* Alert styles */
        .alert {
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid;
            background-color: #f8f9fa;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .alert-success {
            border-left-color: #28a745;
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            border-left-color: #dc3545;
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-info {
            border-left-color: #17a2b8;
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .alert.show {
            animation: slideIn 0.3s ease-out;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-text {
            color: #999;
            font-size: 16px;
        }

        /* Modal detail styles */
        .detail-group {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-group:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        /* Button groups in modal footer */
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #1e7e34;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</body>
</html>
