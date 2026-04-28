<?php
/**
 * Enhanced Generate Receipt - Member Registration Receipt
 * Professional HTML receipt with ID card style download
 */

require_once '../includes/config.php';

// Get parameters from URL
$member_id = isset($_GET['member_id']) ? trim($_GET['member_id']) : '';
$utr = isset($_GET['utr']) ? trim($_GET['utr']) : '';
$format = isset($_GET['format']) ? trim($_GET['format']) : 'html';

if (empty($member_id)) {
    header('HTTP/1.0 400 Bad Request');
    die('Member ID required');
}

// Connect to database with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    $conn->set_charset("utf8mb4");
    
    // Get member details
    $stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ? LIMIT 1");
    $stmt->bind_param("s", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Member not found', 404);
    }
    
    $member = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    header("HTTP/1.0 404 Not Found");
    die($e->getMessage());
}

// Set headers for HTML
header('Content-Type: text/html; charset=utf-8');

// Helper functions
function formatDate($date, $format = 'd-m-Y') {
    return $date ? date($format, strtotime($date)) : '-';
}

// Generate QR Code data
$qr_data = json_encode([
    'member_id' => $member['member_id'],
    'name' => $member['full_name'],
    'date' => $member['created_at']
]);

?>
<!DOCTYPE html>
<html lang="hi" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BRCT भारत ट्रस्ट सदस्यता रसीद">
    <title>पंजीकरण रसीद - <?php echo htmlspecialchars($member['member_id']); ?></title>
    
    <!-- External Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            font-family: 'Segoe UI', 'Arial', 'Devanagari', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .receipt-wrapper {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        
        /* ID Card Style Receipt */
        .receipt-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            overflow: hidden;
            position: relative;
            animation: slideIn 0.5s ease-out;
            border: 1px solid rgba(255,255,255,0.8);
            aspect-ratio: 3/4; /* ID card proportion */
            max-width: 380px;
            margin: 0 auto;
            max-height: 520px;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Watermark Logo - Only logo has watermark effect */
        .watermark-logo {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 180px;
            height: 180px;
            background: url('../assets/images/logo/logo.png') no-repeat center;
            background-size: contain;
            opacity: 0.15;
            pointer-events: none;
            z-index: 1;
            filter: grayscale(100%);
        }
        
        .receipt-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
    
        }
        
        /* Decorative Header - ID Card Style */
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 12px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .receipt-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 0%);
        }
        
        .header-icon {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .receipt-header h1 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }
        
        .receipt-header p {
            font-size: 11px;
            opacity: 1;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 25px;
            font-size: 9px;
            font-weight: 600;
            margin-top: 5px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(5px);
        }
        
        /* Body Content */
        .receipt-body {
            padding: 12px 15px;
            flex: 1;
            background: white;
            overflow: hidden;
        }
        
        /* Info Grid - Compact for ID card */
        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px dashed rgba(102, 126, 234, 0.15);
        }
        
        .info-icon {
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            color: #667eea;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 10px;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-label {
            font-size: 9px;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .info-value {
            font-size: 11px;
            font-weight: 600;
            color: #333;
            text-align: right;
            opacity: 1 !important;
        }
        
        /* QR Code Section - Compact */
        .qr-section {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            border-radius: 10px;
            margin: 8px 0;
        }
        
        .qr-code {
            width: 55px;
            height: 55px;
            background: white;
            padding: 3px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            flex-shrink: 0;
        }
        
        .qr-text {
            font-size: 8px;
            color: #495057;
            line-height: 1.3;
        }
        
        .qr-text strong {
            color: #667eea;
            display: block;
            margin-bottom: 1px;
        }
        
        /* Payment Details - Compact */
        .payment-details {
            background: #f0f7ff;
            border-radius: 10px;
            padding: 8px 10px;
            margin: 8px 0;
            border-left: 3px solid #667eea;
        }
        
        .payment-title {
            font-size: 9px;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 9px;
            color: #495057;
        }
        
        .payment-row:last-child {
            margin-bottom: 0;
        }
        
        /* Notes Section - Minimal for ID card */
        .notes-section {
            background: #fff8e7;
            border-radius: 8px;
            padding: 8px 10px;
            margin: 8px 0;
        }
        
        .notes-title {
            font-size: 9px;
            font-weight: 600;
            color: #b85c00;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .notes-list {
            list-style: none;
            font-size: 8px;
            color: #664d03;
        }
        
        .notes-list li {
            margin-bottom: 2px;
            padding-left: 12px;
            position: relative;
            line-height: 1.2;
        }
        
        .notes-list li::before {
            content: '•';
            position: absolute;
            left: 4px;
            color: #b85c00;
        }
        
        /* Footer - ID Card Style */
        .receipt-footer {
            background: #f8f9fa;
            padding: 8px 12px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 7px;
            color: #868e96;
        }
        
        .footer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 7px;
        }
        
        .member-photo-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0 auto 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #333;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 10000;
        }
        
        .toast {
            background: white;
            border-radius: 8px;
            padding: 12px 18px;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideInRight 0.3s ease-out;
            font-size: 13px;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0.5;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast.success {
            border-left: 4px solid #28a745;
        }
        
        .toast.error {
            border-left: 4px solid #dc3545;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .receipt-card {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
                max-width: 100%;
                margin: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .watermark-logo {
                opacity: 0.15 !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            /* Ensure all text is fully opaque */
            .receipt-content,
            .receipt-body,
            .info-label,
            .info-value,
            .payment-row,
            .receipt-header p,
            .receipt-footer,
            .notes-title,
            .notes-list li,
            .receipt-header h1,
            .qr-text {
                color: black !important;
                text-shadow: none !important;
            }
        }
        
        /* PDF Download Specific - Ensures single page */
        
        /* Responsive Design */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .receipt-card {
                aspect-ratio: auto;
                height: auto;
                min-height: 500px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <div class="receipt-wrapper">
        <!-- Main Receipt Card - ID Card Style -->
        <div class="receipt-card" id="receipt">
            <!-- Watermark Logo - Enhanced -->
            <div class="watermark-logo"></div>
            
            <div class="receipt-content">
                <!-- Header -->
                <div class="receipt-header">
                    <div class="header-icon">
                        <i class="fas fa-circle" style="font-size: 6px; margin-right: 4px;"></i>
                    </div>
                    <h1>BRCT भारत ट्रस्ट</h1>
                    <p>सदस्यता पहचान पत्र</p>
                    <div class="status-badge">
    
                        <?php echo ($member['status'] == 1) ? 'सक्रिय' : 'सत्यापन प्रतीक्षा'; ?>
                    </div>
                </div>
                
                <!-- Member Photo Placeholder (Initials) -->
                <div style="text-align: center; margin-top: -15px;">
                    <div class="member-photo-placeholder">
                        <?php 
                        $name_parts = explode(' ', $member['full_name']);
                        $initials = '';
                        foreach($name_parts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                        }
                        echo substr($initials, 0, 2);
                        ?>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="receipt-body">
                    <!-- Member Information - ID Card Style -->
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">सदस्य ID</span>
                                <span class="info-value"><?php echo htmlspecialchars($member['member_id']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">नाम</span>
                                <span class="info-value"><?php echo htmlspecialchars($member['full_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">मोबाइल</span>
                                <span class="info-value"><?php echo htmlspecialchars($member['mobile_number']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">ईमेल</span>
                                <span class="info-value"><?php echo htmlspecialchars(substr($member['email'] ?? 'N/A', 0, 15)) . (strlen($member['email'] ?? '') > 15 ? '...' : ''); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">आधार (अंतिम 4)</span>
                                <span class="info-value">•••• <?php echo htmlspecialchars(substr($member['aadhar_number'], -4)); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR Code Section - Compact -->
                    <div class="qr-section">
                        <div class="qr-code" id="qrcode"></div>
                        <div class="qr-text">
                            <strong>स्कैन करें</strong>
                            सत्यापन के लिए QR कोड स्कैन करें
                        </div>
                    </div>
                    
                    <!-- Payment Details - Compact -->
                    <div class="payment-details">
                        <div class="payment-title">
                            <i class="fas fa-credit-card"></i> भुगतान
                        </div>
                        <div class="payment-row">
                            <span>राशि:</span>
                            <strong>₹ <?php echo number_format($member['payment_amount'] ?? 50, 2); ?></strong>
                        </div>
                        <?php if(!empty($utr)): ?>
                        <div class="payment-row">
                            <span>UTR:</span>
                            <strong><?php echo htmlspecialchars(substr($utr, -6)); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="payment-row">
                            <span>तारीख:</span>
                            <strong><?php echo formatDate($member['created_at']); ?></strong>
                        </div>
                    </div>
                    
                    <!-- Important Notes - Compact -->
                    <div class="notes-section">
                        <div class="notes-title">
                            <i class="fas fa-info-circle"></i> नोट
                        </div>
                        <ul class="notes-list">
                            <li>24-48 घंटे में सत्यापन</li>
                            <li>लॉगिन के बाद सेवाएं सक्रिय</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="receipt-footer">
                    <div class="footer-info">
                        <span>जारी तिथि: <?php echo formatDate($member['created_at']); ?></span>
                        <span>समय: <?php echo date('h:i A', strtotime($member['created_at'])); ?></span>
                    </div>
                    <div style="font-weight: 500; color: #667eea;">
                        BRCT भारत ट्रस्ट © 2026
                    </div>
                    <div style="margin-top: 3px;">
                        यह कंप्यूटर जनित पहचान पत्र है
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button class="btn btn-primary" onclick="downloadAsIDCardPDF()">
                <i class="fas fa-file-pdf"></i> ID कार्ड डाउनलोड
            </button>
            <button class="btn btn-success" onclick="downloadAsImage()">
                <i class="fas fa-image"></i> फोटो डाउनलोड
            </button>
            <button class="btn btn-warning" onclick="window.print()">
                <i class="fas fa-print"></i> प्रिंट करें
            </button>
        </div>
        
        <!-- Share Buttons -->
        <div class="action-buttons no-print" style="margin-top: 10px;">
            <button class="btn" style="background: #25D366; color: white;" onclick="shareViaWhatsApp()">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
        </div>
    </div>
    
    <script>
        // Initialize QR Code with proper error handling
        try {
            new QRCode(document.getElementById('qrcode'), {
                text: '<?php echo htmlspecialchars($qr_data); ?>',
                width: 65,
                height: 65,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error('QR Code generation failed:', e);
        }
        
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="color: ${type === 'success' ? '#28a745' : '#dc3545'};"></i>
                <span>${message}</span>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Download as ID Card PDF - Single page, ID card style
        async function downloadAsIDCardPDF() {
            showLoading();
            
            try {
                const element = document.getElementById('receipt');
                
                // Configure for ID card size (credit card proportions but larger)
                const opt = {
                    margin: 3,
                    filename: 'BRCT_ID_Card_<?php echo $member['member_id']; ?>.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 3, // Higher scale for better quality
                        backgroundColor: '#ffffff',
                        logging: false,
                        useCORS: true,
                        allowTaint: false,
                        removeContainer: true
                    },
                    jsPDF: { 
                        unit: 'mm',
                        format: [100, 140], // Optimized size to fit all content on one page
                        orientation: 'portrait',
                        compress: true
                    }
                };
                
                await html2pdf().set(opt).from(element).save();
                showToast('ID कार्ड सफलतापूर्वक डाउनलोड हुआ', 'success');
            } catch (error) {
                console.error('PDF generation failed:', error);
                showToast('डाउनलोड विफल हुआ, पुनः प्रयास करें', 'error');
            } finally {
                hideLoading();
            }
        }
        
        // Download as Image with watermark preserved
        async function downloadAsImage() {
            showLoading();
            
            try {
                const element = document.getElementById('receipt');
                const canvas = await html2canvas(element, {
                    scale: 3,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true
                });
                
                const link = document.createElement('a');
                link.download = 'BRCT_ID_Card_<?php echo $member['member_id']; ?>.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                showToast('फोटो सफलतापूर्वक डाउनलोड हुई', 'success');
            } catch (error) {
                console.error('Image generation failed:', error);
                showToast('डाउनलोड विफल हुआ', 'error');
            } finally {
                hideLoading();
            }
        }
        
        // Share via WhatsApp
        function shareViaWhatsApp() {
            const text = encodeURIComponent(
                '*BRCT भारत ट्रस्ट - सदस्यता ID कार्ड*\n\n' +
                'नाम: <?php echo $member['full_name']; ?>\n' +
                'सदस्य ID: <?php echo $member['member_id']; ?>\n' +
                'मोबाइल: <?php echo $member['mobile_number']; ?>\n' +
                'जारी तिथि: <?php echo formatDate($member['created_at']); ?>\n\n' +
                'ID कार्ड देखने के लिए लिंक पर क्लिक करें:\n' +
                window.location.href
            );
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }
        
        // Copy member ID to clipboard
        function copyMemberID() {
            const memberID = '<?php echo $member['member_id']; ?>';
            navigator.clipboard.writeText(memberID).then(() => {
                showToast('सदस्य ID कॉपी हो गई: ' + memberID, 'success');
            }).catch(() => {
                showToast('कॉपी करने में विफल', 'error');
            });
        }
        
        // Add click handler for member ID
        document.querySelector('.info-value').addEventListener('dblclick', copyMemberID);
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Ctrl+D for download
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                downloadAsIDCardPDF();
            }
        });
        
        // Auto download if format=pdf in URL
        <?php if ($format === 'pdf'): ?>
        window.onload = function() {
            setTimeout(() => {
                downloadAsIDCardPDF();
            }, 1000);
        };
        <?php endif; ?>
    </script>
</body>
</html>