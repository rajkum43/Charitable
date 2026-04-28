<?php
// Member Registration Page
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar (Non-sticky for register page) -->
    <?php $navbar_sticky = false; include '../components/navbar.php'; ?>

    <!-- Registration Section -->
    <section class="registration-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="registration-card w-100">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus text-primary" style="font-size: 2.5rem;"></i>
                            <h1 class="mt-3">BRCT Bharat Trust में शामिल हों</h1>
                            <p class="text-secondary">सभी विवरण भरकर आसानी से सदस्य बनें</p>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="registrationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab">
                                    <i class="fas fa-credit-card me-2"></i>भुगतान
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                    <i class="fas fa-user me-2"></i>व्यक्तिगत विवरण
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
                                <button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab">
                                    <i class="fas fa-lock me-2"></i>खाता & शर्तें
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <form id="registrationForm" method="POST" action="../api/register.php" enctype="multipart/form-data">
                            <div class="tab-content" id="registrationTabContent">
                                <!-- Tab 1: Payment -->
                                <div class="tab-pane fade show active" id="payment" role="tabpanel">
                                    <div class="payment-tab-content">
                                        <h5 class="mb-4"><i class="fas fa-rupee-sign text-primary me-2"></i>भुगतान विवरण</h5>
                                        
                                        <div class="alert alert-info mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>सदस्यता शुल्क:</strong> ₹50 (एकमुश्त)
                                        </div>

                                        <div class="payment-section">
                                            <p class="text-secondary mb-4 text-center"><i class="fas fa-rupee-sign text-primary me-2"></i><strong>सदस्यता शुल्क: ₹50 (एकमुश्त)</strong></p>
                                            
                                            <!-- Payment Methods Container -->
                                            <div class="payment-methods-container">
                                                <!-- UPI Method -->
                                                <div class="payment-method-box">
                                                    <div class="payment-method-title mb-3">
                                                        <i class="fas fa-mobile-alt text-primary me-2"></i>
                                                        <strong>UPI के माध्यम से भुगतान करें</strong>
                                                    </div>
                                                    <p class="text-secondary small mb-3">नीचे दिए गए QR कोड को स्कैन करके भुगतान करें:</p>
                                                    
                                                    <div class="qr-code-container mb-3 text-center">
                                                        <a href="upi://pay?pa=satynarayan9936@axl&pn=BRCT%20Bharat%20Trust&am=50&tn=Membership%20Fee&cu=INR" style="display: inline-block; text-decoration: none;">
                                                            <img id="upiQrCode" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi%3A%2F%2Fpay%3Fpa%3Dsatynarayan9936%40axl%26pn%3DBRCT%2520Bharat%2520Trust%26am%3D50%26tn%3DMembership%2520Fee%26cu%3DINR" alt="UPI QR Code" style="width: 180px; height: 180px; border: 2px solid #0d6efd; border-radius: 10px; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        </a>
                                                        <p class="text-muted small mt-2">QR कोड को क्लिक करके सीधे भुगतान करें</p>
                                                    </div>

                                                    <div class="upi-section mb-3">
                                                        <p class="text-secondary small mb-2"><strong>UPI ID:</strong></p>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" value="satynarayan9936@axl" readonly style="cursor: copy;">
                                                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('satynarayan9936@axl')">
                                                                <i class="fas fa-copy me-1"></i>कॉपी करें
                                                            </button>
                                                        </div>
                                                        <div class="mt-2">
                                                            <a href="upi://pay?pa=satynarayan9936@axl&pn=BRCT%20Bharat%20Trust&am=50&tn=Membership%20Fee&cu=INR" class="btn btn-primary btn-sm w-100">
                                                                <i class="fas fa-mobile-alt me-2"></i>UPI App में भुगतान करें
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Bank Transfer Method -->
                                                <div class="payment-method-box">
                                                    <div class="payment-method-title mb-3">
                                                        <i class="fas fa-university text-primary me-2"></i>
                                                        <strong>बैंक खाते में सीधा भुगतान करें</strong>
                                                    </div>
                                                    
                                                    <!-- Bank Details -->
                                                    <div class="bank-details">
                                                        <div class="bank-detail-item mb-3">
                                                            <label class="text-secondary small"><strong>बैंक का नाम:</strong></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="STATE BANK OF INDIA" readonly>
                                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('STATE BANK OF INDIA')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="bank-detail-item mb-3">
                                                            <label class="text-secondary small"><strong>खाता धारक का नाम:</strong></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="SATYANAEAYAN" readonly>
                                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('SATYANAEAYAN')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="bank-detail-item mb-3">
                                                            <label class="text-secondary small"><strong>खाता संख्या:</strong></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="33570681358" readonly>
                                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('33570681358')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="bank-detail-item mb-3">
                                                            <label class="text-secondary small"><strong>IFSC कोड:</strong></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" value="SBIN0016948" readonly>
                                                                <button class="btn btn-outline-primary btn-sm" type="button" onclick="copyToClipboard('SBIN0016948')">
                                                                    <i class="fas fa-copy"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="alert alert-info border-0 bg-light p-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                भुगतान करते समय Reference में "Membership Fee ₹50" लिखें
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- UTR Number Entry -->
                                            <div class="utr-section mt-4 p-3 bg-light rounded-3 border border-info">
                                                <h6 class="mb-3"><i class="fas fa-receipt text-info me-2"></i>भुगतान की पुष्टि</h6>
                                                <div class="mb-3">
                                                    <label for="utrNumber" class="form-label"><strong>UTR नंबर दर्ज करें <span class="text-danger">*</span></strong></label>
                                                    <input type="text" class="form-control" id="utrNumber" name="utrNumber" placeholder="अपना UTR/Transaction ID दर्ज करें (जैसे - 202500001234567)" required>
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        UTR नंबर आपके बैंक की मेसेज या बैंक अकाउंट विवरण में मिलेगा। यह UPI और बैंक ट्रांसफर दोनों में दिया जाता है।
                                                    </small>
                                                    <small class="text-danger d-block" id="utrNumberError"></small>
                                                </div>
                                            </div>

                                            <!-- Payment Confirmation -->
                                            <div class="payment-status mt-4 p-3 border rounded-3">
                                                <label class="form-check-label mb-2">
                                                    <input type="checkbox" class="form-check-input" id="paymentConfirm" name="paymentConfirm">
                                                    <strong>मैं ₹50 का भुगतान कर चुका/चुकी हूँ</strong>
                                                </label>
                                                <small class="text-danger d-block" id="paymentConfirmError"></small>
                                            </div>

                                            <div class="text-muted small mt-3 text-center">
                                                <p><i class="fas fa-check-circle text-success me-2"></i>भुगतान के बाद "अगला" बटन दबाएं</p>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <button type="button" class="btn btn-primary btn-lg" onclick="nextTab('personal-tab')">
                                                अगला <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Personal Details -->
                                <div class="tab-pane fade" id="personal" role="tabpanel">
                                    <h5 class="mb-4"><i class="fas fa-user-circle text-primary me-2"></i>व्यक्तिगत विवरण</h5>

                                    <!-- Member ID Display -->
                                    <div class="alert alert-info mb-4" style="background: linear-gradient(135deg, #d1ecf1 0%, #e6f9fb 100%); border: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><small><strong>आपकी Member ID:</strong></small></p>
                                                <h6 class="text-primary" id="loginIdDisplay" style="font-family: 'Courier New', monospace;">आधार के अंतिम 8 अंक</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><small><strong>आपकी Login ID:</strong></small></p>
                                                <h6 class="text-primary" style="font-family: 'Courier New', monospace;" id="loginIdDisplay2">आधार के अंतिम 8 अंक</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fullName" class="form-label">पूरा नाम <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="fullName" name="fullName">
                                        <small class="text-danger" id="fullNameError"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="aadharNumber" class="form-label">आधार कार्ड नंबर (12 अंक) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="aadharNumber" name="aadharNumber" placeholder="XXXX XXXX XXXX">
                                        <small class="text-muted">Member ID और Login ID आधार के अंतिम 8 अंकों से बनेगी</small>
                                        <small class="text-danger" id="aadharNumberError"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fatherName" class="form-label">पिता/पति का नाम <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="fatherName" name="fatherName">
                                        <small class="text-danger" id="fatherNameError"></small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="dob" class="form-label">जन्म तिथि <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="dob" name="dob">
                                                <small class="text-danger" id="dobError"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="mobile" class="form-label">मोबाइल नंबर <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="mobile" name="mobile" maxlength="10">
                                                <small class="text-danger" id="mobileError"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="button" class="btn btn-primary btn-lg" onclick="nextTab('additional-tab')">
                                            अगला <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevTab('payment-tab')">
                                            <i class="fas fa-arrow-left me-2"></i>पिछला
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab 3: Additional Details -->
                                <div class="tab-pane fade" id="additional" role="tabpanel">
                                    <h5 class="mb-4"><i class="fas fa-briefcase text-primary me-2"></i>अतिरिक्त विवरण</h5>

                                    <div class="mb-3">
                                        <label class="form-label">लिंग <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="gender" id="male" value="पुरुष">
                                            <label class="btn btn-outline-secondary btn-sm me-2" for="male">
                                                <i class="fas fa-mars me-2"></i>पुरुष
                                            </label>
                                            <input type="radio" class="btn-check" name="gender" id="female" value="महिला">
                                            <label class="btn btn-outline-primary" for="female">
                                                <i class="fas fa-venus me-2"></i>महिला
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="occupation" class="form-label">व्यवसाय <span class="text-danger">*</span></label>
                                        <select class="form-select" id="occupation" name="occupation">
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
                                        <small class="text-danger" id="occupationError"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="officeName" class="form-label">कार्यरत कार्यालय नाम</label>
                                        <input type="text" class="form-control" id="officeName" name="office_name">
                                    </div>

                                    <div class="mb-3">
                                        <label for="officeAddress" class="form-label">कार्यालय पता</label>
                                        <textarea class="form-control" id="officeAddress" name="office_address" rows="2" placeholder="अपने कार्यालय का पूरा पता दर्ज करें"></textarea>
                                    </div>

                                    <!-- Nominee Section -->
                                    <div class="alert alert-warning mb-4 p-3">
                                        <i class="fas fa-user-tie text-warning me-2"></i>
                                        <strong>नॉमिनी व्यक्ति की जानकारी</strong>
                                        <p class="mb-0 mt-2"><small>आपकी मृत्यु के मामले में आर्थिक सहायता प्राप्त करने वाला व्यक्ति</small></p>
                                    </div>

                                    <div class="mb-3">
                                        <label for="nomineeName" class="form-label">नॉमिनी व्यक्ति का नाम <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nomineeName" name="nomineeName" placeholder="???????? ??????? ?? ???? ??? ???? ????">
                                        <small class="text-danger" id="nomineeNameError"></small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nomineeRelation" class="form-label">संबंध <span class="text-danger">*</span></label>
                                                <select class="form-select" id="nomineeRelation" name="nomineeRelation">
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
                                                <small class="text-danger" id="nomineeRelationError"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nomineeMobile" class="form-label">नॉमिनी का मोबाइल नंबर (वैकल्पिक)</label>
                                                <input type="tel" class="form-control" id="nomineeMobile" name="nomineeMobile" maxlength="10" placeholder="10 ????? ????"> 
                                                <small class="text-danger" id="nomineeMobileError"></small>
                                            </div>
                                        </div>
                                    </div>

                                  <div class="mb-3">
    <label for="nomineeAadhar" class="form-label">नॉमिनी का आधार नंबर (वैकल्पिक)</label>
    <input type="text" class="form-control" id="nomineeAadhar" name="nomineeAadhar"
        placeholder="XXXX XXXX XXXX" maxlength="12"
        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,12);">
    <small class="text-danger" id="nomineeAadharError"></small>
