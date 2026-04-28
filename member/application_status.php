<?php
// Member Dashboard - Check Application Status
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

$member_id = $_SESSION['member_id'];

// Get applications for this member
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$applications = [];

if ($member_id) {
    // Fetch Beti Vivah applications
    $stmt = $conn->prepare("SELECT *, 'beti_vivah' as app_type FROM beti_vivah_aavedan WHERE member_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    $stmt->close();
    
    // Fetch Death Aavedan applications
    $stmt = $conn->prepare("SELECT *, 'death_aavedan' as app_type FROM death_aavedan WHERE member_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $applications[] = $row;
    }
    $stmt->close();
    
    // Sort all applications by created_at DESC
    usort($applications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

$conn->close();?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>आवेदन की स्थिति - BRCT Bharat Trust</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
    <style>
        .application-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #007bff;
        }
        
        .application-card.pending {
            border-left-color: #ffc107;
        }
        
        .application-card.under-review {
            border-left-color: #17a2b8;
        }
        
        .application-card.approved {
            border-left-color: #28a745;
        }
        
        .application-card.rejected {
            border-left-color: #dc3545;
        }
        
        .application-card.on-hold {
            border-left-color: #6c757d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-under-review {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .timeline-item .timeline-marker {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
            position: relative;
            z-index: 2;
            border: 3px solid white;
        }
        
        .timeline-item.active .timeline-marker {
            background: #007bff;
            color: white;
        }
        
        .timeline-item.completed .timeline-marker {
            background: #28a745;
            color: white;
        }
        
        .timeline-item.completed.approved .timeline-marker {
            background: #28a745;
        }
        
        .timeline-item.completed.rejected .timeline-marker {
            background: #dc3545;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-content h6 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="member-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Navbar -->
            <?php include 'includes/navbar.php'; ?>
            
            <!-- Content Wrapper -->
            <div class="content-wrapper">
                <!-- Page Header -->
                <div class="mb-4">
                    <h2 class="mb-3">
                        <i class="fas fa-file-alt me-2 text-primary"></i>आवेदन की स्थिति
                    </h2>
                    <p class="text-muted">अपने बेटी विवाह सहायता और मृत्यु सहायता आवेदन की स्थिति यहाँ देखें।</p>
                </div>

                <?php if (count($applications) > 0): ?>
                    <?php foreach ($applications as $app): 
                        $statusClass = strtolower(str_replace(' ', '-', $app['status']));
                        
                        // Map status values based on app type
                        if ($app['app_type'] === 'death_aavedan') {
                            // Death aavedan uses different status values
                            $statusMap = [
                                'submitted' => 'Pending',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'on_hold' => 'On Hold'
                            ];
                            $statusDisplay = $statusMap[$app['status']] ?? ucfirst(str_replace('_', ' ', $app['status']));
                            $statusClass = strtolower(str_replace(' ', '-', $statusDisplay));
                        } else {
                            // Beti vivah uses direct status
                            $statusDisplay = $app['status'];
                        }
                        
                        $statusTrans = [
                            'Pending' => 'प्रतीक्षारत',
                            'Under Review' => 'समीक्षाधीन',
                            'Approved' => 'स्वीकृत',
                            'Rejected' => 'अस्वीकृत',
                            'On Hold' => 'प्रतीक्षा सूची में'
                        ];
                    ?>
                    <div class="application-card <?php echo $statusClass; ?>">
                        <div class="row align-items-start">
                            <div class="col-md-8">
                                <h5 class="fw-bold mb-3">
                                    <?php if ($app['app_type'] === 'beti_vivah'): ?>
                                        <i class="fas fa-heart text-danger me-2"></i>बेटी विवाह आवेदन #<?php echo htmlspecialchars($app['application_number']); ?>
                                    <?php else: ?>
                                        <i class="fas fa-heart-broken text-dark me-2"></i>मृत्यु सहयोग आवेदन #<?php echo htmlspecialchars($app['application_number']); ?>
                                    <?php endif; ?>
                                </h5>
                                
                                <?php if ($app['app_type'] === 'beti_vivah'): ?>
                                    <!-- Beti Vivah Details -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-secondary">बेटी का नाम</small>
                                            <div class="fw-600"><?php echo htmlspecialchars($app['bride_name']); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-secondary">वर का नाम</small>
                                            <div class="fw-600"><?php echo htmlspecialchars($app['groom_name']); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Death Aavedan Details -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-secondary">मृत व्यक्ति का नाम</small>
                                            <div class="fw-600"><?php echo htmlspecialchars($app['deceased_name']); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-secondary">आवेदक का नाम</small>
                                            <div class="fw-600"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-secondary">आवेदन तिथि</small>
                                        <div><?php echo date('d M Y', strtotime($app['created_at'])); ?></div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-secondary">अपडेट तिथि</small>
                                        <div><?php echo date('d M Y', strtotime($app['updated_at'])); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="status-badge status-<?php echo $statusClass; ?>">
                                    <i class="fas fa-circle-notch me-1"></i><?php echo $statusTrans[$statusDisplay] ?? $statusDisplay; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="timeline mt-4 pt-3 border-top">
                            <div class="timeline-item <?php echo ($statusDisplay === 'Pending' ? 'active' : 'completed'); ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>आवेदन सबमिट किया गया</h6>
                                    <small class="text-secondary"><?php echo date('d M Y, H:i', strtotime($app['created_at'])); ?></small>
                                </div>
                            </div>

                            <div class="timeline-item <?php echo in_array($statusDisplay, ['Under Review', 'On Hold', 'Approved', 'Rejected']) ? 'active' : ''; ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6>समीक्षा प्रक्रिया चल रही है</h6>
                                    <small class="text-secondary">आपके आवेदन की जांच की जा रही है।</small>
                                </div>
                            </div>

                            <div class="timeline-item <?php echo in_array($statusDisplay, ['Approved', 'Rejected']) ? 'completed ' . ($statusDisplay === 'Approved' ? 'approved' : 'rejected') : ''; ?>">
                                <div class="timeline-marker">
                                    <i class="fas fa-<?php echo $statusDisplay === 'Approved' ? 'check' : 'times'; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6><?php echo ($statusDisplay === 'Approved' ? 'आवेदन स्वीकृत' : ($statusDisplay === 'Rejected' ? 'आवेदन अस्वीकृत' : 'प्रतीक्षा सूची में')); ?></h6>
                                    <?php if ($statusDisplay === 'Rejected' && !empty($app['remarks'])): ?>
                                        <small class="text-secondary">कारण: <?php echo htmlspecialchars($app['remarks']); ?></small>
                                    <?php elseif ($statusDisplay === 'Approved'): ?>
                                        <small class="text-secondary">बधाई हो! आपका आवेदन स्वीकृत हो गया है।</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="mt-4 pt-3 border-top">
                            <details>
                                <summary class="fw-600 cursor-pointer">
                                    <i class="fas fa-chevron-down me-2"></i>पूरा विवरण देखें
                                </summary>
                                <div class="mt-3">
                                    <?php if ($app['app_type'] === 'beti_vivah'): ?>
                                        <!-- Beti Vivah Details -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">बैंक विवरण</h6>
                                                <small class="text-secondary">बैंक:</small> <div><?php echo htmlspecialchars($app['bank_name']); ?></div>
                                                <small class="text-secondary">शाखा:</small> <div><?php echo htmlspecialchars($app['branch_name']); ?></div>
                                                <small class="text-secondary">खाता:</small> <div>XXXX-XXXX-<?php echo substr($app['account_number'], -4); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">पारिवारिक विवरण</h6>
                                                <small class="text-secondary">वार्षिक आय:</small> <div>₹ <?php echo number_format($app['family_income'], 0, '.', ','); ?></div>
                                                <small class="text-secondary">परिवार के सदस्य:</small> <div><?php echo $app['family_members']; ?></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Death Aavedan Details -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">मृत व्यक्ति की जानकारी</h6>
                                                <small class="text-secondary">सदस्य ID:</small> <div><?php echo htmlspecialchars($app['deceased_member_id']); ?></div>
                                                <small class="text-secondary">जन्मतिथि:</small> <div><?php echo date('d M Y', strtotime($app['deceased_dob'])); ?></div>
                                                <small class="text-secondary">आयु:</small> <div><?php echo $app['deceased_age']; ?> वर्ष</div>
                                                <small class="text-secondary">मृत्यु तिथि:</small> <div><?php echo date('d M Y', strtotime($app['death_date'])); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">बैंक विवरण</h6>
                                                <small class="text-secondary">बैंक:</small> <div><?php echo htmlspecialchars($app['bank_name']); ?></div>
                                                <small class="text-secondary">शाखा:</small> <div><?php echo htmlspecialchars($app['branch_name']); ?></div>
                                                <small class="text-secondary">खाता:</small> <div>XXXX-XXXX-<?php echo substr($app['account_number'], -4); ?></div>
                                                <small class="text-secondary">खाताधारक:</small> <div><?php echo htmlspecialchars($app['account_holder_name']); ?></div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">पारिवारिक जानकारी</h6>
                                                <small class="text-secondary">परिवार की आय:</small> <div>₹ <?php echo number_format($app['family_income'], 0, '.', ','); ?></div>
                                                <small class="text-secondary">परिवार के सदस्य:</small> <div><?php echo $app['family_members']; ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="fw-600 text-primary">अन्य जानकारी</h6>
                                                <small class="text-secondary">मृत्यु का कारण:</small> <div><?php echo htmlspecialchars($app['cause_of_death']); ?></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </details>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-inbox"></i>
                                </div>
                                <h5 class="mb-2">कोई आवेदन नहीं</h5>
                                <p class="text-muted">आपने अभी तक कोई आवेदन सबमिट नहीं किया है।</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle function
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('active');
            }
        }

        // Load section function
        function loadSection(sectionName) {
            console.log('Loading section:', sectionName);
        }

        // Logout function
        function logoutMember() {
            if (confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
                window.location.href = '../includes/logout.php';
            }
        }

        // Sidebar Toggle Event Listener
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Close sidebar on outside click
        document.addEventListener('click', function(event) {
            if (!sidebar) return;
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = event.target.closest('.sidebar-toggle') || 
                                   event.target.closest('.navbar-toggle-mobile');
            
            if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
