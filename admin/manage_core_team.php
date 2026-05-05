<?php
// Admin - Core Team Management
// Security Check - Required for all admin pages
require_once 'includes/auth.php';

require_once '../includes/config.php';

// Get all team members
$query = "SELECT * FROM core_team_members ORDER BY uploaded_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Team Management - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Admin Common CSS -->
    <link rel="stylesheet" href="css/admin-common.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --dark-color: #212529;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0px;
            width: 100%;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), #0b5ed7);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }
        
        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--light-bg);
            border: 2px dashed var(--primary-color);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .file-input-wrapper input[type="file"] {
            display: none;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .members-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .member-card {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .member-card:hover {
            background-color: var(--light-bg);
        }
        
        .member-card:last-child {
            border-bottom: none;
        }
        
        .member-photo {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
            border: 2px solid var(--primary-color);
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .member-post {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .member-mobile {
            font-size: 0.9rem;
            color: #666;
        }
        
        .member-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: #198754;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #157347;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #bb2d3b;
        }
        
        .alert-message {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Sidebar -->
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content Wrapper -->
<div class="main-wrapper">
<div class="main-content">

<div class="admin-container">
    <!-- Header -->
    <div class="header-section">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fas fa-users me-2"></i>Core Team Management</h1>
                <p class="mb-0">Manage BRCT Bharat Trust Core Team Members</p>
            </div>
            <a href="index.php" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertMessage" class="alert-message"></div>

    <!-- Add Team Member Form -->
    <div class="form-card">
        <h3 class="mb-4">
            <i class="fas fa-user-plus me-2"></i>Add New Team Member
        </h3>
        <form id="coreTeamForm" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required placeholder="Enter full name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Mobile Number *</label>
                        <input type="tel" class="form-control" id="mobileNumber" name="mobile_number" required placeholder="Enter 10-digit mobile number">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Post/Designation *</label>
                        <input type="text" class="form-control" id="postName" name="post_name" required placeholder="e.g., संस्थापक, सह-संस्थापक">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Photo (PNG, Max 50KB) *</label>
                        <div class="file-input-wrapper">
                            <label class="file-input-label">
                                <i class="fas fa-upload me-2"></i>Choose Photo
                                <input type="file" id="photoInput" name="photo" accept=".png" required>
                            </label>
                            <div class="file-name" id="fileName"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save me-2"></i>Add Team Member
            </button>
        </form>
    </div>

    <!-- Team Members List -->
    <div class="form-card">
        <h3 class="mb-4">
            <i class="fas fa-list me-2"></i>Team Members (<?php echo count($team_members); ?>)
        </h3>
        <div class="members-list">
            <?php if (count($team_members) > 0): ?>
                <?php foreach ($team_members as $member): ?>
                    <div class="member-card">
                        <img src="../uploads/core-team/<?php echo htmlspecialchars($member['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($member['full_name']); ?>" 
                             class="member-photo">
                        <div class="member-info">
                            <div class="member-name"><?php echo htmlspecialchars($member['full_name']); ?></div>
                            <div class="member-post"><?php echo htmlspecialchars($member['post_name']); ?></div>
                            <div class="member-mobile">
                                <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($member['mobile_number']); ?>
                            </div>
                        </div>
                        <div class="member-actions">
                            <button class="btn-delete" onclick="deleteMember(<?php echo $member['id']; ?>)">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: #999;">
                    <i class="fas fa-user-slash fa-3x mb-3" style="display: block; color: #ddd; font-size: 3rem;"></i>
                    <p>No team members added yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    // File input change handler
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const fileSize = e.target.files[0]?.size;
        
        if (fileName) {
            const sizeKB = (fileSize / 1024).toFixed(2);
            document.getElementById('fileName').textContent = `📁 ${fileName} (${sizeKB} KB)`;
            
            if (fileSize > 51200) {
                showAlert('File size must be less than 50KB', 'error');
                e.target.value = '';
                document.getElementById('fileName').textContent = '';
            }
        }
    });

    // Form submission
    document.getElementById('coreTeamForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('../api/add_core_team.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert(result.message, 'success');
                document.getElementById('coreTeamForm').reset();
                document.getElementById('fileName').textContent = '';
                
                // Reload members list after 1 second
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(result.error || 'Error adding team member', 'error');
            }
        } catch (error) {
            showAlert('Error: ' + error.message, 'error');
        }
    });

    // Show alert
    function showAlert(message, type) {
        const alertDiv = document.getElementById('alertMessage');
        alertDiv.textContent = message;
        alertDiv.className = 'alert-message alert-' + type;
        
        setTimeout(() => {
            alertDiv.className = 'alert-message';
        }, 4000);
    }

    // Delete member
    function deleteMember(id) {
        if (confirm('क्या आप सुनिश्चित हैं कि आप इस सदस्य को हटाना चाहते हैं? (Are you sure you want to delete this team member?)')) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../api/delete_core_team.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showAlert(result.message, 'success');
                    // Reload members list after 1 second
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(result.error || 'Error deleting team member', 'error');
                }
            })
            .catch(error => {
                showAlert('Error: ' + error.message, 'error');
            });
        }
    }
</script>

</div> <!-- main-content -->
</div> <!-- main-wrapper -->

</body>
</html>
