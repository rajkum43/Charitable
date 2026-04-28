<?php
/**
 * Death Claims Receipt - Generate receipt after claim submission
 */

header('Content-Type: text/html; charset=utf-8');

require_once '../includes/config.php';

$claim_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($claim_id)) {
    die('<div class="alert alert-danger">क्लेम ID आवश्यक है।</div>');
}

try {
    // Extract ID from formatted claim_id
    // Format: BRCT-D + yyyymmdd + 4-digit ID
    // Example: BRCT-D202603300008 -> ID is 8
    
    // Method 1: Remove prefix and date, get 4-digit ID
    if (strpos($claim_id, 'BRCT-D') === 0 && strlen($claim_id) >= 18) {
        // Skip 'BRCT-D' (6 chars) and date (8 chars) = skip first 14 chars
        $id_part = substr($claim_id, 14, 4);
        $id_only = intval($id_part);
    } else {
        // Fallback: just get last 4 digits
        $id_only = intval(substr($claim_id, -4));
    }
    
    $stmt = $pdo->prepare("SELECT * FROM death_claims WHERE id = ? LIMIT 1");
    $stmt->execute([$id_only]);
    $claim = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$claim) {
        die('<div class="alert alert-danger">क्लेम नहीं मिला। ID: ' . htmlspecialchars($id_only) . '</div>');
    }
} catch (Exception $e) {
    die('<div class="alert alert-danger">त्रुटि: ' . $e->getMessage() . '</div>');
}

$created_date = new DateTime($claim['created_at']);
$formatted_date = $created_date->format('d-m-Y H:i');
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>मृत्यु सहायता रसीद - BRCT Bharat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .receipt-container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 3px solid #8B0000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .receipt-header h1 {
            color: #8B0000;
            font-weight: bold;
            margin: 0;
            font-size: 28px;
        }
        .receipt-header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .receipt-content {
            margin: 30px 0;
        }
        .receipt-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #8B0000;
            border-radius: 5px;
        }
        .receipt-section h3 {
            color: #8B0000;
            font-size: 18px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .receipt-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 12px;
        }
        .receipt-row.full {
            grid-template-columns: 1fr;
        }
        .receipt-field {
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .receipt-field label {
            display: block;
            font-weight: bold;
            color: #333;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .receipt-field span {
            display: block;
            font-size: 16px;
            color: #555;
            word-break: break-all;
        }
        .claim-id-box {
            background: linear-gradient(135deg, #8B0000, #d32f2f);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .claim-id-box h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            font-weight: 600;
        }
        .claim-id-box .claim-id {
            font-size: 32px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            background: #ffc107;
            color: #333;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .receipt-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 13px;
            border-top: 2px solid #e0e0e0;
            margin-top: 30px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .action-buttons button {
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
        }
        @media print {
            body {
                background: white;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
            .action-buttons {
                display: none;
            }
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Success Message -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 style="color: #28a745; margin-bottom: 10px;">आवेदन सफलतापूर्वक जमा हुआ</h2>
            <p style="color: #666; margin: 0;">आपका मृत्यु सहायता आवेदन हमारे सिस्टम में दर्ज हो गया है।</p>
        </div>

        <!-- Claim ID -->
        <div class="claim-id-box">
            <h2>आपकी एप्लीकेशन ID</h2>
            <div class="claim-id"><?php echo htmlspecialchars($claim_id); ?></div>
            <span class="status-badge">
                <i class="fas fa-hourglass-half me-2"></i>लंबित जांच
            </span>
        </div>

        <!-- Member Details -->
        <div class="receipt-section">
            <h3><i class="fas fa-user me-2"></i>सदस्य जानकारी</h3>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>सदस्य ID</label>
                    <span><?php echo htmlspecialchars($claim['member_id']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>पूरा नाम</label>
                    <span><?php echo htmlspecialchars($claim['full_name']); ?></span>
                </div>
            </div>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>पिता/पति का नाम</label>
                    <span><?php echo htmlspecialchars($claim['father_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="receipt-field">
                    <label>जन्मतिथि</label>
                    <span><?php echo htmlspecialchars($claim['dob']); ?></span>
                </div>
            </div>
        </div>

        <!-- Death Details -->
        <div class="receipt-section">
            <h3><i class="fas fa-heart-broken me-2"></i>मृत्यु विवरण</h3>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>मृत्यु तिथि</label>
                    <span><?php echo htmlspecialchars($claim['death_date']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>स्थान</label>
                    <span><?php echo htmlspecialchars($claim['death_place']); ?></span>
                </div>
            </div>
            <div class="receipt-row full">
                <div class="receipt-field">
                    <label>कारण</label>
                    <span><?php echo htmlspecialchars($claim['death_reason'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Nominee Details -->
        <div class="receipt-section">
            <h3><i class="fas fa-address-card me-2"></i>नॉमिनी जानकारी</h3>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>नाम</label>
                    <span><?php echo htmlspecialchars($claim['nominee_name']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>संबंध</label>
                    <span><?php echo htmlspecialchars($claim['nominee_relation']); ?></span>
                </div>
            </div>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>मोबाइल नंबर</label>
                    <span><?php echo htmlspecialchars($claim['nominee_mobile']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>जन्मतिथि</label>
                    <span><?php echo htmlspecialchars($claim['nominee_dob'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="receipt-section">
            <h3><i class="fas fa-university me-2"></i>बैंक विवरण</h3>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>बैंक का नाम</label>
                    <span><?php echo htmlspecialchars($claim['bank_name']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>IFSC कोड</label>
                    <span><?php echo htmlspecialchars($claim['ifsc_code']); ?></span>
                </div>
            </div>
            <div class="receipt-row">
                <div class="receipt-field">
                    <label>खाते धारक का नाम</label>
                    <span><?php echo htmlspecialchars($claim['account_holder_name']); ?></span>
                </div>
                <div class="receipt-field">
                    <label>शाखा</label>
                    <span><?php echo htmlspecialchars($claim['branch_name'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <?php if (!empty($claim['upi_id'])): ?>
            <div class="receipt-row full">
                <div class="receipt-field">
                    <label>UPI ID</label>
                    <span><?php echo htmlspecialchars($claim['upi_id']); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p><strong>आवेदन तिथि:</strong> <?php echo $formatted_date; ?></p>
            <p>यह रसीद आपके आवेदन की पुष्टि है। कृपया इसे भविष्य के संदर्भ के लिए सुरक्षित रखें।</p>
            <p style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
                <strong>BRCT Bharat - मृत्यु सहायता योजना</strong><br>
                यदि कोई प्रश्न है तो संपर्क करें: support@brctbharat.org
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>प्रिंट करें
            </button>
            <button class="btn btn-info" onclick="downloadPDF()">
                <i class="fas fa-download me-2"></i>PDF डाउनलोड करें
            </button>
            <button class="btn btn-secondary" onclick="goHome()">
                <i class="fas fa-home me-2"></i>होम पर जाएं
            </button>
        </div>
    </div>

    <script>
        function downloadPDF() {
            alert('PDF डाउनलोड सुविधा जल्द उपलब्ध होगी।');
        }

        function goHome() {
            window.location.href = '/Charitable/';
        }
    </script>
</body>
</html>
