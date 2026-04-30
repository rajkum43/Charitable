<?php
/**
 * Alert Details - Death Sahyog Suchi
 * Displays all Death Sahyog donations for a specific alert/publish batch
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
    header('Location: alert-death-suchi.php');
    exit;
}

// Get all districts for filter
try {
    $districts_query = "SELECT DISTINCT m.district 
                        FROM poll p
                        INNER JOIN donation_transactions dt ON p.claim_number = dt.claim_number COLLATE utf8mb4_unicode_ci
                        LEFT JOIN members m ON dt.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
                        WHERE p.alert = ? 
                        AND p.application_type = 'Death_Claims'
                        AND dt.application_type = 'Death_Claims'
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
                    AND p.application_type = 'Death_Claims'
                    AND dt.application_type = 'Death_Claims'";
    
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
    <title>सूचना <?php echo htmlspecialchars($alert_number); ?> - मृत्यु सहयोग विवरण - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="../assets/css/sahyog-suchi.css">
    <link rel="stylesheet" href="../assets/css/ac-holder-betvivah-suchi.css">
    <link rel="stylesheet" href="../assets/css/alert-death-details.css">
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
                    <i class="fas fa-bell me-3"></i>सूचना <?php echo htmlspecialchars($alert_number); ?> - मृत्यु सहयोग विवरण
                </h1>
                <p>प्रकाशित सूची में सभी दान विवरण</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content py-5">
        <div class="container">
            
            <!-- Back Button -->
            <div class="mb-4">
                <a href="alert-death-suchi.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>वापस जाएं
                </a>
            </div>

            <!-- Total Collection -->
            <div class="total-collection" data-aos="fade-up">
                Total Collection: Rs. <span id="totalCollection"><?php echo number_format($total_collection, 2); ?></span>
            </div>

            <!-- Filter Section -->
            <div class="filter-section" data-aos="fade-up">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="districtFilter">
                            <i class="fas fa-map-marker-alt"></i> जिला
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
                            <i class="fas fa-cube"></i> ब्लॉक
                        </label>
                        <select id="blockFilter" class="form-select" disabled>
                            <option value="">सभी ब्लॉक</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-pagination-section" data-aos="fade-up">
                <div class="search-box">
                    <label for="searchInput">
                        <i class="fas fa-search"></i> खोजें:
                    </label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="नाम या ID से खोजें..."
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
                                <th>क्र.सं.</th>
                                <th>नाम</th>
                                <th>ID</th>
                                <th>दान राशि</th>
                                <th>लाभार्थी</th>
                                <th>जिला</th>
                                <th>ब्लॉक</th>
                                <th>तारीख</th>
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
    
    <!-- Alert Death Details JS -->
    <script src="../assets/js/alert-death-details.js"></script>
</body>
</html>
