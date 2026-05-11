<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>प्रीमियम सदस्य आईडी कार्ड | Bharat Relief</title>

<?php
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    $root_path = '/Charitable/';
} else {
    $root_path = '/';
}
// अब $root_path का उपयोग करके लिंक बनाएं
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" href="<?php echo $root_path; ?>assets/images/favicon.png">
<link href="<?php echo $root_path; ?>member/assets/css/id_card.css" rel="stylesheet">
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</head>
<body>

<div class="container-fluid px-2">
    <h2 class="text-center my-3 fw-bold" style="color: #0a4d8c; text-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        🌟 प्रीमियम सदस्य आईडी कार्ड
    </h2>

    <div class="controls">
        <div class="mb-4">
            <label class="form-label d-flex align-items-center gap-2">
                <span>📸 सदस्य की फोटो अपलोड करें</span>
                <span class="badge bg-info rounded-pill">वैकल्पिक</span>
            </label>
            <input type="file" class="form-control" id="memberImage" accept="image/*" style="cursor: pointer;">
            <div class="form-text text-muted mt-1">PNG या JPG, बेहतर परिणाम के लिए स्क्वायर फोटो चुनें</div>
        </div>

        <button id="downloadBtn" class="btn btn-primary w-100 py-2" disabled>
            ⬇️ आईडी कार्ड डाउनलोड करें (HD JPG)
        </button>
        <div class="text-center mt-3 small text-secondary">✨ उच्च-रिज़ॉल्यूशन में कार्ड सेव करें</div>
    </div>

    <div id="loading" class="loading text-center">
        <div class="spinner-border text-primary" style="width: 2.8rem; height: 2.8rem;"></div>
        <p class="mt-3 fw-semibold">डेटा लोड हो रहा है... कृपया प्रतीक्षा करें</p>
    </div>

    <div id="idCardContainer" style="display:none;">
        <div class="id-card" id="idCard">
            <!-- Decorative corners -->
            <div class="corner corner-tl"></div>
            <div class="corner corner-tr"></div>
            <div class="corner corner-bl"></div>
            <div class="corner corner-br"></div>
            <div class="top-strip"></div>
            <div class="badge-seal"></div>

            <div class="header">
                <div class="trust-name">BHARAT RELIEF CHARITABLE TRUST</div>
                <div class="card-title">✦ सदस्य पहचान पत्र | MEMBERSHIP CARD ✦</div>
            </div>

            <div class="card-body">
                <div class="photo-box">
                    <div class="photo-frame">
                        <img src="" id="memberPhoto" class="member-photo" alt="Member Photo">
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-row">
                        <span class="label">🆔 सदस्य ID :</span>
                        <span class="value" id="memberId">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">👤 पूरा नाम :</span>
                        <span class="value" id="memberName">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">👪 पिता/पति :</span>
                        <span class="value" id="fatherHusband">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">⚥ लिंग :</span>
                        <span class="value" id="gender">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">📞 मोबाइल :</span>
                        <span class="value" id="mobile">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">📅 ज्वाइन तिथि :</span>
                        <span class="value" id="joiningDate">-</span>
                    </div> 
                </div>
            </div>
            <div class="leftside">
                        <span class="label">🏠 स्थायी पता :</span>
                        <span id="address" class="address">-</span>
                    </div>
            <div class="footer">
                🔹 यह कार्ड Bharat Relief Charitable Trust द्वारा जारी है • वैधता अनिश्चितकालीन 🔹
            </div>
        </div>
        <!-- extra info: hologram feel -->
        <div style="text-align: center; margin-top: 6px; font-size: 11px; color: #2c6e9e; opacity: 0.7;">✅ सत्यापित सदस्य | Verified Member</div>
    </div>
</div>

<script src="assets/js/member-common.js"></script>
<script src="<?php echo $root_path; ?>member/assets/js/id_card.js"></script>
</body>
</html>