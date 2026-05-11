<?php
// Member Dashboard Page
session_start();

// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../includes/config.php';
?>
<!DOCTYPE html> 
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Member Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/member.css">
    <!-- Member Donation CSS -->
    <link rel="stylesheet" href="assets/css/member_donation.css">
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
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Member Dashboard Section -->
                <div id="dashboard-section" class="content-box active">
                    <div class="mb-4">
                        <h2 class="mb-3">स्वागत है! 👋</h2>
                        <p class="text-muted">आपके खाते का संक्षिप्त विवरण नीचे दिया गया है।</p>
                    </div>

                    <!-- Stats Grid -->
                    <div class="dashboard-grid">
                        <div class="stat-card primary">
                            <div class="stat-icon">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <div class="stat-value" id="memberId">-</div>
                            <div class="stat-label">सदस्य ID</div>
                        </div>

                        <div class="stat-card success">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value" id="memberStatus">-</div>
                            <div class="stat-label">सक्रिय सदस्यता स्थिति</div>
                        </div>

                        <div class="stat-card info">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-value" id="joinDate">-</div>
                            <div class="stat-label">शामिल होने की तारीख</div>
                        </div>

                        <div class="stat-card secondary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value" id="referralCount">0</div>
                            <div class="stat-label">रेफरल संख्या</div>
                        </div>

                        <div class="stat-card danger">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-value" id="renewalDate">-</div>
                            <div class="stat-label">नवीनीकरण की तारीख</div>
                        </div>
                    </div>

                    <!-- Active Donations Section -->
                    <div class="mt-4">
                        <h3 class="mb-3"><i class="fas fa-hand-holding-heart me-2"></i>सक्रिय दान अवसर</h3>
                        <p class="text-muted">आपके पोल विकल्प के अनुसार सक्रिय दान अनुरोध।</p>
                        
                        <div id="activeDonationsContainer" class="donation-cards-section">
                            <!-- Donation cards will be loaded here -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">लोड हो रहा है...</span>
                                </div>
                                <p class="mt-2 text-muted">दान डेटा लोड हो रहा है...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Member Info Card -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>आपकी जानकारी</h5>
                            <a href="referrals.php" class="btn btn-light btn-sm">
                                <i class="fas fa-users me-1"></i>रेफरल देखें
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>नाम:</strong> <span id="memberName">-</span></p>
                                    <p><strong>सदस्य ID:</strong> <span id="memberID2">-</span></p>
                                    <p><strong>लिंग:</strong> <span id="infoGender">-</span></p>
                                    <p><strong>मोबाइल:</strong> <span id="infoMobile">-</span></p>
                                    <p><strong>जिला:</strong> <span id="infoDistrict">-</span></p>
                                    <p><strong>ब्लॉक:</strong> <span id="infoBlock">-</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>पिता/पति का नाम:</strong> <span id="infoFather">-</span></p>
                                    <p><strong>नामांकित व्यक्ति:</strong> <span id="infoNominee">-</span></p>
                                    <p><strong>वर्तमान पोल:</strong> <span id="infoPoll">-</span></p>
                                    <p><strong>शामिल होने की तारीख:</strong> <span id="infoJoinDate">-</span></p>
                                    <p><strong>स्थायी पता:</strong> <span id="infoAddress">-</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div id="profile-section" class="content-box">
                    <h2 class="mb-4">प्रोफाइल संपादित करें</h2>

                    <div class="profile-card card profile-section p-4">
                        <div class="profile-top d-flex align-items-center justify-content-between flex-column flex-lg-row gap-4">
                            <div class="profile-image">
                                <div class="profile-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>

                            <div class="profile-summary flex-grow-1">
                                <p class="section-badge mb-2">सदस्य प्रबंधन</p>
                                <h3 class="profile-name mb-2" id="profileFullName">-</h3>
                                <p class="profile-subtitle text-muted mb-3">
                                    अपनी प्रोफाइल जानकारी सुरक्षित और तेज़ तरीके से अपडेट करें। नीचे दिए गए फ़ील्ड्स पर क्लिक करके संपादन शुरू करें।
                                </p>
                                <div class="profile-actions d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-user-edit me-1"></i> सम्पूर्ण प्रोफ़ाइल देखें
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary shadow-sm">
                                        <i class="fas fa-sync-alt me-1"></i> डेटा रिफ्रेश करें
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="profile-info-grid mt-4">
                            <!-- Email -->
                            <div class="info-group">
                                <label class="info-label">ईमेल</label>
                                <div>
                                    <div class="info-value" id="profileEmail">-</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 btn-edit-profile" data-field="profileEmail">
                                        <i class="fas fa-edit"></i> संपादित करें
                                    </button>
                                </div>
                            </div>

                            <!-- Mobile -->
                            <div class="info-group">
                                <label class="info-label">मोबाइल</label>
                                <div>
                                    <div class="info-value" id="profileMobile">-</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 btn-edit-profile" data-field="profileMobile">
                                        <i class="fas fa-edit"></i> संपादित करें
                                    </button>
                                </div>
                            </div>

                            <!-- Gender -->
                            <div class="info-group">
                                <label class="info-label">लिंग</label>
                                <div class="info-value" id="profileGender">-</div>
                            </div>

                            <!-- DOB -->
                            <div class="info-group">
                                <label class="info-label">जन्म तारीख</label>
                                <div class="info-value" id="profileDOB">-</div>
                            </div>

                            <!-- Occupation -->
                            <div class="info-group">
                                <label class="info-label">व्यवसाय</label>
                                <div class="info-value" id="profileOccupation">-</div>
                            </div>

                            <!-- Office Name -->
                            <div class="info-group">
                                <label class="info-label">कार्यालय</label>
                                <div>
                                    <div class="info-value" id="profileOffice">-</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 btn-edit-profile" data-field="profileOffice">
                                        <i class="fas fa-edit"></i> संपादित करें
                                    </button>
                                </div>
                            </div>

                            <!-- Office Address -->
                            <div class="info-group">
                                <label class="info-label">कार्यालय पता</label>
                                <div>
                                    <div class="info-value" id="profileOfficeAddress">-</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 btn-edit-profile" data-field="profileOfficeAddress">
                                        <i class="fas fa-edit"></i> संपादित करें
                                    </button>
                                </div>
                            </div>

                            <!-- Permanent Address -->
                            <div class="info-group">
                                <label class="info-label">स्थायी पता</label>
                                <div>
                                    <div class="info-value" id="profilePermanentAddress">-</div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 btn-edit-profile" data-field="profilePermanentAddress">
                                        <i class="fas fa-edit"></i> संपादित करें
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Section -->
                <div id="membership-section" class="content-box">
                    <h2 class="mb-4">सदस्यता विवरण</h2>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <td class="fw-bold">आधार नंबर</td>
                                    <td id="membershipAadhar">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">पिता/पति का नाम</td>
                                    <td id="membershipFather">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">राज्य</td>
                                    <td id="membershipState">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">जिला</td>
                                    <td id="membershipDistrict">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">ब्लॉक</td>
                                    <td id="membershipBlock">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">UTR नंबर</td>
                                    <td id="membershipUTR">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">भुगतान स्थिति</td>
                                    <td><span id="membershipPaymentStatus" class="badge">-</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">शामिल होने की तारीख</td>
                                    <td id="membershipJoinDate">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">वर्तमान पोल</td>
                                    <td id="membershipPoll">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">नामांकित व्यक्ति का नाम</td>
                                    <td id="membershipNominee">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">लिंग</td>
                                    <td id="membershipGender">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">मोबाइल नंबर</td>
                                    <td id="membershipMobile">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">स्थायी पता</td>
                                    <td id="membershipAddress">-</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">यूनिक ID</td>
                                    <td id="membershipUniqueId">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Section -->
                <div id="payment-section" class="content-box">
                    <h2 class="mb-4">भुगतान विवरण</h2>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        भुगतान संबंधित जानकारी यहां प्रदर्शित की जाएगी।
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">भुगतान इतिहास</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>तारीख</th>
                                        <th>राशि</th>
                                        <th>स्थिति</th>
                                        <th>विवरण</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">कोई भुगतान रिकॉर्ड नहीं</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div id="documents-section" class="content-box">
                    <h2 class="mb-4">दस्तावेज़</h2>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        आपके दस्तावेज़ यहां प्रदर्शित होंगे।
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">अपलोड किए गए दस्तावेज़</h5>
                            <div class="list-group">
                                <div class="list-group-item text-center text-muted py-5">
                                    <i class="fas fa-folder-open fa-2x mb-3"></i>
                                    <p>कोई दस्तावेज अपलोड नहीं किए गए हैं</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Section -->
                <div id="settings-section" class="content-box">
                    <h2 class="mb-4">सेटिंग्स</h2>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">खाता सेटिंग्स</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">पासवर्ड बदलें</label>
                                    <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                        <i class="fas fa-key me-2"></i>पासवर्ड बदलें
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">दो-फैक्टर प्रमाणीकरण</label>
                                    <button class="btn btn-outline-primary w-100" disabled>
                                        <i class="fas fa-shield-alt me-2"></i>जल्द आ रहा है
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0 text-danger">खतरे वाला जोन</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-danger" onclick="logoutMember()">
                                <i class="fas fa-sign-out-alt me-2"></i>लॉगआउट
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Config JS -->
    <script src="../assets/js/config.js"></script>
    <!-- Member Dashboard JS -->
    <script src="assets/js/member.js"></script>

    <!-- Renewal Modal -->
    <div class="modal fade" id="renewalModal" tabindex="-1" aria-labelledby="renewalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="renewalModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>सदस्यता नवीनीकरण आवश्यक
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-calendar-times text-warning" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">आपकी सदस्यता समाप्त हो गई है</h4>
                    <p class="text-muted">कृपया अपनी सदस्यता को नवीनीकृत करें ताकि आप सभी सेवाओं का लाभ उठा सकें।</p>
                    <div class="alert alert-info">
                        <strong>नवीनीकरण शुल्क:</strong> ₹500/- (प्रति वर्ष)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">बाद में</button>
                    <a href="renew.php" class="btn btn-primary">
                        <i class="fas fa-refresh me-2"></i>अभी नवीनीकृत करें
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Check renewal status on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetch('../api/check_renewal.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.needs_renewal) {
                    const modal = new bootstrap.Modal(document.getElementById('renewalModal'));
                    modal.show();
                }
            })
            .catch(error => console.error('Error checking renewal:', error));
    });
    </script>
</body>
</html>
