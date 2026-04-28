<?php
/**
 * Generate Receipt - Member Registration Receipt
 * Simple HTML receipt that can be printed or saved as PDF
 */

require_once '../includes/config.php';

// Get parameters from URL
$member_id = isset($_GET['member_id']) ? trim($_GET['member_id']) : '';
$utr = isset($_GET['utr']) ? trim($_GET['utr']) : '';

if (empty($member_id)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Member ID required';
    exit;
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Database connection failed';
    exit;
}

$conn->set_charset("utf8mb4");

// Get member details
$stmt = $conn->prepare("SELECT member_id, full_name, gender, mobile_number, email, aadhar_number, date_of_birth, permanent_address, district, block, state, created_at FROM members WHERE member_id = ? LIMIT 1");
$stmt->bind_param("s", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.0 404 Not Found');
    echo 'Member not found';
    exit;
}

$member = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Set headers
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पंजीकरण रसीद - BRCT भारत ट्रस्ट</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            font-size: 16px;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 10px;
            margin: 0;
        }
        
        .id-card-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 15px;
            border: 2px dashed #100bdd;
            border-radius: 3px;
            position: relative;
            overflow: hidden;
        }
        
        /* Logo Watermark */
        .id-card-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: url('../assets/images/logo/logo.png') no-repeat center;
            background-size: contain;
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }
        
        .receipt-content {
            position: relative;
            z-index: 1;
            margin: 0;
            border: 2px ridge #e04b4b;
            padding: 12px 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #dc143c;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        
        .header p {
            font-size: 11px;
            color: #666;
            display: none;
        }
        
        .field {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
            font-size: 11px;
            padding: 5px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: flex-start;
        }
        
        .field-label {
            font-weight: 700;
            color: #000;
            display: inline-block;
            min-width: 100px;
            flex-shrink: 0;
        }
        
        .field-value {
            color: #333;
            font-weight: 400;
            word-break: break-word;
            flex: 1;
            min-width: 0;
        }
        
        .note-section {
            display: none;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e0e0e0;
            display: none;
        }
        
        .action-buttons {
            text-align: center;
            margin-top: 12px;
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 10px;
            font-size: 10px;
            border: 1px solid #dc143c;
            border-radius: 2px;
            cursor: pointer;
            font-weight: 600;
            display: inline-block;
            min-width: auto;
        }
        
        .btn-download {
            background: #dc143c;
            color: white;
        }
        
        .btn-download:hover {
            background: #b22222;
        }
        
        .btn-image {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .btn-image:hover {
            background: #45a049;
        }
        
        .btn-print {
            background: white;
            color: #dc143c;
        }
        
        .btn-print:hover {
            background: #f0f0f0;
        }
        
        /* Mobile Responsive - Small phones (max 480px) */
        @media (max-width: 480px) {
            body {
                padding: 5px;
                background: #fff;
            }
            
            .id-card-container {
                padding: 10px;
                border: 1px dashed #dc143c;
                margin: 0;
            }
            
            .receipt-content {
                padding: 10px 8px;
                border-width: 1px;
            }
            
            .header h1 {
                font-size: 14px;
                margin-bottom: 2px;
            }
            
            .header {
                margin-bottom: 12px;
            }
            
            .field {
                margin-bottom: 8px;
                padding: 4px 0;
            }
            
            .field-label {
                font-size: 10px;
                min-width: 90px;
            }
            
            .field-value {
                font-size: 10px;
            }
            
            .action-buttons {
                gap: 6px;
                margin-top: 10px;
            }
            
            .btn {
                padding: 7px 8px;
                font-size: 9px;
            }
        }
        
        /* Medium devices (481px - 768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            .id-card-container {
                padding: 15px;
            }
            
            .receipt-content {
                padding: 12px;
            }
            
            .header h1 {
                font-size: 15px;
            }
            
            .field-label {
                font-size: 11px;
                min-width: 100px;
            }
            
            .field-value {
                font-size: 11px;
            }
        }
        
        /* Tablets and larger screens (768px and up) */
        @media (min-width: 769px) {
            body {
                padding: 15px;
            }
            
            .id-card-container {
                padding: 20px;
                border-width: 3px;
            }
            
            .receipt-content {
                padding: 15px;
                border-width: 3px;
            }
            
            .header h1 {
                font-size: 18px;
                margin-bottom: 5px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            .field {
                font-size: 12px;
                margin-bottom: 12px;
                padding: 6px 0;
            }
            
            .field-label {
                font-size: 12px;
                min-width: 120px;
            }
            
            .field-value {
                font-size: 12px;
            }
            
            .btn {
                padding: 10px 15px;
                font-size: 11px;
            }
        }
        
        @media print {
            * {
                margin: 0;
                padding: 0;
            }
            
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .id-card-container {
                max-width: 100%;
                width: 100%;
                padding: 15mm;
                margin: 0;
                border: 2px dashed #dc143c;
                page-break-after: avoid;
                box-shadow: none;
            }
            
            .receipt-content {
                margin: 0;
                padding: 10mm;
                border: 2px ridge #f5a906;
            }
            
            .header h1 {
                font-size: 14pt;
            }
            
            .field {
                font-size: 10pt;
                margin-bottom: 8px;
                padding: 4px 0;
                page-break-inside: avoid;
            }
            
            .field-label {
                font-size: 10pt;
                min-width: 100px;
            }
            
            .field-value {
                font-size: 10pt;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div id="receipt" class="id-card-container">
        <div class="receipt-content">
            <div class="header">
                <h1> BHARAT RELIEF CHARITABLE TEAM</h1>
            </div>
            
            <div class="field">
                <span class="field-label">Name :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['full_name']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Gender :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['gender']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Mobile :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['mobile_number']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Email :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['email']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Unique ID :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['member_id']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">DOB :</span>
                <span class="field-value"><?php echo !empty($member['date_of_birth']) ? date('d-m-Y', strtotime($member['date_of_birth'])) : 'N/A'; ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Address :</span>
                <span class="field-value">
                    <?php 
                    $addressParts = array_filter([
                        htmlspecialchars($member['permanent_address']),
                        htmlspecialchars($member['block']),
                        htmlspecialchars($member['district']),
                        htmlspecialchars($member['state'])
                    ]);
                    echo implode(', ', $addressParts); 
                    ?>
                </span>
            </div>
            
            <div class="field">
                <span class="field-label">District :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['district']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Block :</span>
                <span class="field-value"><?php echo htmlspecialchars($member['block']); ?></span>
            </div>
            
            <div class="field">
                <span class="field-label">Registered On :</span>
                <span class="field-value"><?php echo date('Y-m-d', strtotime($member['created_at'])); ?></span>
            </div>
            
            <div class="note-section">
                <div class="note-title">⚠️ महत्वपूर्ण नोट:</div>
                <div class="note-content">
                   आपका सदस्यता पंजीकरण सफलतापूर्वक प्राप्त हो गया है।<br/>
₹ 50 की राशि हमें प्राप्त हो चुकी है।
                </div>
            </div>
            
            <div class="footer">
                <p>तैयार: <?php echo date('d-m-Y H:i'); ?></p>
                <p>Bharat Relief Charitable Trust © 2026</p>
            </div>
        </div>
    </div>
    
    <div class="action-buttons no-print">
        <button class="btn btn-download" onclick="downloadAsPDF()">📥 PDF डाउनलोड करें</button>
        <button class="btn btn-image" onclick="downloadAsJPG()">📷 JPG डाउनलोड करें</button>
        <button class="btn btn-print" onclick="window.print()">🖨️ प्रिंट करें</button>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function downloadAsPDF() {
            const element = document.getElementById('receipt');
            const opt = {
                margin: 3,
                filename: 'Receipt_<?php echo $member_id; ?>_<?php echo date('dmy'); ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', compress: true }
            };
            
            html2pdf().set(opt).from(element).save();
        }
        
        function downloadAsJPG() {
            const element = document.getElementById('receipt');
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false
            }).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/jpeg', 0.95);
                link.download = 'Receipt_<?php echo $member_id; ?>_<?php echo date('dmy'); ?>.jpg';
                link.click();
            }).catch(error => {
                alert('JPG डाउनलोड में त्रुटि: ' + error);
            });
        }
    </script>
</body>
</html>