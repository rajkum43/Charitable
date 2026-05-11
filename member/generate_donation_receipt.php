<?php
/**
 * Generate Donation Receipt
 * Member facing receipt display page
 * Usage: /member/generate_donation_receipt.php?txn_id=123
 */

session_start();
header('Content-Type: text/html; charset=utf-8');

require_once '../includes/config.php';

try {
    // Get transaction ID from URL
    $txnId = isset($_GET['txn_id']) ? intval($_GET['txn_id']) : 0;
    
    if (!$txnId) {
        throw new Exception('Transaction ID required');
    }
    
    // 1. Fetch Transaction Details
    $txnStmt = $pdo->prepare("
        SELECT id, member_id, donation_to_member_id, claim_number, application_type, 
               amount, transaction_number, receipt_file_path, remarks, 
               status, created_at, updated_at
        FROM donation_transactions
        WHERE id = ?
        LIMIT 1
    ");
    $txnStmt->execute([$txnId]);
    $transaction = $txnStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        throw new Exception('Transaction not found');
    }
    
    // 2. Fetch Donor Details
    $donorStmt = $pdo->prepare("
        SELECT member_id, full_name, mobile_number, email, permanent_address, 
               block, district, state, aadhar_number, created_at
        FROM members
        WHERE member_id = ?
        LIMIT 1
    ");
    $donorStmt->execute([$transaction['member_id']]);
    $donor = $donorStmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Fetch Recipient Details
    $recipient = null;
    
    if ($transaction['application_type'] === 'Death_Claims') {
        $recipientStmt = $pdo->prepare("
            SELECT dc.id, dc.member_id, dc.full_name, dc.nominee_name, dc.nominee_relation,
                   dc.death_date, dc.bank_name, dc.account_number, dc.ifsc_code, dc.upi_id,
                   m.permanent_address, m.block, m.district, m.state
            FROM death_claims dc
            LEFT JOIN members m ON dc.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
            WHERE dc.claim_id = ?
            LIMIT 1
        ");
        $recipientStmt->execute([$transaction['claim_number']]);
        $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($transaction['application_type'] === 'Beti_Vivah') {
        $recipientStmt = $pdo->prepare("
            SELECT bv.id, bv.member_id, bv.bride_name, bv.groom_name, bv.member_name, bv.wedding_date,
                   bv.bank_name, bv.account_number, bv.ifsc_code, bv.upi_id,
                   m.permanent_address as address, m.block, m.district, m.state
            FROM beti_vivah_aavedan bv
            LEFT JOIN members m ON bv.member_id COLLATE utf8mb4_unicode_ci = m.member_id COLLATE utf8mb4_unicode_ci
            WHERE bv.application_number = ?
            LIMIT 1
        ");
        $recipientStmt->execute([$transaction['claim_number']]);
        $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$recipient) {
        throw new Exception('Recipient details not found');
    }
    
    // Format dates
    $transactionDate = new DateTime($transaction['created_at']);
    $formattedDate = $transactionDate->format('d/m/Y H:i');
    
} catch (Exception $e) {
    http_response_code(400);
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>दान रसीद - Donation Receipt</title>
    <link rel="stylesheet" href="assets/css/donation_receipt.css">
</head>
<body>
    <div class="receipt-container">
        <!-- Watermark Logo -->
        <div class="watermark">
            <img src="../assets/images/logo/logo.png" alt="BRCT Logo">
        </div>
        
        <!-- Receipt Content -->
        <div class="receipt-content">
            <!-- Header -->
            <div class="receipt-header">
                <div class="trust-name">BHARAT RELIEF CHARITABLE TRUST</div>
                <div class="trust-subtitle">भारत रिलीफ चैरिटेबल ट्रस्ट</div>
            </div>
            
            <!-- Receipt Fields -->
            <div class="receipt-field">
                <div class="field-label">Thank You For Donation To</div>
                <div class="field-value">
                    <strong>
                        <?php 
                        if ($transaction['application_type'] === 'Death_Claims') {
                            echo htmlspecialchars($recipient['full_name'] ?? 'N/A') . ' (deceased family)';
                        } else {
                            // Show applicant name for Beti_Vivah
                            echo htmlspecialchars($recipient['member_name'] ?? 'N/A');
                        }
                        ?>
                    </strong>
                </div>
            </div>
            
            <div class="receipt-field">
                <div class="field-label">From Sh./Smt./M/S</div>
                <div class="field-value"><strong><?php echo htmlspecialchars($donor['full_name'] ?? 'N/A'); ?></strong></div>
            </div>
            
            <div class="receipt-field">
                <div class="field-label">Email</div>
                <div class="field-value"><?php echo htmlspecialchars($donor['email'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="receipt-field">
                <div class="field-label">Donated Amount</div>
                <div class="field-value"><strong>₹ <?php echo number_format($transaction['amount'], 2); ?></strong></div>
            </div>
            
            <div class="receipt-field">
                <div class="field-label">On</div>
                <div class="field-value"><strong><?php echo $formattedDate; ?></strong></div>
            </div>
            
            <div class="section-divider"></div>
            
            <div class="receipt-field">
                <div class="field-label">Transaction ID</div>
                <div class="field-value"><strong><?php echo htmlspecialchars($transaction['transaction_number']); ?></strong></div>
            </div>
            
            <div class="receipt-field">
                <div class="field-label">Cause For Donation</div>
                <div class="field-value">
                    <strong>
                        <?php 
                        if ($transaction['application_type'] === 'Death_Claims') {
                            echo 'Death Benefit Assistance / मृत्यु सहायता';
                        } else {
                            echo 'Beti Vivah Support / बेटी विवाह सहायता';
                        }
                        ?>
                    </strong>
                </div>
            </div>
            
            <div class="section-divider"></div>
            
            <!-- Footer -->
            <div class="receipt-footer">
                <div class="footer-text">Thank You For Your Donation</div>
                <div style="margin-top: 10px; font-size: 12px; color: #666;">
                    आपके दान के लिए धन्यवाद
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons (hidden during print) -->
    <div class="print-button">
        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="btn-download" onclick="downloadAsJPG()">📥 Download as JPG</button>
    </div>

    <!-- html2canvas library for JPG conversion -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        /**
         * Download receipt as JPG
         */
        function downloadAsJPG() {
            const element = document.querySelector('.receipt-container');
            const txnId = new URLSearchParams(window.location.search).get('txn_id');
            const fileName = `Receipt_${txnId}_${new Date().getTime()}.jpg`;
            
            // Show loading message
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '⏳ Processing...';
            btn.disabled = true;
            
            html2canvas(element, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true,
                allowTaint: true,
                imageTimeout: 0
            }).then(canvas => {
                // Convert canvas to JPG and download
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/jpeg', 0.95);
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Reset button
                btn.textContent = originalText;
                btn.disabled = false;
            }).catch(error => {
                console.error('Error capturing receipt:', error);
                alert('रसीद डाउनलोड करने में त्रुटि हुई। कृपया पुनः प्रयास करें।');
                btn.textContent = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
