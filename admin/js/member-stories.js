// Member Stories JavaScript

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
