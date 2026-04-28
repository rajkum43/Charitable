<?php
// Admin Edit Member Page
session_start();
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सदस्य विवरण संपादित करें - BRCT Bharat Trust Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Admin Common CSS -->
    <link rel="stylesheet" href="css/admin-common.css">
    <!-- Member Edit CSS -->
    <link rel="stylesheet" href="css/edit-member.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <div class="main-content">
            <!-- Page Content -->
            <div class="admin-content-wrapper">
                <div class="container-fluid">
                    <!-- Page Title -->
                    <div class="page-header mb-4">
                        <h2><i class="fas fa-edit me-2"></i>सदस्य विवरण संपादित करें</h2>
                        <p class="text-muted">आप यहाँ सदस्य के सभी विवरण को अपडेट कर सकते हैं</p>
                    </div>

                    <!-- Alert Container -->
                    <div id="alertContainer"></div>

                    <!-- Search Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-search me-2"></i>सदस्य खोजें</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="mb-0">
                                        <label for="searchMemberId" class="form-label">सदस्य ID</label>
                                        <input type="text" class="form-control" id="searchMemberId" placeholder="सदस्य ID दर्ज करें">
                                        <small class="text-muted">उदाहरण: 12345678</small>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="mb-0">
                                        <label for="searchMobileNumber" class="form-label">मोबाइल नंबर</label>
                                        <input type="text" class="form-control" id="searchMobileNumber" placeholder="मोबाइल नंबर दर्ज करें" maxlength="10">
                                        <small class="text-muted">उदाहरण: 9876543210</small>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="searchBtn" onclick="searchMember()">
                                        <i class="fas fa-search me-2"></i>खोजें
                                    </button>
                                </div>
                            </div>
                            <small class="d-block mt-2 text-info"><i class="fas fa-info-circle me-1"></i>सदस्य ID या मोबाइल नंबर में से कोई एक दर्ज करें</small>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">लोड हो रहा है...</span>
                        </div>
                        <p class="mt-3 text-muted">कृपया प्रतीक्षा करें...</p>
                    </div>

                    <!-- Member Details Section (Hidden initially) -->
                    <div id="memberDetailsSection" style="display: none;">
                        <!-- Member Info Card -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>सदस्य जानकारी</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>सदस्य ID:</strong> <span id="displayMemberId"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>लॉगिन ID:</strong> <span id="displayLoginId"></span></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p><strong>आधार नंबर:</strong> <span id="displayAadhar"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>स्थिति:</strong> <span id="displayStatus"></span></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>योगदान तिथि:</strong> <span id="displayCreatedAt"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>अंतिम अपडेट:</strong> <span id="displayUpdatedAt"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabbed Form -->
                        <form id="memberEditForm">
                            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="memberEditTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                        <i class="fas fa-user-circle me-2"></i>व्यक्तिगत विवरण
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="additional-tab" data-bs-toggle="tab" data-bs-target="#additional" type="button" role="tab">
                                        <i class="fas fa-briefcase me-2"></i>अतिरिक्त विवरण
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="location-tab" data-bs-toggle="tab" data-bs-target="#location" type="button" role="tab">
                                        <i class="fas fa-map-marker-alt me-2"></i>स्थान विवरण
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="nominee-tab" data-bs-toggle="tab" data-bs-target="#nominee" type="button" role="tab">
                                        <i class="fas fa-user-tie me-2"></i>नॉमिनी विवरण
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                                        <i class="fas fa-cog me-2"></i>खाता विवरण
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="memberEditTabContent">
                                <!-- Tab 1: Personal Details -->
                                <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                    <div class="edit-form-section">
                                        <h5 class="mb-4">व्यक्तिगत विवरण</h5>
                                        
                                        <div class="mb-3">
                                            <label for="editFullName" class="form-label">पूरा नाम <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                                            <small class="text-danger" id="editFullNameError"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editAadharNumber" class="form-label">आधार नंबर <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editAadharNumber" name="aadhar_number" placeholder="XXXX XXXX XXXX" disabled>
                                            <small class="text-muted">यह फील्ड संपादन योग्य नहीं है</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editFatherName" class="form-label">पिता/पति का नाम <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editFatherName" name="father_husband_name" required>
                                            <small class="text-danger" id="editFatherNameError"></small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editDob" class="form-label">जन्म तिथि <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="editDob" name="date_of_birth" required>
                                                    <small class="text-danger" id="editDobError"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editMobile" class="form-label">मोबाइल नंबर <span class="text-danger">*</span></label>
                                                    <input type="tel" class="form-control" id="editMobile" name="mobile_number" maxlength="10" required>
                                                    <small class="text-danger" id="editMobileError"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editGender" class="form-label">लिंग <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="editGender" name="gender" required>
                                                        <option value="">-- चुनें --</option>
                                                        <option value="पुरुष">पुरुष</option>
                                                        <option value="महिला">महिला</option>
                                                    </select>
                                                    <small class="text-danger" id="editGenderError"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editEmail" class="form-label">ईमेल (वैकल्पिक)</label>
                                                    <input type="email" class="form-control" id="editEmail" name="email">
                                                    <small class="text-danger" id="editEmailError"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Additional Details -->
                                <div class="tab-pane fade" id="additional" role="tabpanel">
                                    <div class="edit-form-section">
                                        <h5 class="mb-4">अतिरिक्त विवरण</h5>
                                        
                                        <div class="mb-3">
                                            <label for="editOccupation" class="form-label">व्यवसाय <span class="text-danger">*</span></label>
                                            <select class="form-select" id="editOccupation" name="occupation" required>
                                                <option value="">-- चुनें --</option>
                                                <option value="सरकारी नौकरी">सरकारी नौकरी</option>
                                                <option value="निजी नौकरी">निजी नौकरी</option>
                                                <option value="व्यापार">व्यापार</option>
                                                <option value="कृषि">कृषि</option>
                                                <option value="गृहिणी">गृहिणी</option>
                                                <option value="विद्यार्थी">विद्यार्थी</option>
                                                <option value="ठेकेदारी">ठेकेदारी</option>
                                                <option value="जनप्रतिनिधि">जनप्रतिनिधि</option>
                                                <option value="सार्वजनिक विभाग">सार्वजनिक विभाग</option>
                                                <option value="अन्य">अन्य</option>
                                            </select>
                                            <small class="text-danger" id="editOccupationError"></small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editOfficeName" class="form-label">कार्यालय का नाम</label>
                                            <input type="text" class="form-control" id="editOfficeName" name="office_name" placeholder="कार्यालय का नाम दर्ज करें">
                                        </div>

                                        <div class="mb-3">
                                            <label for="editOfficeAddress" class="form-label">कार्यालय पता</label>
                                            <textarea class="form-control" id="editOfficeAddress" name="office_address" rows="3" placeholder="कार्यालय का पूरा पता दर्ज करें"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 3: Location Details -->
                                <div class="tab-pane fade" id="location" role="tabpanel">
                                    <div class="edit-form-section">
                                        <h5 class="mb-4">स्थान विवरण</h5>
                                        
                                        <div class="mb-3">
                                            <label for="editState" class="form-label">राज्य <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="editState" name="state" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editDistrict" class="form-label">जिला <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="editDistrict" name="district" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editBlock" class="form-label">ब्लॉक <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="editBlock" name="block" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editPermanentAddress" class="form-label">स्थायी पता <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="editPermanentAddress" name="permanent_address" rows="3" placeholder="स्थायी पता दर्ज करें" required></textarea>
                                            <small class="text-danger" id="editPermanentAddressError"></small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 4: Nominee Details -->
                                <div class="tab-pane fade" id="nominee" role="tabpanel">
                                    <div class="edit-form-section">
                                        <h5 class="mb-4">नॉमिनी विवरण</h5>
                                        
                                        <div class="alert alert-info mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            यदि नॉमिनी की जानकारी उपलब्ध नहीं है तो इस टैब को छोड़ सकते हैं।
                                        </div>

                                        <div class="mb-3">
                                            <label for="editNomineeName" class="form-label">नॉमिनी व्यक्ति का नाम</label>
                                            <input type="text" class="form-control" id="editNomineeName" name="nominee_name" placeholder="नॉमिनी का नाम दर्ज करें">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editNomineeRelation" class="form-label">संबंध</label>
                                                    <select class="form-select" id="editNomineeRelation" name="nominee_relation">
                                                        <option value="">-- चुनें --</option>
                                                        <option value="पत्नी">पत्नी</option>
                                                        <option value="पति">पति</option>
                                                        <option value="बेटा">बेटा</option>
                                                        <option value="बेटी">बेटी</option>
                                                        <option value="माता">माता</option>
                                                        <option value="पिता">पिता</option>
                                                        <option value="भाई">भाई</option>
                                                        <option value="बहू">बहू</option>
                                                        <option value="अन्य">अन्य</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="editNomineeMobile" class="form-label">नॉमिनी मोबाइल नंबर</label>
                                                    <input type="tel" class="form-control" id="editNomineeMobile" name="nominee_mobile" maxlength="10" placeholder="मोबाइल नंबर दर्ज करें">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="editNomineeAadhar" class="form-label">नॉमिनी आधार नंबर</label>
                                            <input type="text" class="form-control" id="editNomineeAadhar" name="nominee_aadhar" placeholder="XXXX XXXX XXXX" maxlength="12">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 5: Account Details -->
                                <div class="tab-pane fade" id="account" role="tabpanel">
                                    <div class="edit-form-section">
                                        <h5 class="mb-4">खाता विवरण</h5>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="displayUtrNumber" class="form-label">UTR नंबर</label>
                                                <input type="text" class="form-control" id="displayUtrNumber" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="editPaymentVerified" class="form-label">भुगतान सत्यापित</label>
                                                <select class="form-select" id="editPaymentVerified" name="payment_verified">
                                                    <option value="0">नहीं</option>
                                                    <option value="1">हाँ</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="editStatus" class="form-label"><strong>खाता स्थिति <span class="text-danger">*</span></strong></label>
                                                <select class="form-select" id="editStatus" name="status" required>
                                                    <option value="0">निष्क्रिय (0)</option>
                                                    <option value="1">सक्रिय (1)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-4 d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>परिवर्तन सहेजें
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetSearch()">
                                    <i class="fas fa-sync me-2"></i>नया सदस्य खोजें
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- No Member Found Section (Hidden initially) -->
                    <div id="noMemberSection" style="display: none;" class="text-center py-5">
                        <i class="fas fa-search text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-muted">सदस्य नहीं मिला</h4>
                        <p class="text-secondary">कृपया सही Member ID या मोबाइल नंबर दर्ज करें</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Member Edit JS -->
    <script src="js/edit-member.js"></script>
</body>
</html>
