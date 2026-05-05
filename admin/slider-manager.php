<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';

require_once '../includes/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle fetch slider data for editing
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get') {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM sliders WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Slider not found']);
    }
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $result = $conn->query("SELECT image FROM sliders WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $image_path = "../assets/images/slider/" . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    $conn->query("DELETE FROM sliders WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Slider deleted successfully']);
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'edit')) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $heading = $conn->real_escape_string($_POST['heading']);
    $description = $conn->real_escape_string($_POST['description']);
    $button_text = $conn->real_escape_string($_POST['button_text']);
    $button_link = $conn->real_escape_string($_POST['button_link']);
    $image_name = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "../assets/images/slider/";
        $image_name = basename($_FILES['image']['name']);
        $image_name = time() . '_' . $image_name;
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Delete old image if editing
            if ($id) {
                $result = $conn->query("SELECT image FROM sliders WHERE id = $id");
                if ($result && $row = $result->fetch_assoc()) {
                    $old_image = $target_dir . $row['image'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Image upload failed']);
            exit;
        }
    }

    if ($_POST['action'] == 'add') {
        $sql = "INSERT INTO sliders (heading, description, image, button_text, button_link, status) 
                VALUES ('$heading', '$description', '$image_name', '$button_text', '$button_link', 1)";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Slider added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        // Update
        if ($image_name) {
            $sql = "UPDATE sliders SET heading='$heading', description='$description', image='$image_name', 
                    button_text='$button_text', button_link='$button_link' WHERE id = $id";
        } else {
            $sql = "UPDATE sliders SET heading='$heading', description='$description', 
                    button_text='$button_text', button_link='$button_link' WHERE id = $id";
        }
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Slider updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    }
    exit;
}

// Fetch all sliders
$sliders = [];
$result = $conn->query("SELECT * FROM sliders ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slider Manager - BRCT Bharat Trust</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .main-wrapper {
            display: flex;
            flex: 1;
            margin-left: 250px;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Mobile main-content styling */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
        }

        .sidebar { min-height: 100vh; }
        
        .slider-card {
            transition: all 0.3s;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .slider-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .slider-image {
            height: 150px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .modal-header {
            background: #0d6efd;
            color: white;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .admin-footer {
            margin-top: auto;
            background: white;
            border-top: 1px solid #e9ecef;
            padding: 20px;
            margin-left: 250px;
            flex-shrink: 0;
            width: calc(100% - 250px);
            position: static;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 56px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 100%;
                max-width: 250px;
                z-index: 2000;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .admin-footer {
                margin-left: 0;
                width: 100%;
                position: static;
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                position: fixed;
                transform: translateX(0) !important;
            }
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-weight: 700;
            color: #0d6efd;
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
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="fas fa-images me-2"></i>Slider Manager
                        </h1>
                        <p class="text-secondary mb-0">Manage hero slider images and content</p>
                    </div>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addSliderModal">
                        <i class="fas fa-plus me-2"></i>Add Slider
                    </button>
                </div>

                <!-- Sliders Grid -->
                <div class="row g-4">
                <?php foreach ($sliders as $slider): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card slider-card h-100">
                        <img src="../assets/images/slider/<?php echo $slider['image']; ?>" class="card-img-top slider-image" alt="Slider">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($slider['heading']); ?></h5>
                            <p class="card-text text-secondary"><?php echo substr(htmlspecialchars($slider['description']), 0, 60) . '...'; ?></p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning flex-grow-1" onclick="editSlider(<?php echo $slider['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSlider(<?php echo $slider['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addSliderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Slider</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="sliderForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="sliderId" name="id">
                        <input type="hidden" name="action" id="formAction" value="add">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Heading (Title)</label>
                            <input type="text" class="form-control" id="heading" name="heading" required placeholder="e.g., BRCT Bharat Trust">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Detailed description..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Button Text</label>
                            <input type="text" class="form-control" id="button_text" name="button_text" placeholder="e.g., Join Now">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Button Link</label>
                            <input type="text" class="form-control" id="button_link" name="button_link" placeholder="e.g., pages/register.php">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-secondary">Recommended size: 1920x600px (Required for new slider, optional for edit)</small>
                            <div id="currentImagePreview" style="display: none; margin-top: 10px;">
                                <p class="text-muted">Current image:</p>
                                <img id="currentImage" src="" alt="Current slider image" style="max-width: 200px; border-radius: 5px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Slider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('addSliderModal'));
        const form = document.getElementById('sliderForm');

        // Reset form when modal is hidden
        document.getElementById('addSliderModal').addEventListener('hidden.bs.modal', function() {
            form.reset();
            document.getElementById('modalTitle').textContent = 'Add New Slider';
            document.getElementById('formAction').value = 'add';
            document.getElementById('sliderId').value = '';
            document.getElementById('image').setAttribute('required', 'required');
            document.getElementById('currentImagePreview').style.display = 'none';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        function editSlider(id) {
            // Fetch slider data via AJAX
            fetch('?action=get&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const slider = data.data;
                        
                        // Populate form fields
                        document.getElementById('sliderId').value = slider.id;
                        document.getElementById('heading').value = slider.heading;
                        document.getElementById('description').value = slider.description;
                        document.getElementById('button_text').value = slider.button_text;
                        document.getElementById('button_link').value = slider.button_link;
                        document.getElementById('formAction').value = 'edit';
                        
                        // Make image optional for edit
                        document.getElementById('image').removeAttribute('required');
                        
                        // Show current image preview
                        document.getElementById('currentImage').src = '../assets/images/slider/' + slider.image;
                        document.getElementById('currentImagePreview').style.display = 'block';
                        
                        // Update modal title
                        document.getElementById('modalTitle').textContent = 'Edit Slider';
                        
                        // Show modal
                        modal.show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error fetching slider data: ' + error.message);
                });
        }

        function deleteSlider(id) {
            if (confirm('Are you sure you want to delete this slider?')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + id
                }).then(response => response.json())
                  .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    }
                  });
            }
        }
    </script>
</body>
</html>
