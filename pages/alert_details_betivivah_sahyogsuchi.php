<?php
/**
 * Alert Details - Beti Vivah Sahyog Suchi
 * Displays all Beti Vivah donations for a specific alert/publish batch
 */

require_once '../includes/config.php';

// Determine base path
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $root_path = '/Charitable/';
} else {
    $root_path = '/';
}

// Get alert number from URL
$alert_number = isset($_GET['alert']) ? intval($_GET['alert']) : 0;

if ($alert_number <= 0) {
    header('Location: alert-betvivah-suchi.php');
    exit;
}

// Get all districts for filter
try {
    $districts_query = "SELECT DISTINCT m.district 
                        FROM poll p
                        INNER JOIN donation_transactions dt ON p.claim_number = dt.claim_number COLLATE utf8mb4_unicode_ci
                        LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
                        WHERE p.alert = ? 
                        AND p.application_type = 'Beti_Vivah'
                        AND dt.application_type = 'Beti_Vivah'
                        AND m.district IS NOT NULL 
                        AND m.district != 'Unknown' 
                        ORDER BY m.district";
    
    $districts_stmt = $pdo->prepare($districts_query);
    $districts_stmt->execute([$alert_number]);
    $districts = $districts_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log('Error fetching districts: ' . $e->getMessage());
    $districts = [];
}

// Get total collection for this alert
try {
    $total_query = "SELECT COALESCE(SUM(dt.amount), 0) as total_amount
                    FROM poll p
                    INNER JOIN donation_transactions dt ON p.claim_number = dt.claim_number COLLATE utf8mb4_unicode_ci
                    WHERE p.alert = ? 
                    AND p.application_type = 'Beti_Vivah'
                    AND dt.application_type = 'Beti_Vivah'";
    
    $total_stmt = $pdo->prepare($total_query);
    $total_stmt->execute([$alert_number]);
    $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
    $total_collection = floatval($total_result['total_amount'] ?? 0);
    
} catch (Exception $e) {
    error_log('Error calculating total: ' . $e->getMessage());
    $total_collection = 0;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सतर्कता <?php echo htmlspecialchars($alert_number); ?> - बेटी विवाह सहयोग विवरण - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/ac-holder-betvivah-suchi.css">
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
                    <i class="fas fa-bell me-3"></i>सतर्कता <?php echo htmlspecialchars($alert_number); ?> - बेटी विवाह सहयोग विवरण
                </h1>
                <p>प्रकाशित सूची में सभी दान विवरण</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Total Collection -->
            <div class="total-collection" data-aos="fade-up">
                Total Collection: Rs. <span id="totalCollection"><?php echo number_format($total_collection, 2); ?></span>
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
                            <?php foreach ($districts as $district): ?>
                                <option value="<?php echo htmlspecialchars($district); ?>">
                                    <?php echo htmlspecialchars($district); ?>
                                </option>
                            <?php endforeach; ?>
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
                                <th>Unique ID</th>
                                <th>Amount</th>
                                <th>Donation To Member</th>
                                <th>District</th>
                                <th>Block</th>
                                <th>Sahyog Date</th>
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
    
    <!-- Data Table JS -->
    <script>
        const BASE_PATH = document.body.getAttribute('data-base-path');
        const ALERT_NUMBER = <?php echo htmlspecialchars($alert_number); ?>;
        let allData = [];
        let currentPage = 1;
        let itemsPerPage = 25;
        let filteredData = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadAlertData();
            setupEventListeners();
        });

        function loadAlertData() {
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tableSection').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';

            // Fetch data via AJAX
            fetch(`${BASE_PATH}api/get_alert_betivivah_donations.php?alert=${ALERT_NUMBER}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.donations.length > 0) {
                        allData = data.donations;
                        filteredData = [...allData];
                        document.getElementById('loadingSpinner').style.display = 'none';
                        document.getElementById('tableSection').style.display = 'block';
                        displayTable();
                    } else {
                        document.getElementById('loadingSpinner').style.display = 'none';
                        document.getElementById('noDataMessage').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loadingSpinner').style.display = 'none';
                    document.getElementById('noDataMessage').style.display = 'block';
                });
        }

        function setupEventListeners() {
            // District filter
            document.getElementById('districtFilter').addEventListener('change', function() {
                const selectedDistrict = this.value;
                const blockFilter = document.getElementById('blockFilter');
                
                if (selectedDistrict) {
                    const blocks = [...new Set(allData
                        .filter(item => item.donor_district === selectedDistrict)
                        .map(item => item.donor_block))];
                    
                    blockFilter.disabled = false;
                    blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
                    blocks.forEach(block => {
                        blockFilter.innerHTML += `<option value="${block}">${block}</option>`;
                    });
                } else {
                    blockFilter.disabled = true;
                    blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
                }
                
                applyFilters();
            });

            // Block filter
            document.getElementById('blockFilter').addEventListener('change', applyFilters);

            // Entries select
            document.getElementById('entriesSelect').addEventListener('change', function() {
                itemsPerPage = parseInt(this.value);
                currentPage = 1;
                displayTable();
            });

            // Search
            document.getElementById('searchInput').addEventListener('keyup', applyFilters);
        }

        function applyFilters() {
            const selectedDistrict = document.getElementById('districtFilter').value;
            const selectedBlock = document.getElementById('blockFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            filteredData = allData.filter(item => {
                const districtMatch = !selectedDistrict || item.donor_district === selectedDistrict;
                const blockMatch = !selectedBlock || item.donor_block === selectedBlock;
                const searchMatch = !searchTerm || 
                    item.donor_name.toLowerCase().includes(searchTerm) ||
                    item.donor_member_id.toLowerCase().includes(searchTerm) ||
                    item.recipient_name.toLowerCase().includes(searchTerm);

                return districtMatch && blockMatch && searchMatch;
            });

            currentPage = 1;
            displayTable();
        }

        function displayTable() {
            const tableBody = document.getElementById('applicationsTableBody');
            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = startIdx + itemsPerPage;
            const pageData = filteredData.slice(startIdx, endIdx);

            if (pageData.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">कोई डेटा नहीं मिला</td></tr>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            tableBody.innerHTML = pageData.map((item, idx) => `
                <tr>
                    <td>${startIdx + idx + 1}</td>
                    <td>${item.donor_name}</td>
                    <td>${item.donor_member_id}</td>
                    <td>₹${parseFloat(item.amount).toFixed(2)}</td>
                    <td>${item.recipient_name}</td>
                    <td>${item.donor_district}</td>
                    <td>${item.donor_block}</td>
                    <td>${new Date(item.created_at).toLocaleDateString('hi-IN')}</td>
                </tr>
            `).join('');

            displayPagination();
        }

        function displayPagination() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const paginationDiv = document.getElementById('pagination');

            if (totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }

            let html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

            // Previous button
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            </li>`;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
                    </li>`;
                } else if (i === 2 || i === totalPages - 1) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            // Next button
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            </li></ul></nav>`;

            paginationDiv.innerHTML = html;
        }

        function goToPage(pageNum) {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (pageNum >= 1 && pageNum <= totalPages) {
                currentPage = pageNum;
                displayTable();
                window.scrollTo(0, 0);
            }
        }
    </script>
</body>
</html>
