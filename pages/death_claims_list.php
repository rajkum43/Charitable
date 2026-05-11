<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>मृत्यु दावा आवेदन सूची - BRCT Bharat Trust</title>
    
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
    <!-- Death Claims List CSS -->
    <link rel="stylesheet" href="../assets/css/death-claims-list.css">
    
    <style>
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
                    <i class="fas fa-file-alt me-3"></i>मृत्यु दावा आवेदन सूची
                </h1>
                <p>सभी मृत्यु दावा आवेदनों की जानकारी देखें।</p>
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
                    <i class="fas fa-hourglass-half"></i>
                    <h3 id="underReviewApplications">0</h3>
                    <p>समीक्षा में</p>
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
                            placeholder="आवेदन संख्या, सदस्य नाम, दिवंगत नाम..."
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
                            <option value="submitted">लंबित</option>
                            <option value="under_review">समीक्षा में</option>
                            <option value="approved">स्वीकृत</option>
                            <option value="rejected">अस्वीकृत</option>
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
                    <i class="fas fa-list-ul me-2"></i>मृत्यु दावा सूची
                </div>

                <!-- Applications Table -->
                <div class="table-responsive" id="applicationsTableContainer">
                    <table class="members-table table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>S NO</th>
                                <th>आवेदन संख्या</th>
                                <th>सदस्य नाम</th>
                                <th>दिवंगत नाम</th>
                                <th>मृत्यु तिथि</th>
                                <th>आवेदक नाम</th>
                                <th>जिला</th>
                                <th>ब्लॉक</th>
                                <th>दस्तावेज़</th>
                                <th>आवेदन तिथि</th>
                                <th>स्थिति</th>
                                <th>क्रिया</th>
                            </tr>
                        </thead>
                        <tbody id="applicationsTableBody">
                            <tr>
                                <td colspan="12" class="loading-row text-center py-4">
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

    <!-- Image Viewer Modal -->
    <div class="modal" id="imageViewerModal">
        <div class="modal-content" style="max-width: 90%;">
            <div class="modal-header">
                <h2>दस्तावेज़ देखें</h2>
                <button type="button" class="modal-close" id="imageViewerClose">&times;</button>
            </div>
            <div class="modal-body text-center">
                <img id="imageViewerImg" src="" alt="Document" style="max-width: 100%; max-height: 600px;">
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Death Claims List JS -->
    <script src="../assets/js/death-claims-list.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
