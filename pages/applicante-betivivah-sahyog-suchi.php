<?php
/**
 * Beti Vivah Sahyog Donations List
 * Displays all donations received by an account holder for Beti Vivah
 */

require_once '../includes/config.php';

// Check if member_id is provided
if (!isset($_GET['member_id'])) {
    header('Location: ac-holder-betvivah-suchi.php');
    exit;
}

$member_id = htmlspecialchars($_GET['member_id']);

// Fetch member details
$member_query = "SELECT member_id, member_name FROM beti_vivah_aavedan WHERE member_id = ? AND status = 'Approved' LIMIT 1";
$member_stmt = $pdo->prepare($member_query);
$member_stmt->execute([$member_id]);
$member_details = $member_stmt->fetch(PDO::FETCH_ASSOC);

if (!$member_details) {
    header('Location: ac-holder-betvivah-suchi.php');
    exit;
}

$application = $member_details;

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
    <title>बेटी विवाह सहयोग विवरण - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Sahyog Suchi CSS -->
    <link rel="stylesheet" href="../assets/css/sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .detail-section {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .detail-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .detail-row {
            margin-bottom: 20px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
            font-size: 1.05rem;
            padding-left: 15px;
            border-left: 3px solid var(--primary-color);
        }
        
        .back-button {
            margin-bottom: 20px;
        }
        
        .amount-highlight {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            text-align: center;
            padding: 20px;
            background: var(--primary-light);
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .document-link {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .document-link a {
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .document-link a:hover {
            background-color: var(--primary-dark);
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
                    <i class="fas fa-gift me-3"></i>बेटी विवाह सहयोग दान
                </h1>
                <p><?php echo htmlspecialchars($application['member_name']); ?> को प्राप्त दान</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Back Button -->
            <div class="back-button">
                <a href="ac-holder-betvivah-suchi.php" class="btn btn-secondary">
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
                        placeholder="नाम या ID खोजें..."
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

            <!-- Donations Section -->
            <div class="members-section" id="tableSection" style="display: none;" data-aos="fade-up">
                <!-- Donations Table -->
                <div class="table-responsive" id="donationsTableContainer">
                    <table class="table members-table" id="donationsTable">
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
                        <tbody id="donationsTableBody">
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
    
    <script>
        const BASE_PATH = '<?php echo $root_path; ?>';
        const MEMBER_ID = '<?php echo $member_id; ?>';
        let allData = [];
        let currentPage = 1;
        let entriesPerPage = 10;
        let filteredData = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDonations();
            
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

        function loadDonations() {
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('tableSection').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';

            fetch(`${BASE_PATH}api/get_betivivah_donations.php?member_id=${MEMBER_ID}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allData = data.donations || [];
                        filteredData = allData;
                        populateDistrictFilter();
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

        function populateDistrictFilter() {
            const districts = [...new Set(allData
                .map(donation => donation.donor_district)
                .filter(d => d && d !== 'Unknown'))];
            
            const districtFilter = document.getElementById('districtFilter');
            districts.sort().forEach(district => {
                const option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                districtFilter.appendChild(option);
            });
        }

        function loadBlocksByDistrict() {
            const district = document.getElementById('districtFilter').value;
            const blockFilter = document.getElementById('blockFilter');
            
            blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
            blockFilter.disabled = true;

            if (district) {
                const blocks = [...new Set(allData
                    .filter(donation => donation.donor_district === district)
                    .map(donation => donation.donor_block)
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

            filteredData = allData.filter(donation => {
                const matchDistrict = !district || donation.donor_district === district;
                const matchBlock = !block || donation.donor_block === block;
                const matchSearch = !search || 
                    donation.donor_name.toLowerCase().includes(search) ||
                    donation.donor_member_id.toLowerCase().includes(search);

                return matchDistrict && matchBlock && matchSearch;
            });

            currentPage = 1;
            displayTable();
        }

        function displayTable() {
            const tbody = document.getElementById('donationsTableBody');
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
            tbody.innerHTML = pageData.map((donation, idx) => {
                const serialNo = startIdx + idx + 1;
                const donationDate = new Date(donation.created_at).toLocaleDateString('hi-IN');
                
                return `
                    <tr>
                        <td>${serialNo}</td>
                        <td>${escapeHtml(donation.donor_name)}</td>
                        <td>${escapeHtml(donation.donor_member_id)}</td>
                        <td>${donation.amount}</td>
                        <td>${escapeHtml(donation.recipient_name)}</td>
                        <td>${escapeHtml(donation.donor_district)}</td>
                        <td>${escapeHtml(donation.donor_block)}</td>
                        <td>${donationDate}</td>
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
            const total = allData.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
            document.getElementById('totalCollection').textContent = total.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
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