</div>

                                    <div class="d-grid gap-2 mt-4">
                                         <button type="button" class="btn btn-primary btn-lg" onclick="nextTab('location-tab')">
                                            अगला <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevTab('personal-tab')">
                                            <i class="fas fa-arrow-left me-2"></i>पिछला
                                        </button>
                                       
                                    </div>
                                </div>

                                <!-- Tab 4: Location Details -->
                                <div class="tab-pane fade" id="location" role="tabpanel">
                                    <h5 class="mb-4"><i class="fas fa-map-marker-alt text-primary me-2"></i>स्थान विवरण</h5>
                                    
                                    <!-- State Selection -->
                                    <div class="mb-3">
                                        <label for="state" class="form-label">राज्य <span class="text-danger">*</span></label>
                                        <select class="form-select" id="state" name="state" onchange="handleStateChange()">
                                            <option value="">-- चुनें --</option>
                                            <option value="उत्तर प्रदेश">उत्तर प्रदेश</option>
                                            <option value="अन्य">अन्य</option>
                                        </select>
                                        <small class="text-danger" id="stateError"></small>
                                    </div>

                                    <!-- Manual State Entry -->
                                    <div class="mb-3" id="manualState" style="display: none;">
                                        <label for="manualStateName" class="form-label">राज्य का नाम (मैनुअल दर्ज करें)</label>
                                        <input type="text" class="form-control" id="manualStateName" name="manualStateName" placeholder="अपना राज्य दर्ज करें">
                                    </div>

                                    <div class="row">
                                        <!-- District Section -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label for="district" class="form-label">जिला <span class="text-danger">*</span></label>
                                                    <small class="text-muted">
                                                        <a href="javascript:void(0)" onclick="toggleManualDistrict()" class="text-decoration-none">
                                                            <i class="fas fa-edit me-1"></i>मैनुअल दर्ज करें?
                                                        </a>
                                                    </small>
                                                </div>
                                                <select class="form-select" id="district" name="district" onchange="loadBlocks()">
                                                    <option value="">-- जिला चुनें --</option>
                                                </select>
                                                <small class="text-danger" id="districtError"></small>
                                            </div>

                                            <!-- Manual District Entry -->
                                            <div class="mb-3" id="manualDistrictField" style="display: none;">
                                                <label for="manualDistrict" class="form-label">जिला दर्ज करें</label>
                                                <input type="text" class="form-control" id="manualDistrict" name="manualDistrict" placeholder="अपना जिला दर्ज करें">
                                                <small class="text-muted">ड्रॉपडाउन से चुनने के लिए ऊपर क्लिक करें</small>
                                            </div>
                                        </div>

                                        <!-- Block Section -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label for="block" class="form-label">ब्लॉक <span class="text-danger">*</span></label>
                                                    <small class="text-muted">
                                                        <a href="javascript:void(0)" onclick="toggleManualBlock()" class="text-decoration-none">
                                                            <i class="fas fa-edit me-1"></i>मैनुअल दर्ज करें?
                                                        </a>
                                                    </small>
                                                </div>
                                                <select class="form-select" id="block" name="block">
                                                    <option value="">-- ब्लॉक चुनें --</option>
                                                </select>
                                                <small class="text-danger" id="blockError"></small>
                                            </div>

                                            <!-- Manual Block Entry -->
                                            <div class="mb-3" id="manualBlockField" style="display: none;">
                                                <label for="manualBlock" class="form-label">ब्लॉक दर्ज करें</label>
                                                <input type="text" class="form-control" id="manualBlock" name="manualBlock" placeholder="अपना ब्लॉक दर्ज करें">
                                                <small class="text-muted">ड्रॉपडाउन से चुनने के लिए ऊपर क्लिक करें</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="permanentAddress" class="form-label">स्थाई पता <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="permanentAddress" name="permanentAddress" rows="3" placeholder="???? ???? ????? ??? ???? ????"></textarea>
                                        <small class="text-danger" id="permanentAddressError"></small>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="button" class="btn btn-primary btn-lg" onclick="nextTab('account-tab')">
                                            अगला <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevTab('additional-tab')">
                                            <i class="fas fa-arrow-left me-2"></i>पिछला
                                        </button>
                                    </div>
                                </div>

                                <!-- Tab 5: Account & Terms -->
                                <div class="tab-pane fade" id="account" role="tabpanel">
                                    <h5 class="mb-4"><i class="fas fa-lock text-primary me-2"></i>खाता & शर्तें</h5>

                                    <div class="mb-3">
                                        <label for="referrerMemberId" class="form-label">किसके द्वारा रेफर किए गए? (Member ID) - वैकल्पिक</label>
                                        <input type="text" class="form-control" id="referrerMemberId" name="referrerMemberId" placeholder="रेफर करने वाले सदस्य की ID दर्ज करें (यदि कोई हो)">
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            यदि किसी मौजूदा सदस्य की सिफारिश पर रजिस्ट्रेशन कर रहे हैं, तो उनकी Member ID दर्ज करें
                                        </small>
                                        <small class="text-danger" id="referrerMemberIdError"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">ईमेल (वैकल्पिक)</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="आपका ईमेल पता दर्ज करें">
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">पासवर्ड बनाएं <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="?? ?? ?? 6 ????">
                                        <small class="text-muted">कम से कम 6 वर्ण होने चाहिए</small>
                                        <small class="text-danger" id="passwordError"></small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms" name="terms">
                                            <label class="form-check-label" for="terms">
                                                <small>मैं सभी शर्तों और नियमों से सहमत हूं। यदि मैं BRCT Bharat Trust के नियमों के अनुसार नियमित योगदान नहीं दूंगा, तो मेरे नॉमिनी व्यक्ति को आर्थिक सहायता का दावा करने का अधिकार नहीं होगा।</small>
                                            </label>
                                        </div>
                                        <small class="text-danger" id="termsError"></small>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                         <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                            <i class="fas fa-user-check me-2"></i>रजिस्टर करें
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="prevTab('location-tab')">
                                            <i class="fas fa-arrow-left me-2"></i>पिछला
                                        </button>
                                       
                                    </div>
                                </div>
                            </div>
                        </form>

                        <p class="text-center mt-4 text-secondary">
                            पहले से सदस्य हैं? 
                            <a href="login.php" class="text-primary text-decoration-none fw-bold">लॉगिन करें</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Alert for messages -->
    <div class="position-fixed top-0 end-0 p-3" id="alertContainer" style="z-index: 1050;"></div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/register.js"></script>
    
    <script>
    // Handle State Change
    function handleStateChange() {
        const state = document.getElementById('state').value;
        const manualStateField = document.getElementById('manualState');
        
        if (state === 'अन्य') {
            manualStateField.style.display = 'block';
        } else {
            manualStateField.style.display = 'none';
            document.getElementById('manualStateName').value = '';
        }
        
        // Load districts when state changes
        if (state === 'उत्तर प्रदेश') {
            loadDistricts();
        } else {
            // Clear districts for other states
            const districtSelect = document.getElementById('district');
            districtSelect.innerHTML = '<option value="">-- जिला चुनें --</option>';
        }
    }

    // Toggle Manual District Entry
    function toggleManualDistrict() {
        const manualField = document.getElementById('manualDistrictField');
        const dropdown = document.getElementById('district');
        
        if (manualField.style.display === 'none') {
            manualField.style.display = 'block';
            dropdown.value = '';
            dropdown.disabled = true;
        } else {
            manualField.style.display = 'none';
            document.getElementById('manualDistrict').value = '';
            dropdown.disabled = false;
        }
    }

    // Toggle Manual Block Entry
    function toggleManualBlock() {
        const manualField = document.getElementById('manualBlockField');
        const dropdown = document.getElementById('block');
        
        if (manualField.style.display === 'none') {
            manualField.style.display = 'block';
            dropdown.value = '';
            dropdown.disabled = true;
        } else {
            manualField.style.display = 'none';
            document.getElementById('manualBlock').value = '';
            dropdown.disabled = false;
        }
    }

    // Load districts from database on page load
    function loadDistricts() {
        fetch('../api/get_districts.php')
            .then(response => response.json())
            .then(data => {
                const districtSelect = document.getElementById('district');
                districtSelect.innerHTML = '<option value="">-- जिला चुनें --</option>';
                
                if (data.success) {
                    data.districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.district;
                        option.textContent = district.district;
                        districtSelect.appendChild(option);
                    });
                } else {
                    console.error('Error loading districts:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Load blocks based on selected district
    function loadBlocks() {
        const district = document.getElementById('district').value;
        const blockSelect = document.getElementById('block');
        
        if (!district) {
            blockSelect.innerHTML = '<option value="">-- ब्लॉक चुनें --</option>';
            return;
        }

        fetch('../api/get_blocks.php?district=' + encodeURIComponent(district))
            .then(response => response.json())
            .then(data => {
                blockSelect.innerHTML = '<option value="">-- ब्लॉक चुनें --</option>';
                
                if (data.success) {
                    data.blocks.forEach(block => {
                        const option = document.createElement('option');
                        option.value = block.block;
                        option.textContent = block.block;
                        blockSelect.appendChild(option);
                    });
                } else {
                    console.error('Error loading blocks:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Load districts when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadDistricts();
    });
    </script>
</body>
</html>

