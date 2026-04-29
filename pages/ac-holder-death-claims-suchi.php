<?php
/**
 * Death Claims Account Holder List Page
 * Displays all approved death claims with filtering and pagination
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
    <title>मृत्यु सहायता सूची - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/ac-holder-death-claims-suchi.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Config -->
    <script src="../assets/js/config.js"></script>
</head>
<body data-base-path="<?php echo $root_path; ?>">

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>

    <!-- Page Header Section -->
    <section class="members-header">
        <div class="container">
            <div class="members-header-content">
                <h1>
                    <i class="fas fa-heartbeat me-3"></i>मृत्यु सहायता खाता धारक सूची
                </h1>
                <p>अनुमोदित दावों की सूची</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Back Button -->
            <div class="back-button">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> वापस जाएं
                </a>
            </div>

            <!-- Total Collection -->
            <div class="total-collection" data-aos="fade-up">
                Total Collection: Rs. <span id="totalCollection">0</span>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="districtFilter">
                            <i class="fas fa-map-marker-alt"></i> Select District
                        </label>
                        <select id="districtFilter" class="form-select">
                            <option value="">सभी जिले</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="blockFilter">
                            <i class="fas fa-cube"></i> Select Block
                        </label>
                        <select id="blockFilter" class="form-select" disabled>
                            <option value="">सभी ब्लॉक</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="entriesSelect">
                            <i class="fas fa-list"></i> Show
                        </label>
                        <select id="entriesSelect" class="form-select entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <small class="text-muted">entries</small>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-pagination-section" data-aos="fade-up">
                <div class="search-box">
                    <label for="searchInput">
                        <i class="fas fa-search"></i> Search:
                    </label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="नाम खोजें..."
                        class="form-control"
                    >
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
                <!-- Applications Table -->
                <div class="table-responsive" id="applicationsTableContainer">
                    <table class="table members-table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th>S NO</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>District</th>
                                <th>Block</th>
                                <th>Death Date</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="applicationsTableBody">
                            <tr>
                                <td colspan="7" class="loading-row text-center py-4">
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
    <!-- Death Claims AC Holder JS -->
    <script src="../assets/js/ac-holder-death-claims-suchi.js"></script>
</body>
</html>
