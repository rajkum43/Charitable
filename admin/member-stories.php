<?php
// Security Check - Required for all admin pages
require_once 'includes/auth.php';

require_once '../includes/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $result = $conn->query("SELECT image FROM member_stories WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        $image_path = "../assets/images/stories/" . $row['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    $conn->query("DELETE FROM member_stories WHERE id = $id");
    echo json_encode(['success' => true, 'message' => 'Story deleted successfully']);
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'edit')) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $story = $conn->real_escape_string($_POST['story']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
    $image_name = null;

    // Handle image upload (Passport size)
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "../assets/images/stories/";
        $image_name = basename($_FILES['image']['name']);
        $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image_name);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Delete old image if editing
            if ($id) {
                $result = $conn->query("SELECT image FROM member_stories WHERE id = $id");
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
        if (!$image_name) {
            echo json_encode(['success' => false, 'message' => 'Image is required']);
            exit;
        }
        $sql = "INSERT INTO member_stories (name, address, story, image, rating, status) 
                VALUES ('$name', '$address', '$story', '$image_name', $rating, 1)";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Story added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    } else {
        // Update
        if ($image_name) {
            $sql = "UPDATE member_stories SET name='$name', address='$address', story='$story', 
                    image='$image_name', rating=$rating WHERE id = $id";
        } else {
            $sql = "UPDATE member_stories SET name='$name', address='$address', story='$story', 
                    rating=$rating WHERE id = $id";
        }
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Story updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
    }
    exit;
}

// Fetch all stories
$stories = [];
$result = $conn->query("SELECT * FROM member_stories ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Stories Manager - BRCT Bharat Trust</title>
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

        .story-card { 
            transition: all 0.3s;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .story-card:hover { 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }

        .story-image { 
            height: 200px;
            width: 160px;
            object-fit: cover;
            border-radius: 8px;
        }

        .modal-header {
            background: #198754;
            color: white;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }

        .form-control:focus, .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        .passport-preview {
            width: 120px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
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

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-weight: 700;
            color: #198754;
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
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                position: fixed;
                transform: translateX(0) !important;
            }
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
                            <i class="fas fa-user-circle me-2"></i>Member Stories
                        </h1>
                        <p class="text-secondary mb-0">सदस्यों की कहानियां प्रबंधित करें</p>
                    </div>
                    <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addStoryModal">
                        <i class="fas fa-plus me-2"></i>Add Story
                    </button>
                </div>

                <!-- Stories Table -->
                <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Story Summary</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($stories) > 0): ?>
                                <?php foreach ($stories as $story): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/images/stories/<?php echo htmlspecialchars($story['image']); ?>" alt="<?php echo htmlspecialchars($story['name']); ?>" class="passport-preview">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($story['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo substr(htmlspecialchars($story['address']), 0, 40) . '...'; ?>
                                    </td>
                                    <td>
                                        <small class="text-secondary"><?php echo substr(htmlspecialchars($story['story']), 0, 50) . '...'; ?></small>
                                    </td>
                                    <td>
                                        <div class="text-warning">
                                            <?php for ($i = 0; $i < $story['rating']; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>
                                            <?php for ($i = $story['rating']; $i < 5; $i++): ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editStory(<?php echo $story['id']; ?>, '<?php echo htmlspecialchars($story['name']); ?>', '<?php echo htmlspecialchars($story['address']); ?>', '<?php echo htmlspecialchars($story['story']); ?>', <?php echo $story['rating']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteStory(<?php echo $story['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                                        No stories yet. Add one to get started!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="addStoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Member Story</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="storyForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="storyId" name="id">
                        <input type="hidden" name="action" id="formAction" value="add">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name (नाम)</label>
                                    <input type="text" class="form-control" id="name" name="name" required placeholder="Member's full name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rating (⭐)</label>
                                    <select class="form-select" id="rating" name="rating">
                                        <option value="5" selected>⭐⭐⭐⭐⭐ (5 Stars)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 Stars)</option>
                                        <option value="3">⭐⭐⭐ (3 Stars)</option>
                                        <option value="2">⭐⭐ (2 Stars)</option>
                                        <option value="1">⭐ (1 Star)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Address (पता)</label>
                            <input type="text" class="form-control" id="address" name="address" required placeholder="City, District">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Story (कहानी)</label>
                            <textarea class="form-control" id="story" name="story" rows="5" required placeholder="Member's experience and story..."></textarea>
                            <small class="text-secondary d-block mt-2">Example: "BRCT Bharat Trust ने मेरी बेटी के विवाह में बहुत मदद की..."</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Passport Size Photo</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-secondary">Recommended: 200x250px (Passport size)</small>
                            <div id="imagePreview" class="mt-3"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Save Story
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('addStoryModal'));
        const form = document.getElementById('storyForm');

        // Image preview
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="passport-preview"> <small class="d-block mt-2">Preview</small>';
                }
                reader.readAsDataURL(this.files[0]);
            }
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

        function editStory(id, name, address, story, rating) {
            document.getElementById('modalTitle').textContent = 'Edit Member Story';
            document.getElementById('storyId').value = id;
            document.getElementById('formAction').value = 'edit';
            document.getElementById('name').value = name;
            document.getElementById('address').value = address;
            document.getElementById('story').value = story;
            document.getElementById('rating').value = rating;
            document.getElementById('image').removeAttribute('required');
            modal.show();
        }

        function deleteStory(id) {
            if (confirm('Are you sure you want to delete this story?')) {
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

        // Reset form on modal close
        document.getElementById('addStoryModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalTitle').textContent = 'Add New Member Story';
            document.getElementById('storyForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('image').setAttribute('required', 'required');
            document.getElementById('imagePreview').innerHTML = '';
        });
    </script>
</body>
</html>
