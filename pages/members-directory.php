<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सदस्य निर्देशिका - BRCT Bharat Trust</title>
    
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
                    <i class="fas fa-users me-3"></i>सदस्य निर्देशिका
                </h1>
                <p>BRCT Bharat Trust के साथ जुड़े हुए सभी अनुमोदित सदस्यों की जानकारी प्राप्त करें।</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Statistics Section -->
            <div class="stats-section" data-aos="fade-up">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3 id="totalMembers">0</h3>
                    <p>कुल सदस्य</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3 id="verifiedMembers">0</h3>
                    <p>सत्यापित सदस्य</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3 id="districtCount">-</h3>
                    <p>जिले</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-cube"></i>
                    <h3 id="blockCount">-</h3>
                    <p>ब्लॉक</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <div class="filter-title">
                    <i class="fas fa-sliders-h me-2"></i>सदस्य खोजें
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="searchInput">
                            <i class="fas fa-search"></i> सदस्य ID या नाम
                        </label>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="सदस्य ID या नाम दर्ज करें..."
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

            <!-- Members Section -->
            <div class="members-section" data-aos="fade-up">
                <div class="section-title">
                    <i class="fas fa-list-ul me-2"></i>सदस्यों की सूची
                </div>

                <!-- Members Table -->
                <div class="table-responsive" id="membersTableContainer">
                    <table class="members-table table" id="membersTable">
                        <thead>
                            <tr>
                                <th>SN.</th>
                                <th>Unique ID</th>
                                <th>नाम</th>
                                <th>स्थाई निवासी जिला</th>
                                <th>ब्लॉक</th>
                                <th>Poll Option</th>
                                <th>Status</th>
                                <th>Submission Date</th>
                                <th>क्रिया</th>
                            </tr>
                        </thead>
                        <tbody id="membersTableBody">
                            <tr>
                                <td colspan="8" class="loading-row">
                                    <div class="spinner"></div>
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

    <!-- Member Directory Script -->
    <script src="../assets/js/members-directory.js"></script>

</body>
</html>
