<?php
session_start();

$page_title = 'मृत्यु सहयोग आवेदन';

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
    <!-- External CSS for Death Aavedan Form -->
    <link rel="stylesheet" href="../assets/css/death_aavedan.css">
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
                <h2><i class="fas fa-heart-broken" style="color: #8B0000;"></i> मृत्यु सहयोग आवेदन</h2>
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
                        <i class="fas fa-search me-2"></i>मृत सदस्य को खोजें
                    </h5>
                    <p style="color: #555; margin-bottom: 20px;">आवेदन जारी रखने के लिए मृत व्यक्ति की सदस्य ID दर्ज करें</p>
                    
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6 offset-md-3">
                            <div class="form-group">
                                <input type="text" class="form-control" id="search_member_id" placeholder="मृत व्यक्ति की सदस्य ID दर्ज करें" style="font-size: 16px; padding: 12px;">
                                <small class="text-muted" style="display: block; margin-top: 8px;">जैसे: 32600611</small>
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
            <ul class="nav nav-tabs-custom mb-4" id="deathTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="userdetails-tab" data-bs-toggle="tab" data-bs-target="#userdetails" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>सदस्य विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="applicant-tab" data-bs-toggle="tab" data-bs-target="#applicant" type="button" role="tab">
                        <i class="fas fa-file-alt me-2"></i>आवेदक विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="deceased-tab" data-bs-toggle="tab" data-bs-target="#deceased" type="button" role="tab">
                        <i class="fas fa-users me-2"></i>मृत व्यक्ति का विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab">
                        <i class="fas fa-home me-2"></i>आर्थिक विवरण
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab">
                        <i class="fas fa-university me-2"></i>बैंक विवरण
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
            <form id="death-aavedan-form" method="POST" enctype="multipart/form-data" novalidate>
                <div class="tab-content" id="deathTabContent">

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
                                    <label for="member_dob" class="required-field">जन्म तिथि</label>
                                    <input type="text" class="form-control" id="member_dob" name="member_dob" readonly>
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

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-next" onclick="showTab('applicant-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 2: Applicant Details (New) -->
                    <div class="tab-pane fade" id="applicant" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-file-alt"></i> आवेदक का विवरण
                        </h5>

                        <div class="alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>महत्वपूर्ण:</strong> आवेदक आपका कौन है? कृपया अपना संबंध दर्ज करें।
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="applicant_name" class="required-field">आवेदक का नाम</label>
                                    <input type="text" class="form-control" id="applicant_name" name="applicant_name" placeholder="अपना पूरा नाम दर्ज करें">
                                    <small class="text-muted">आपका पूरा नाम दर्ज करें (जो आवेदन कर रहे हैं)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="applicant_dob" class="required-field">आवेदक की जन्म तिथि</label>
                                    <input type="date" class="form-control" id="applicant_dob" name="applicant_dob">
                                    <small class="text-muted">आपकी जन्म तिथि दर्ज करें (YYYY-MM-DD)</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" id="relation_father" name="applicant_relation" value="पिता" required>
                                    <label class="form-check-label" for="relation_father">
                                        <strong>पिता</strong>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" id="relation_daughter" name="applicant_relation" value="पुत्री" required>
                                    <label class="form-check-label" for="relation_daughter">
                                        <strong>पुत्री</strong>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="relation_wife" name="applicant_relation" value="पत्नी" required>
                                    <label class="form-check-label" for="relation_wife">
                                        <strong>पत्नी</strong>
                                    </label>
                                </div>
                            </div>
                            <small class="text-danger" id="applicant_relation_error"></small>
                        </div>

                        <div class="form-group">
                            <label for="applicant_parent_name" class="required-field">अभिभावक का नाम</label>
                            <input type="text" class="form-control" id="applicant_parent_name" name="applicant_parent_name" placeholder="अपने अभिभावक का पूरा नाम दर्ज करें">
                            <small class="text-muted">आपके पिता/माता/पति का नाम दर्ज करें</small>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('userdetails-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('deceased-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Deceased Details -->
                    <div class="tab-pane fade" id="deceased" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-users"></i> मृत व्यक्ति की जानकारी
                        </h5>

                        <div class="alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>महत्वपूर्ण:</strong> मृत व्यक्ति का सदस्य ID और जन्म तिथि अनिवार्य है। मृत व्यक्ति कम से कम 1 वर्ष का सदस्य होना चाहिए और आयु 18-60 वर्ष के बीच होनी चाहिए।
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deceased_name" class="required-field">मृत व्यक्ति का नाम</label>
                                    <input type="text" class="form-control" id="deceased_name" name="deceased_name" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                    <small class="text-muted">स्वचालित रूप से खोज के आधार पर भरा जाएगा</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deceased_member_id" class="required-field">मृत व्यक्ति का सदस्य ID</label>
                                    <input type="text" class="form-control" id="deceased_member_id" name="deceased_member_id" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                    <small class="text-muted">स्वचालित रूप से खोज के आधार पर भरा जाएगा</small>
                                    <small class="text-danger" id="deceased_member_id_error"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deceased_dob" class="required-field">मृत व्यक्ति की जन्म तिथि</label>
                                    <input type="date" class="form-control" id="deceased_dob" name="deceased_dob" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                    <small class="text-muted">स्वचालित रूप से खोज के आधार पर भरा जाएगा</small>
                                    <small class="text-danger" id="deceased_dob_error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deceased_age" class="required-field">मृत्यु के समय आयु (वर्ष)</label>
                                    <input type="number" class="form-control" id="deceased_age" name="deceased_age" required min="18" max="60" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                    <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i> यह आयु स्वचालित रूप से जन्म तिथि और मृत्यु तिथि से गणना की जाती है</small>
                                    <small class="text-danger" id="deceased_age_error"></small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="death_date" class="required-field">मृत्यु की तारीख</label>
                                    <input type="date" class="form-control" id="death_date" name="death_date" required>
                                    <small class="text-danger" id="death_date_error"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deceased_relationship" class="required-field">आवेदक से संबंध</label>
                                    <select class="form-control" id="deceased_relationship" name="deceased_relationship" required>
                                        <option value="">-- चुनें --</option>
                                        <option value="पति">पति</option>
                                        <option value="पत्नी">पत्नी</option>
                                        <option value="बेटा">बेटा</option>
                                        <option value="बेटी">बेटी</option>
                                        <option value="माता">माता</option>
                                        <option value="पिता">पिता</option>
                                        <option value="भाई">भाई</option>
                                        <option value="बहन">बहन</option>
                                        <option value="अन्य">अन्य</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cause_of_death" class="required-field">मृत्यु का कारण</label>
                            <textarea class="form-control" id="cause_of_death" name="cause_of_death" rows="3" required></textarea>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('applicant-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('family-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 4: Family and Financial Details -->
                    <div class="tab-pane fade" id="family" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-home"></i> परिवार की आर्थिक स्थिति
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

                        <div class="form-group">
                            <label for="remarks">कोई अन्य जानकारी (वैकल्पिक)</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="4" placeholder="यदि कोई और जानकारी जोड़नी है तो यहाँ लिखें..."></textarea>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('deceased-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('bank-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 5: Bank Details -->
                    <div class="tab-pane fade" id="bank" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-university"></i> बैंक विवरण
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ifsc_code" class="required-field">IFSC कोड</label>
                                    <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" required placeholder="Ex: SBIN0001234">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bank_name" class="required-field">बैंक का नाम</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="branch_name" class="required-field">शाखा का नाम</label>
                                    <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="account_number" class="required-field">खाता संख्या</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" required>
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
                            <button type="button" class="btn-next" onclick="showTab('documents-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 6: Documents Upload -->
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <h5 class="section-title">
                            <i class="fas fa-file-upload"></i> आवश्यक दस्तावेज़
                        </h5>

                        <div class="alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>नोट:</strong> कृपया निम्नलिखित दस्तावेज़ अपलोड करें (PDF/JPG, अधिकतम 5MB)
                        </div>

                        <div class="mb-4">
                            <label for="deceased_aadhar" class="form-label required-field">मृत का आधार कार्ड</label>
                            <div class="file-upload-box" onclick="document.getElementById('deceased_aadhar').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="deceased_aadhar" name="deceased_aadhar" accept="image/*,.pdf" required>
                            <small class="text-danger" id="deceased_aadhar_error"></small>
                            <p class="mt-2" id="deceased_aadhar_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="mb-4">
                            <label for="death_certificate" class="form-label required-field">मृत्यु प्रमाण पत्र (Death Certificate)</label>
                            <div class="file-upload-box" onclick="document.getElementById('death_certificate').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="death_certificate" name="death_certificate" accept="image/*,.pdf" required>
                            <small class="text-danger" id="death_certificate_error"></small>
                            <p class="mt-2" id="death_certificate_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                        </div>

                        <div class="mb-4">
                            <label for="post_mortem_report" class="form-label">पोस्टमार्टम रिपोर्ट (वैकल्पिक)</label>
                            <div class="file-upload-box" onclick="document.getElementById('post_mortem_report').click()">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; color: var(--accent-color); margin-bottom: 10px; display: block;"></i>
                                <p><strong>फ़ाइल चुनने के लिए यहाँ क्लिक करें</strong></p>
                                <small class="text-muted">या यहाँ ड्रैग करें</small>
                            </div>
                            <input type="file" class="form-control d-none" id="post_mortem_report" name="post_mortem_report" accept="image/*,.pdf">
                            <small class="text-danger" id="post_mortem_report_error"></small>
                            <p class="mt-2" id="post_mortem_report_name" style="display:none;"><i class="fas fa-check text-success me-2"></i><span></span></p>
                            <small class="text-muted d-block mt-2">यह दस्तावेज़ वैकल्पिक है लेकिन आवेदन को मजबूत करने में सहायक हो सकता है।</small>
                        </div>

                        <div class="btn-group-tabs">
                            <button type="button" class="btn-previous" onclick="showTab('bank-tab')">
                                <i class="fas fa-arrow-left me-2"></i> पिछला
                            </button>
                            <button type="button" class="btn-next" onclick="showTab('review-tab')">
                                अगला <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab 7: Review and Submit -->
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
                                <i class="fas fa-user-tie me-2"></i>आवेदक विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">आवेदक का नाम</div>
                                        <div class="preview-item-value" id="preview_applicant_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">आवेदक की जन्म तारीख</div>
                                        <div class="preview-item-value" id="preview_applicant_dob">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">संबंध</div>
                                        <div class="preview-item-value" id="preview_applicant_relation">-</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 10px;"> 
                                <div class="col-md-12">
                                    <div class="preview-item">
                                        <div class="preview-item-label">अभिभावक का नाम</div>
                                        <div class="preview-item-value" id="preview_applicant_parent_name">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-users me-2"></i>मृत व्यक्ति विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">मृत व्यक्ति का नाम</div>
                                        <div class="preview-item-value" id="preview_deceased_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">संबंध</div>
                                        <div class="preview-item-value" id="preview_deceased_relationship">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="preview-item">
                                        <div class="preview-item-label">मृत्यु की तारीख</div>
                                        <div class="preview-item-value" id="preview_death_date">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-home me-2"></i>आर्थिक जानकारी
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">वार्षिक पारिवारिक आय</div>
                                        <div class="preview-item-value" id="preview_family_income">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">परिवार के सदस्य</div>
                                        <div class="preview-item-value" id="preview_family_members">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h6 style="color: var(--primary-color); font-weight: 700; margin-bottom: 15px;">
                                <i class="fas fa-university me-2"></i>बैंक विवरण
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">बैंक का नाम</div>
                                        <div class="preview-item-value" id="preview_bank_name">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="preview-item">
                                        <div class="preview-item-label">खाता धारक का नाम</div>
                                        <div class="preview-item-value" id="preview_account_holder_name">-</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-12">
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

    <!-- Validation Error Modal -->
    <div class="modal fade" id="validationErrorModal" tabindex="-1" aria-labelledby="validationErrorTitle" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" style="z-index: 9999;">
            <div class="modal-content" style="border: 2px solid #dc3545; position: relative; z-index: 10000;">
                <div class="modal-header" style="background: #dc3545; color: white;">
                    <h5 class="modal-title" id="validationErrorTitle">
                        <i class="fas fa-exclamation-triangle me-2"></i>आवेदन में त्रुटियाँ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 15px; color: #333;"><strong>कृपया निम्नलिखित त्रुटियों को ठीक करें:</strong></p>
                    <div id="validationErrorList" style="border: 1px solid #ffe8e8; background: #fff5f5; padding: 15px; border-radius: 6px; max-height: 400px; overflow-y: auto;">
                        <!-- Errors will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-2"></i>फॉर्म ठीक करने जाएँ
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .modal-backdrop {
            z-index: 9998 !important;
        }
        #validationErrorModal {
            z-index: 9999 !important;
        }
        #validationErrorModal .modal-dialog {
            z-index: 9999 !important;
        }
        #validationErrorModal .modal-content {
            z-index: 10000 !important;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass member_id to JavaScript
        const memberId = '<?php echo isset($member_id) ? $member_id : ''; ?>';
    </script>
    <script src="../assets/js/death_aavedan.js"></script>
</body>
</html>
