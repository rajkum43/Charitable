<?php
session_start();

$page_title = 'Death Claim Form';

// Check if user is logged in
$is_logged_in = isset($_SESSION['member_id']);
$member_id = $is_logged_in ? $_SESSION['member_id'] : null;
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - BRCT Bharat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/death-claim.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>

    <div class="container container-wrapper py-5">
        <div class="form-card">
            <div class="form-header text-center mb-4">
                <h2><i class="fas fa-heart-broken" style="color: #8B0000;"></i> मृत्यु सहयोग आवेदन</h2>
                <p class="text-muted">Death Claim Application Form</p>
            </div>

            <!-- Member Search Section -->
            <div id="member-search-section" class="search-section mb-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>महत्वपूर्ण:</strong> मृत सदस्य की जानकारी दर्ज करें
                </div>

                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="form-group mb-3">
                            <label for="search_aadhaar" class="form-label">Aadhaar (Last 8 Digits)</label>
                            <input type="text" class="form-control" id="search_aadhaar" placeholder="अंतिम 8 अंक दर्ज करें" maxlength="8">
                        </div>
                        <button type="button" class="btn btn-primary w-100" onclick="searchMember()">
                            <i class="fas fa-search me-2"></i>खोजें (Search)
                        </button>
                        <div id="search-loader" style="display: none; text-align: center; margin-top: 15px;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-primary mt-2">खोज रहे हैं...</p>
                        </div>
                        <div id="search-error" class="alert alert-danger mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Main Form (shown after member search) -->
            <div id="main-form-section" style="display: none;">
                <form id="death-claim-form" method="POST" enctype="multipart/form-data" novalidate>
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4" id="deathClaimTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-member-tab" data-bs-toggle="tab" data-bs-target="#tab-member" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>सदस्य जानकारी
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-death-tab" data-bs-toggle="tab" data-bs-target="#tab-death" type="button" role="tab">
                                <i class="fas fa-calendar me-2"></i>मृत्यु विवरण
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-nominee-tab" data-bs-toggle="tab" data-bs-target="#tab-nominee" type="button" role="tab">
                                <i class="fas fa-address-card me-2"></i>नॉमिनी विवरण
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-bank-tab" data-bs-toggle="tab" data-bs-target="#tab-bank" type="button" role="tab">
                                <i class="fas fa-university me-2"></i>बैंक विवरण
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-documents-tab" data-bs-toggle="tab" data-bs-target="#tab-documents" type="button" role="tab">
                                <i class="fas fa-file-upload me-2"></i>दस्तावेज़
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-review-tab" data-bs-toggle="tab" data-bs-target="#tab-review" type="button" role="tab">
                                <i class="fas fa-eye me-2"></i>पूर्वावलोकन
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="deathClaimTabContent">

                        <!-- Tab 1: Member Details -->
                        <div class="tab-pane fade show active" id="tab-member" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-user-circle"></i> सदस्य की जानकारी</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="member_id" class="form-label required-field">सदस्य ID</label>
                                        <input type="text" class="form-control" id="member_id" name="member_id" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="member_name" class="form-label required-field">पूरा नाम</label>
                                        <input type="text" class="form-control" id="member_name" name="member_name" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="member_father_name" class="form-label">पिता/पति का नाम</label>
                                        <input type="text" class="form-control" id="member_father_name" name="member_father_name" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="member_dob" class="form-label">जन्म तिथि</label>
                                        <input type="date" class="form-control" id="member_dob" name="member_dob" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="member_address" class="form-label">पता</label>
                                <textarea class="form-control" id="member_address" name="member_address" rows="2" readonly></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" onclick="clearSearch()">नए सदस्य को खोजें</button>
                                <button type="button" class="btn btn-primary" onclick="goToTab('tab-death')">अगला</button>
                            </div>
                        </div>

                        <!-- Tab 2: Death Details -->
                        <div class="tab-pane fade" id="tab-death" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-heart-broken"></i> मृत्यु विवरण</h5>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-warning me-2"></i>
                                <strong>नोट:</strong> मृत्यु तिथि आपके जन्म तिथि से बाद की होनी चाहिए।
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="death_date" class="form-label required-field">मृत्यु तिथि</label>
                                        <input type="date" class="form-control" id="death_date" name="death_date" required onchange="calculateAge()">
                                        <small class="text-danger" id="death_date_error"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="age_at_death" class="form-label">मृत्यु के समय आयु</label>
                                        <input type="number" class="form-control" id="age_at_death" name="age_at_death" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="death_place" class="form-label required-field">मृत्यु स्थान</label>
                                <input type="text" class="form-control" id="death_place" name="death_place" placeholder="मृत्यु का स्थान" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="death_reason" class="form-label">मृत्यु का कारण (यदि ज्ञात हो)</label>
                                <textarea class="form-control" id="death_reason" name="death_reason" rows="2" placeholder="मृत्यु का कारण बताएं"></textarea>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <button type="button" class="btn btn-secondary" onclick="goToTab('tab-member')">पिछला</button>
                                <button type="button" class="btn btn-primary" onclick="goToTab('tab-nominee')">अगला</button>
                            </div>
                        </div>

                        <!-- Tab 3: Nominee Details -->
                        <div class="tab-pane fade" id="tab-nominee" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-address-card"></i> नॉमिनी विवरण</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>नॉमिनी:</strong> जो व्यक्ति सहायता प्राप्त करेगा
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nominee_name" class="form-label required-field">नॉमिनी का नाम</label>
                                        <input type="text" class="form-control" id="nominee_name" name="nominee_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nominee_relation" class="form-label required-field">संबंध</label>
                                        <select class="form-select" id="nominee_relation" name="nominee_relation" required>
                                            <option value="">-- संबंध चुनें --</option>
                                            <option value="पत्नी">पत्नी (Wife)</option>
                                            <option value="पति">पति (Husband)</option>
                                            <option value="बेटा">बेटा (Son)</option>
                                            <option value="बेटी">बेटी (Daughter)</option>
                                            <option value="माता">माता (Mother)</option>
                                            <option value="पिता">पिता (Father)</option>
                                            <option value="भाई">भाई (Brother)</option>
                                            <option value="बहू">बहू (Daughter-in-law)</option>
                                            <option value="दामाद">दामाद (Son-in-law)</option>
                                            <option value="अन्य">अन्य (Other)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nominee_dob" class="form-label">नॉमिनी की जन्म तिथि</label>
                                        <input type="date" class="form-control" id="nominee_dob" name="nominee_dob">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="nominee_mobile" class="form-label required-field">मोबाइल नंबर</label>
                                        <input type="tel" class="form-control" id="nominee_mobile" name="nominee_mobile" placeholder="10 अंको वाला" maxlength="10" required>
                                        <small class="text-danger" id="mobile_error"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="nominee_address" class="form-label">नॉमिनी का पता</label>
                                <textarea class="form-control" id="nominee_address" name="nominee_address" rows="2"></textarea>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <button type="button" class="btn btn-secondary" onclick="goToTab('tab-death')">पिछला</button>
                                <button type="button" class="btn btn-primary" onclick="goToTab('tab-bank')">अगला</button>
                            </div>
                        </div>

                        <!-- Tab 4: Bank Details -->
                        <div class="tab-pane fade" id="tab-bank" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-university"></i> बैंक खाता विवरण</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>सहायता राशि इसी खाते में जमा की जाएगी।</strong>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bank_name" class="form-label required-field">बैंक का नाम</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="IFSC दर्ज करने पर स्वचालित भर जाएगा" required>
                                            <span class="input-group-text" title="Auto-filled from IFSC">
                                                <i class="fas fa-magic" style="color: #8B0000;"></i>
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-1">यदि उचित न हो तो आप संपादित कर सकते हैं</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="account_number" class="form-label required-field">खाता संख्या</label>
                                        <input type="text" class="form-control" id="account_number" name="account_number" placeholder="खाता संख्या" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="ifsc_code" class="form-label required-field">IFSC कोड <small class="text-muted">(स्वचालित विवरण के लिए)</small></label>
                                        <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" placeholder="जैसे: SBIN0001234" required style="text-transform: uppercase;">
                                        <small class="text-muted d-block mt-1">IFSC कोड दर्ज करें - बैंक विवरण स्वचालित रूप से भर जाएंगे।</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="branch_name" class="form-label">शाखा का नाम</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="IFSC दर्ज करने पर स्वचालित भर जाएगा">
                                            <span class="input-group-text" title="Auto-filled from IFSC">
                                                <i class="fas fa-magic" style="color: #8B0000;"></i>
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-1">यदि उचित न हो तो आप संपादित कर सकते हैं</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="account_holder_name" class="form-label required-field">खाते धारक का नाम</label>
                                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" placeholder="खाते में दर्ज नाम" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="upi_id" class="form-label">UPI ID (वैकल्पिक)</label>
                                <input type="text" class="form-control" id="upi_id" name="upi_id" placeholder="जैसे: name@upi">
                                <small class="text-muted">UPI ID दर्ज करें या खाली छोड़ सकते हैं</small>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <button type="button" class="btn btn-secondary" onclick="goToTab('tab-nominee')">पिछला</button>
                                <button type="button" class="btn btn-primary" onclick="goToTab('tab-documents')">अगला</button>
                            </div>
                        </div>

                        <!-- Tab 5: Documents Upload -->
                        <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-file-upload"></i> दस्तावेज़ अपलोड करें</h5>
                            
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>महत्वपूर्ण:</strong> सभी दस्तावेज़ अनिवार्य हैं। अधिकतम फाइल आकार 2MB है।
                            </div>

                            <div class="mb-4">
                                <label class="form-label required-field"><i class="fas fa-id-card me-2"></i> मृत व्यक्ति का आधार कार्ड</label>
                                <div class="file-upload-area" onclick="document.getElementById('file_aadhaar_deceased').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <p>JPG, PNG, या PDF (Max 2MB)</p>
                                    <small class="text-muted" id="file_aadhaar_name">फाइल चुनने के लिए क्लिक करें</small>
                                </div>
                                <input type="file" id="file_aadhaar_deceased" name="file_aadhaar_deceased" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" required>
                                <small class="text-danger" id="error_aadhaar_deceased"></small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label required-field"><i class="fas fa-file-pdf me-2"></i> मृत्यु प्रमाण पत्र</label>
                                <div class="file-upload-area" onclick="document.getElementById('file_death_certificate').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <p>JPG, PNG, या PDF (Max 2MB)</p>
                                    <small class="text-muted" id="file_death_cert_name">फाइल चुनने के लिए क्लिक करें</small>
                                </div>
                                <input type="file" id="file_death_certificate" name="file_death_certificate" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" required>
                                <small class="text-danger" id="error_death_certificate"></small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label required-field"><i class="fas fa-file me-2"></i> पोस्टमॉर्टम रिपोर्ट (यदि उपलब्ध हो)</label>
                                <div class="file-upload-area" onclick="document.getElementById('file_postmortem').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <p>JPG, PNG, या PDF (Max 2MB)</p>
                                    <small class="text-muted" id="file_postmortem_name">फाइल चुनने के लिए क्लिक करें</small>
                                </div>
                                <input type="file" id="file_postmortem" name="file_postmortem" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-danger" id="error_postmortem"></small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label required-field"><i class="fas fa-id-card me-2"></i> नॉमिनी का आधार कार्ड</label>
                                <div class="file-upload-area" onclick="document.getElementById('file_nominee_aadhaar').click()">
                                    <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                                    <p>JPG, PNG, या PDF (Max 2MB)</p>
                                    <small class="text-muted" id="file_nominee_name">फाइल चुनने के लिए क्लिक करें</small>
                                </div>
                                <input type="file" id="file_nominee_aadhaar" name="file_nominee_aadhaar" accept=".jpg,.jpeg,.png,.pdf" style="display: none;" required>
                                <small class="text-danger" id="error_nominee_aadhaar"></small>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <button type="button" class="btn btn-secondary" onclick="goToTab('tab-bank')">पिछला</button>
                                <button type="button" class="btn btn-primary" onclick="goToTab('tab-review')">अगला</button>
                            </div>
                        </div>

                        <!-- Tab 6: Review and Submit -->
                        <div class="tab-pane fade" id="tab-review" role="tabpanel">
                            <h5 class="mb-3"><i class="fas fa-eye"></i> पूर्वावलोकन और सबमिट करें</h5>
                            
                            <div class="review-section mb-4">
                                <div class="review-header">
                                    <i class="fas fa-user-circle"></i> सदस्य जानकारी
                                </div>
                                <div class="review-content">
                                    <div id="review_member_details"></div>
                                </div>
                            </div>

                            <div class="review-section mb-4">
                                <div class="review-header">
                                    <i class="fas fa-heart-broken"></i> मृत्यु विवरण
                                </div>
                                <div class="review-content">
                                    <div id="review_death_details"></div>
                                </div>
                            </div>

                            <div class="review-section mb-4">
                                <div class="review-header">
                                    <i class="fas fa-address-card"></i> नॉमिनी विवरण
                                </div>
                                <div class="review-content">
                                    <div id="review_nominee_details"></div>
                                </div>
                            </div>

                            <div class="review-section mb-4">
                                <div class="review-header">
                                    <i class="fas fa-university"></i> बैंक विवरण
                                </div>
                                <div class="review-content">
                                    <div id="review_bank_details"></div>
                                </div>
                            </div>

                            <div class="review-section mb-4">
                                <div class="review-header">
                                    <i class="fas fa-file-upload"></i> अपलोड की गई फाइलें
                                </div>
                                <div class="review-content">
                                    <div id="review_documents"></div>
                                </div>
                            </div>

                            <div id="submit-loader" style="display: none; text-align: center; margin: 20px 0;">
                                <div class="spinner-border spinner-border-lg text-primary" role="status">
                                    <span class="visually-hidden">Submitting...</span>
                                </div>
                                <p class="text-primary mt-3">आवेदन जमा किया जा रहा है...</p>
                            </div>

                            <div id="submit-success" class="alert alert-success" style="display: none;">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>सफल!</strong> आपका आवेदन जमा हो गया है।
                                <p class="mt-2">आवेदन संख्या: <strong id="claim_id"></strong></p>
                            </div>

                            <div id="submit-error" class="alert alert-danger" style="display: none;">
                                <strong>त्रुटि:</strong> <span id="error-message"></span>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <button type="button" class="btn btn-secondary" onclick="goToTab('tab-documents')">पिछला</button>
                                <button type="button" class="btn btn-success btn-lg" id="submit-btn" onclick="submitForm()">
                                    <i class="fas fa-check-circle me-2"></i> सबमिट करें (Submit)
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Hidden field for member_id -->
                    <input type="hidden" id="hidden_member_id" name="hidden_member_id" value="">
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/death-claim.js"></script>
</body>
</html>
