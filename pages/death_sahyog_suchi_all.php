<?php
/**
 * Death Claims Sahyog Suchi - All Donations
 * Displays all Death Claims donations with filters
 */

require_once '../includes/config.php';

// Determine base path
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $root_path = '/Charitable/';
} else {
    $root_path = '/';
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>मृत्यु सहयोग सूची - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Death Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/death-sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Config -->
    <script src="../assets/js/config.js"></script>
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
                    <i class="fas fa-heart me-3"></i>मृत्यु सहयोग सूची
                </h1>
                <p>सभी मृत्यु सहायता दान की सूची और विवरण</p>
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
                    <p>कुल सहयोग</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-rupee-sign"></i>
                    <h3 id="totalAmount">₹ 0</h3>
                    <p>कुल संग्रहण</p>
                </div>
                <!-- <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3 id="verifiedCount">0</h3>
                    <p>सत्यापित दान</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3 id="pendingCount">0</h3>
                    <p>लंबित दान</p>
                </div> -->
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <div class="filter-title">
                    <i class="fas fa-sliders-h me-2"></i>फिल्टर
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="searchInput">
                            <i class="fas fa-search"></i> नाम या ID
                        </label>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="नाम, Unique ID या सदस्य ID..."
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
                        <select id="blockFilter" class="form-select" disabled>
                            <option value="">सभी ब्लॉक</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="entriesSelect">
                            <i class="fas fa-list"></i> प्रविष्टियाँ
                        </label>
                        <select id="entriesSelect" class="form-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="filter-buttons">
                    <button id="filterBtn" class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-search"></i> खोजें
                    </button>
                    <button id="resetBtn" class="btn btn-secondary" onclick="resetSearch()">
                        <i class="fas fa-redo"></i> रीसेट करें
                    </button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">लोड हो रहा है...</span>
                </div>
                <p class="mt-3 text-muted">कृपया प्रतीक्षा करें...</p>
            </div>

            <!-- Applications Section -->
            <div class="members-section" id="tableSection" style="display: none;" data-aos="fade-up">
                <div class="section-title">
                    <i class="fas fa-list-ul me-2"></i>दान सूची
                </div>

                <!-- Applications Table -->
                <div class="table-responsive" id="applicationsTableContainer">
                    <table class="table members-table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>क्र.नं</th>
                                <th>नाम</th>
                                <th>Unique ID</th>
                                <th>राशि</th>
                                <th>दान प्राप्तकर्ता</th>
                                <th>जिला</th>
                                <th>ब्लॉक</th>
                                <th>तिथि</th>
                            </tr>
                        </thead>
                        <tbody id="applicationsTableBody">
                            <tr>
                                <td colspan="8" class="loading-row text-center py-4">
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

            <!-- No Data Message -->
            <div id="noDataMessage" style="display: none;" data-aos="fade-up">
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-search" style="font-size: 2rem;"></i>
                    <p class="mt-3 mb-0">कोई डेटा नहीं मिला</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script for Death Claims -->
    <script src="../assets/js/death-sahyog-suchi.js"></script>
</body>
</html>
