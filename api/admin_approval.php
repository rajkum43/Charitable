<?php
// Admin Approval API for Poll Applications
header('Content-Type: application/json');
session_start();

require_once '../includes/config.php';

// Check if user is admin (check for admin_id in session)
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$type = $data['type'] ?? 'poll_application'; // 'poll_application' or 'death_claim'

try {
    // Handle Death Claim Approvals
    if ($type === 'death_claim') {
        if ($action === 'approve') {
            $claim_id = (int)($data['claim_id'] ?? 0);
            
            if (empty($claim_id)) {
                throw new Exception('Claim ID is required');
            }
            
            $stmt = $pdo->prepare("
                UPDATE death_claims 
                SET status = 'Approved'
                WHERE id = ?
            ");
            
            if ($stmt->execute([$claim_id])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'आवेदन स्वीकृत कर दिया गया'
                ]);
            } else {
                throw new Exception('Failed to approve claim');
            }
            
        } elseif ($action === 'reject') {
            $claim_id = (int)($data['claim_id'] ?? 0);
            $remark = $data['remark'] ?? 'अन्य कारण';
            
            if (empty($claim_id)) {
                throw new Exception('Claim ID is required');
            }
            
            $stmt = $pdo->prepare("
                UPDATE death_claims 
                SET status = 'Rejected', remark = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$remark, $claim_id])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'आवेदन अस्वीकार कर दिया गया'
                ]);
            } else {
                throw new Exception('Failed to reject claim');
            }
        } else {
            throw new Exception('Invalid action for death claim');
        }
        exit;
    }

    // Handle Poll Applications (keep existing poll application logic)
    if ($action === 'get_applications') {
        // Get all pending applications
        $type = $data['type'] ?? '';  // 'vivah', 'death', or '' for all
        
        $query = "SELECT * FROM poll_applications WHERE status = 'Pending'";
        if (!empty($type) && in_array($type, ['vivah', 'death'])) {
            $query .= " AND type = '" . $pdo->quote($type) . "'";
        }
        $query .= " ORDER BY created_at DESC";
        
        $result = $pdo->query($query);
        $applications = $result->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $applications,
            'total' => count($applications)
        ]);

    } elseif ($action === 'approve_application') {
        // Approve an application
        $app_id = (int)$data['id'];
        
        if (empty($app_id)) {
            throw new Exception('Application ID is required');
        }
        
        $stmt = $pdo->prepare("
            UPDATE poll_applications 
            SET status = 'Approved', approved_date = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$app_id])) {
            echo json_encode([
                'success' => true,
                'message' => 'Application approved successfully'
            ]);
        } else {
            throw new Exception('Failed to approve application');
        }

    } elseif ($action === 'reject_application') {
        // Reject an application
        $app_id = (int)$data['id'];
        $reason = $data['reason'] ?? '';
        
        if (empty($app_id)) {
            throw new Exception('Application ID is required');
        }
        
        $stmt = $pdo->prepare("
            UPDATE poll_applications 
            SET status = 'Rejected', rejection_reason = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$reason, $app_id])) {
            echo json_encode([
                'success' => true,
                'message' => 'Application rejected successfully'
            ]);
        } else {
            throw new Exception('Failed to reject application');
        }

    } elseif ($action === 'get_application_details') {
        // Get detailed information about a specific application
        $app_id = (int)$data['id'];
        
        if (empty($app_id)) {
            throw new Exception('Application ID is required');
        }
        
        $stmt = $pdo->prepare("
            SELECT pa.*, m.name, m.mobile, m.aadhar
            FROM poll_applications pa
            LEFT JOIN members m ON pa.member_id = m.member_id
            WHERE pa.id = ?
        ");
        
        $stmt->execute([$app_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            throw new Exception('Application not found');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $application
        ]);

    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
