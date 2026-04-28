<?php
session_start();

$page_title = 'विवाह सहयोग आवेदन';

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
    <!-- External CSS for Beti Vivah Form -->
    <link rel="stylesheet" href="../assets/css/beti_vivah_aavedan.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Main CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Member Directory CSS -->
    <link rel="stylesheet" href="../assets/css/members-directory.css">
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include '../components/navbar.php'; ?>

    <div class="container container-wrapper">
        <div class="form-card">
            <div class="form-header">
                <h2><i class="fas fa-heart" style="color: #e74c3c;"></i> विवाह सहयोग आवेदन</h2>
                <p>कृपया नीचे दिए गए विवरण भरें</p>
            </div>

            <div class="alert-info">
                <i class="fas fa-info-circle"></i>
                <strong> महत्वपूर्ण:</strong> यह आवेदन ध्यान से भरें। आपको सहयोग राशि प्रदान की जाएगी जब आपका आवेदन प्रशासन द्वारा अनुमोदित हो जाए।
            </div>

            <!-- Member Verification Form (for non-logged-in users) -->
            <div id="verification-form" style="<?php echo $is_logged_in ? 'display: none;' : 'display: block;'; ?>">
                <div style="background: #f0f8ff; border: 2px solid #3498db; border-radius: 10px; padding: 30px; text-align: center;">
                    <h5 style="color: #2c3e50; margin-bottom: 20px;">
                        <i class="fas fa-search me-2"></i>सदस्य को खोजें
                    </h5>
                    <p style="color: #555; margin-bottom: 20px;">आवेदन जारी रखने के लिए अपनी सदस्य ID दर्ज करें</p>
                    
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6 offset-md-3">
                            <div class="form-group">
                                <input type="text" class="form-control" id="search_member_id" placeholder="सदस्य ID दर्ज करें" style="font-size: 16px; padding: 12px;">
                                <small class="text-muted" style="display: block; margin-top: 8px;">जैसे: BR001234</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 offset-md-4">
                            <button type="button" class="btn btn-primary w-100" onclick="verifyMember()" style="padding: 10px; font-weight: 600;">
                                <i class="fas fa-search me-2"></i>खोजें
                            </button>
                        </div>
                    </div>

                    <div id="verification-loader" style="display: none; text-align: center; margin-top: 20px;">
                        <div class="spinner" style="margin: 0 auto;"></div>
                        <p style="color: #3498db; margin-top: 10px;">खोज रहे हैं...</p>
                    </div>

                    <div id="verification-error" style="display: none; background: #ffe8e8; border: 1px solid #ff6b6b; color: #c92a2a; padding: 12px; border-radius: 6px; margin-top: 15px;"></div>

                    <div id="verification-success" style="display: none; background: #d4edda; border: 1px solid #90caf9; color: #155724; padding: 12px; border-radius: 6px; margin-top: 15px;">
                        <i class="fas fa-check-circle me-2"></i><span id="success-text"></span>
                    </div>
                </div>
            </div>

            <!-- Main Form (shown after verification or if logged in) -->
            <div id="main-form" style="<?php echo !$is_logged_in ? 'display: none;' : 'display: block;'; ?>">
            <ul class="nav nav-tabs-custom mb-4" id="vivahTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="userdetails-tab" data-bs-toggle="tab" data-bs-target="#userdetails" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>सदस्य विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="couple-tab" data-bs-toggle="tab" data-bs-target="#couple" type="button" role="tab">
                        <i class="fas fa-heart me-2"></i>दुल्हन व दूल्हा
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab">
                        <i class="fas fa-home me-2"></i>अतिरिक्त विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                        <i class="fas fa-file-upload me-2"></i>दस्तावेज़
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab">
                        <i class="fas fa-eye me-2"></i>पूर्वावलोकन
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <form id="beti-vivah-form" method="POST" enctype="multipart/form-data">
                <div class="tab-content" id="vivahTabContent">

                    <!-- Tab 1: User Details -->
                    <div class="tab-pane fade show active" id="userdetails" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-user-circle"></i> सदस्य का विवरण
                        </h5>

                        <div class="alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>यह विवरण आपके पंजीकृत खाते से स्वचालित रूप से भरेगा।</strong>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="member_name" class="required-field">सदस्य का नाम</label>
                                    <input type="text" class="form-control" id="member_name" name="member_name" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="member_id_display" class="required-field">सदस्य ID</label>
                                    <input type="text" class="form-control" id="member_id_display" name="member_id_display" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="member_mobile" class="required-field">मोबाइल नंबर</label>
                                    <input type="text" class="form-control" id="member_mobile" name="member_mobile" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="member_email" class="required-field">ईमेल</label>
                                    <input type="text" class="form-control" id="member_email" name="member_email" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="member_address" class="required-field">पता</label>
                                    <textarea class="form-control" id="member_address" name="member_address" readonly rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden field for birth date (for DB storage if needed) -->
                        <input type="hidden" id="member_dob" name="member_dob">

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-next" onclick="showTab('couple-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 2: Bride and Groom Details -->
                    <div class="tab-pane fade" id="couple" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-heart"></i> दुल्हन व दूल्हा विवरण
                        </h5>

                        <!-- Bride Details -->
                        <div class="mb-4">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-venus text-danger me-2"></i>दुल्हन का विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bride_name" class="required-field">दुल्हन का नाम</label>
                                        <input type="text" class="form-control" id="bride_name" name="bride_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bride_dob" class="required-field">जन्म तिथि</label>
                                        <input type="date" class="form-control" id="bride_dob" name="bride_dob" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bride_education">शिक्षा का स्तर</label>
                                        <select class="form-select" id="bride_education" name="bride_education">
                                            <option value="">-- चुनें --</option>
                                            <option value="निरक्षर">निरक्षर</option>
                                            <option value="प्राथमिक">प्राथमिक</option>
                                            <option value="माध्यमिक">माध्यमिक</option>
                                            <option value="उच्च माध्यमिक">उच्च माध्यमिक</option>
                                            <option value="स्नातक">स्नातक</option>
                                            <option value="स्नातकोत्तर">स्नातकोत्तर</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bride_health" class="required-field">स्वास्थ्य स्थिति</label>
                                        <select class="form-select" id="bride_health" name="bride_health" required>
                                            <option value="">-- चुनें --</option>
                                            <option value="स्वस्थ">स्वस्थ</option>
                                            <option value="कोई विशेष बीमारी">कोई विशेष बीमारी</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Groom Details -->
                        <div class="mb-4">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-mars text-primary me-2"></i>दूल्हे का विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groom_name" class="required-field">दूल्हे का नाम</label>
                                        <input type="text" class="form-control" id="groom_name" name="groom_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groom_dob" class="required-field">जन्म तिथि</label>
                                        <input type="date" class="form-control" id="groom_dob" name="groom_dob" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groom_occupation" class="required-field">व्यवसाय</label>
                                        <input type="text" class="form-control" id="groom_occupation" name="groom_occupation" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groom_education">शिक्षा का स्तर</label>
                                        <select class="form-select" id="groom_education" name="groom_education">
                                            <option value="">-- चुनें --</option>
                                            <option value="निरक्षर">निरक्षर</option>
                                            <option value="प्राथमिक">प्राथमिक</option>
                                            <option value="माध्यमिक">माध्यमिक</option>
                                            <option value="उच्च माध्यमिक">उच्च माध्यमिक</option>
                                            <option value="स्नातक">स्नातक</option>
                                            <option value="स्नातकोत्तर">स्नातकोत्तर</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="wedding_date" class="required-field">विवाह की तारीख</label>
                                        <input type="date" class="form-control" id="wedding_date" name="wedding_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="groom_father_name" class="required-field">दूल्हे के पिता का नाम</label>
                                        <input type="text" class="form-control" id="groom_father_name" name="groom_father_name" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('userdetails-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('family-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Additional Details -->
                    <div class="tab-pane fade" id="family" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-home"></i> अतिरिक्त विवरण
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family_income" class="required-field">वार्षिक पारिवारिक आय (₹)</label>
                                    <input type="number" class="form-control" id="family_income" name="family_income" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family_members" class="required-field">परिवार के सदस्यों की संख्या</label>
                                    <input type="number" class="form-control" id="family_members" name="family_members" required min="1">
                                </div>
                            </div>
                        </div>

                        <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px; margin-top: 25px;">
                            <i class="fas fa-university me-2"></i>बैंक विवरण
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ifsc_code" class="required-field">IFSC कोड</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" required placeholder="Ex: SBIN0001234">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch_name" class="required-field">शाखा का नाम</label>
                                    <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number" class="required-field">खाता संख्या</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_name" class="required-field">बैंक का नाम</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="account_holder_name" class="required-field">खाता धारक का नाम</label>
                            <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" required>
                        </div>
                        <div class="form-group">
                            <label for="upi_id">UPI ID (वैकल्पिक)</label>
                            <input type="text" class="form-control" id="upi_id" name="upi_id" placeholder="जैसे: yourname@upi">
                            <small class="text-muted">उदाहरण: name@okhdfcbank, name@paytm</small>
                        </div>
                        <div class="form-group">
                            <label for="remarks">कोई अन्य जानकारी (वैकल्पिक)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="यदि कोई और जानकारी जोड़नी है तो यहाँ लिखें..."></textarea>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('couple-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('documents-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 4: Documents Upload -->
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-file-upload"></i> आवश्यक दस्तावेज़
                        </h5>

                        <div class="alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>नोट:</strong> कृपया निम्नलिखित दस्तावेज़ अपलोड करें (PDF/JPG, अधिकतम 5MB)
                        </div>

                        <div class="mb-4">
                            <label for="aadhar_proof" class="form-label required-field">आधार कार्ड की फोटोकॉपी</label>
                            <div class="file-upload-box" onclick="document.getElementById('aadhar_proof').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="aadhar_proof" name="aadhar_proof" accept="image/*,.pdf" required>
                            <small class="text-danger" id="aadhar_proof_error"></small>
                            <p class="mt-2" id="aadhar_proof_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="mb-4">
                            <label for="address_proof" class="form-label required-field">पता प्रमाण (बिजली/गैस बिल)</label>
                            <div class="file-upload-box" onclick="document.getElementById('address_proof').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="address_proof" name="address_proof" accept="image/*,.pdf" required>
                            <small class="text-danger" id="address_proof_error"></small>
                            <p class="mt-2" id="address_proof_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="mb-4">
                            <label for="income_proof" class="form-label required-field">आय प्रमाण पत्र</label>
                            <div class="file-upload-box" onclick="document.getElementById('income_proof').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="income_proof" name="income_proof" accept="image/*,.pdf" required>
                            <small class="text-danger" id="income_proof_error"></small>
                            <p class="mt-2" id="income_proof_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="mb-4">
                            <label for="marriage_certificate" class="form-label required-field">विवाह कार्ड/निमंत्रण</label>
                            <div class="file-upload-box" onclick="document.getElementById('marriage_certificate').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="marriage_certificate" name="marriage_certificate" accept="image/*,.pdf" required>
                            <small class="text-danger" id="marriage_certificate_error"></small>
                            <p class="mt-2" id="marriage_certificate_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="form-check" style="background: #fff3cd; padding: 15px; border-radius: 6px; border: 1px solid #ffc107;">
                            <input type="checkbox" class="form-check-input" id="confirm_details" name="confirm_details" required>
                            <label class="form-check-label" for="confirm_details">
                                <strong>मैं पुष्टि करता/करती हूँ कि दी गई सभी जानकारी सही है और मैं इसके लिए जिम्मेदार हूँ।</strong>
                            </label>
                            <small class="text-danger d-block" id="confirm_details_error"></small>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('family-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('review-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 5: Review and Submit -->
                    <div class="tab-pane fade" id="review" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-eye"></i> पूर्वावलोकन व अंतिम जमा
                        </h5>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-user-circle me-2"></i>सदस्य विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">सदस्य का नाम</div>
                                        <div class="preview-item-value" id="preview_member_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">सदस्य ID</div>
                                        <div class="preview-item-value" id="preview_member_id">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-heart me-2"></i>दुल्हन विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">दुल्हन का नाम</div>
                                        <div class="preview-item-value" id="preview_bride_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">जन्म तिथि</div>
                                        <div class="preview-item-value" id="preview_bride_dob">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">स्वास्थ्य स्थिति</div>
                                        <div class="preview-item-value" id="preview_bride_health">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-mars me-2"></i>दूल्हे विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">दूल्हे का नाम</div>
                                        <div class="preview-item-value" id="preview_groom_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">जन्म तिथि</div>
                                        <div class="preview-item-value" id="preview_groom_dob">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">व्यवसाय</div>
                                        <div class="preview-item-value" id="preview_groom_occupation">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-home me-2"></i>अतिरिक्त जानकारी
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">विवाह की तारीख</div>
                                        <div class="preview-item-value" id="preview_wedding_date">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">पारिवारिक आय (वार्षिक)</div>
                                        <div class="preview-item-value" id="preview_family_income">-</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">परिवार के सदस्य</div>
                                        <div class="preview-item-value" id="preview_family_members">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">बैंक खाता संख्या</div>
                                        <div class="preview-item-value" id="preview_account_number">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="loader" id="loader" style="margin-top: 20px;">
                            <div class="spinner"></div>
                            <p style="margin-top: 10px; color: var(--accent-color);">आवेदन जमा किया जा रहा है...</p>
                        </div>

                        <div id="success-message" class="alert alert-success" style="display:none; margin-top: 20px;">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>बधाई हो!</strong> आपका आवेदन सफलतापूर्वक जमा हो गया।
                            <p class="mb-0 mt-2">आवेदन संख्या: <strong id="application-number">-</strong></p>
                        </div>

                        <div id="error-message" class="alert alert-danger" style="display:none; margin-top: 20px;">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>त्रुटि:</strong> <span id="error-text">-</span>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('documents-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="submit" class="btn-submit" id="submit-btn" style="flex: 1; margin-left: 10px;">
                                <i class="fas fa-check me-2"></i>आवेदन जमा करें
                            </button>
                        </div>
                    </div>

                </div>
                <!-- Hidden input to store member_id -->
                <input type="hidden" id="hidden_member_id" name="hidden_member_id" value="<?php echo htmlspecialchars($member_id); ?>">
            </form>
            </div>
            <!-- End of main-form div -->
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <!-- External JavaScript for Beti Vivah Form -->
    <script src="../assets/js/beti_vivah_aavedan.js"></script>
</body>
</html>
