<?php
/**
 * Account Holder Beti Vivah Sahyog Suchi
 * Displays all approved Beti Vivah applications with account holder details
 */

require_once '../includes/config.php';

// Determine base path
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $root_path = '/Charitable/';
} else {
    $root_path = '/';
}

// Get all districts from members table where they have approved applications
$districts_query = "SELECT DISTINCT m.district 
                    FROM members m
                    INNER JOIN beti_vivah_aavedan b ON m.member_id = b.member_id
                    WHERE b.status = 'Approved' 
                    AND m.district IS NOT NULL 
                    AND m.district != 'Unknown' 
                    ORDER BY m.district";
$districts_stmt = $pdo->prepare($districts_query);
$districts_stmt->execute();
$districts = $districts_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>खाता धारक बेटी विवाह सहयोग सूची - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Config -->
    <script src="../assets/js/config.js"></script>
    
    <style>
        .total-collection {
            font-size: 1.5rem;
            font-weight: bold;
            color: green;
            margin-bottom: 1rem;
        }
        
        .search-pagination-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .entries-select {
            min-width: 100px;
        }
        
        @media (max-width: 768px) {
            .search-pagination-section {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
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
                    <i class="fas fa-university me-3"></i>खाता धारक बेटी विवाह सहयोग सूची
                </h1>
                <p>सभी मंजूरीकृत बेटी विवाह सहायता के खाताधारक विवरण</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
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
                                <th>Amount</th>
                                <th>District</th>
                                <th>Block</th>
                                <th>Vivah Sahyog Date</th>
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
    
    <script>
        const BASE_PATH = '<?php echo $root_path; ?>';
        let allData = [];
        let currentPage = 1;
        let entriesPerPage = 10;
        let filteredData = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadApplications();
            
            // Event listeners
            document.getElementById('districtFilter').addEventListener('change', loadBlocksByDistrict);
            document.getElementById('blockFilter').addEventListener('change', applyFilters);
            document.getElementById('entriesSelect').addEventListener('change', function() {
                entriesPerPage = parseInt(this.value);
                currentPage = 1;
                displayTable();
            });
            document.getElementById('searchInput').addEventListener('keyup', applyFilters);
        });

        function loadApplications() {
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tableSection').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';

            fetch(`${BASE_PATH}api/get_ac_holder_betvivah_applications.php`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allData = data.applications || [];
                        filteredData = allData;
                        updateTotalCollection();
                        displayTable();
                    } else {
                        console.error('Error:', data.error);
                    }
                    document.getElementById('loadingSpinner').style.display = 'none';
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    document.getElementById('loadingSpinner').style.display = 'none';
                });
        }

        function loadBlocksByDistrict() {
            const district = document.getElementById('districtFilter').value;
            const blockFilter = document.getElementById('blockFilter');
            
            blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
            blockFilter.disabled = true;

            if (district) {
                const blocks = [...new Set(allData
                    .filter(app => app.district === district)
                    .map(app => app.block)
                    .filter(block => block && block !== 'Unknown'))];
                
                blocks.sort().forEach(block => {
                    const option = document.createElement('option');
                    option.value = block;
                    option.textContent = block;
                    blockFilter.appendChild(option);
                });
                
                blockFilter.disabled = blocks.length === 0;
            }

            currentPage = 1;
            applyFilters();
        }

        function applyFilters() {
            const district = document.getElementById('districtFilter').value;
            const block = document.getElementById('blockFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();

            filteredData = allData.filter(app => {
                const matchDistrict = !district || app.district === district;
                const matchBlock = !block || app.block === block;
                const matchSearch = !search || 
                    app.account_holder_name.toLowerCase().includes(search) ||
                    app.member_name.toLowerCase().includes(search);

                return matchDistrict && matchBlock && matchSearch;
            });

            currentPage = 1;
            displayTable();
        }

        function displayTable() {
            const tbody = document.getElementById('applicationsTableBody');
            const tableSection = document.getElementById('tableSection');
            const noDataMessage = document.getElementById('noDataMessage');

            if (filteredData.length === 0) {
                tableSection.style.display = 'none';
                noDataMessage.style.display = 'block';
                return;
            }

            tableSection.style.display = 'block';
            noDataMessage.style.display = 'none';

            // Calculate pagination
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);
            const startIdx = (currentPage - 1) * entriesPerPage;
            const endIdx = startIdx + entriesPerPage;
            const pageData = filteredData.slice(startIdx, endIdx);

            // Populate table
            tbody.innerHTML = pageData.map((app, idx) => {
                const serialNo = startIdx + idx + 1;
                const weddingDate = new Date(app.wedding_date).toLocaleDateString('hi-IN');
                
                return `
                    <tr>
                        <td>${serialNo}</td>
                        <td>${escapeHtml(app.account_holder_name)}</td>
                        <td>50</td>
                        <td>${escapeHtml(app.district)}</td>
                        <td>${escapeHtml(app.block)}</td>
                        <td>${weddingDate}</td>
                        <td>
                            <a href="applicante-betivivah-sahyog-suchi.php?member_id=${app.member_id}" class="btn btn-sm btn-primary">
                                View Details
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');

            // Update pagination
            updatePagination(totalPages);
        }

        function updatePagination(totalPages) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous button
            if (currentPage > 1) {
                const prevBtn = document.createElement('a');
                prevBtn.href = '#';
                prevBtn.className = 'page-link';
                prevBtn.textContent = 'Previous';
                prevBtn.onclick = (e) => {
                    e.preventDefault();
                    currentPage--;
                    displayTable();
                    window.scrollTo(0, 0);
                };
                pagination.appendChild(prevBtn);
            }

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    const activePage = document.createElement('span');
                    activePage.className = 'page-link active';
                    activePage.textContent = i;
                    pagination.appendChild(activePage);
                } else {
                    const pageBtn = document.createElement('a');
                    pageBtn.href = '#';
                    pageBtn.className = 'page-link';
                    pageBtn.textContent = i;
                    pageBtn.onclick = (e) => {
                        e.preventDefault();
                        currentPage = i;
                        displayTable();
                        window.scrollTo(0, 0);
                    };
                    pagination.appendChild(pageBtn);
                }
            }

            // Next button
            if (currentPage < totalPages) {
                const nextBtn = document.createElement('a');
                nextBtn.href = '#';
                nextBtn.className = 'page-link';
                nextBtn.textContent = 'Next';
                nextBtn.onclick = (e) => {
                    e.preventDefault();
                    currentPage++;
                    displayTable();
                    window.scrollTo(0, 0);
                };
                pagination.appendChild(nextBtn);
            }
        }

        function updateTotalCollection() {
            const total = filteredData.length * 50; // 50 per record
            document.getElementById('totalCollection').textContent = total.toLocaleString('en-IN');
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
