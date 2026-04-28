<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>बेटी विवाह आवेदन सूची - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Member Directory CSS -->
    <link rel="stylesheet" href="../assets/css/members-directory.css">
    
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-under-review { background-color: #cfe2ff; color: #084298; }
        .status-approved { background-color: #d1e7dd; color: #0f5132; }
        .status-rejected { background-color: #f8d7da; color: #842029; }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .members-table thead {
            background-color: #2c3e50;
            color: white;
        }
        
        .members-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }
        
        .members-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .certificate-preview-box {
            width: 60px;
            height: 60px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
        }
        
        .certificate-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .certificate-img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>

    <!-- Page Header Section -->
    <section class="members-header">
        <div class="container">
            <div class="members-header-content">
                <h1>
                    <i class="fas fa-heart me-3"></i>बेटी विवाह आवेदन सूची
                </h1>
                <p>सभी बेटी विवाह आवेदनों की जानकारी देखें।</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Statistics Section -->
            <div class="stats-section" data-aos="fade-up">
                <div class="stat-card">
                    <i class="fas fa-list"></i>
                    <h3 id="totalApplications">0</h3>
                    <p>कुल आवेदन</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3 id="pendingApplications">0</h3>
                    <p>लंबित आवेदन</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3 id="approvedApplications">0</h3>
                    <p>स्वीकृत आवेदन</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <h3 id="rejectedApplications">0</h3>
                    <p>अस्वीकृत आवेदन</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <div class="filter-title">
                    <i class="fas fa-sliders-h me-2"></i>आवेदन खोजें
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="searchInput">
                            <i class="fas fa-search"></i> आवेदन संख्या या नाम
                        </label>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="आवेदन संख्या, सदस्य नाम या बेटी का नाम..."
                            class="form-control"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="districtFilter">
                            <i class="fas fa-map-marker-alt"></i> जिला
                        </label>
                        <select id="districtFilter" class="form-select">
                            <option value="">सभी जिले</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="blockFilter">
                            <i class="fas fa-cube"></i> ब्लॉक
                        </label>
                        <select id="blockFilter" class="form-select">
                            <option value="">सभी ब्लॉक</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="statusFilter">
                            <i class="fas fa-filter"></i> स्थिति
                        </label>
                        <select id="statusFilter" class="form-select">
                            <option value="">सभी स्थिति</option>
                            <option value="Pending">लंबित</option>
                            <option value="Under Review">समीक्षा में</option>
                            <option value="Approved">स्वीकृत</option>
                            <option value="Rejected">अस्वीकृत</option>
                        </select>
                    </div>
                </div>

                <div class="filter-buttons">
                    <button id="filterBtn" class="btn btn-primary">
                        <i class="fas fa-search"></i> खोजें
                    </button>
                    <button id="resetBtn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> रीसेट करें
                    </button>
                </div>
            </div>

            <!-- Applications Section -->
            <div class="members-section" data-aos="fade-up">
                <div class="section-title">
                    <i class="fas fa-list-ul me-2"></i>आवेदनों की सूची
                </div>

                <!-- Applications Table -->
                <div class="table-responsive" id="applicationsTableContainer">
                    <table class="members-table table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>S NO</th>
                                <th>Unique ID</th>
                                <th>नाम</th>
                                <th>बेटी का नाम</th>
                                <th>विवाह तिथि</th>
                                <th>स्थाई निवासी</th>
                                <th>जिला</th>
                                <th>ब्लॉक</th>
                                <th>विवाह कार्ड</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                                <th>क्रिया</th>
                            </tr>
                        </thead>
                        <tbody id="applicationsTableBody">
                            <tr>
                                <td colspan="11" class="loading-row text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
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

    <!-- Application Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>आवेदन विवरण</h2>
                <button type="button" class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Details will be loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-footer button btn-close" onclick="closeModal()">
                    <i class="fas fa-times me-2"></i>बंद करें
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- AOS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>

    <!-- Beti Vivah Applications Script -->
    <script src="../assets/js/beti_vivah_list.js"></script>

</body>
</html> 