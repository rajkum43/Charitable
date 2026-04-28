// Slider Manager JavaScript

const modal = new bootstrap.Modal(document.getElementById('addSliderModal'));
const form = document.getElementById('sliderForm');

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
    // This would require fetching slider details via AJAX
    alert('Edit functionality - fetch slider data and populate form');
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

// Reset form on modal close
document.getElementById('addSliderModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalTitle').textContent = 'Add New Slider';
    document.getElementById('sliderForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('image').setAttribute('required', 'required');
});
