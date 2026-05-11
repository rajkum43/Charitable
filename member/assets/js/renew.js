// Member Renewal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const renewalForm = document.getElementById('renewalForm');
    const submitBtn = document.getElementById('submitBtn');
    const alertContainer = document.getElementById('alertContainer');

    // Form submission
    renewalForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const transactionId = document.getElementById('transactionId').value.trim();

        if (!transactionId) {
            showAlert('लेन-देन ID दर्ज करें', 'danger');
            return;
        }

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>प्रोसेस हो रहा है...';

        // Submit renewal
        fetch('../api/renew.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                transactionId: transactionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('सदस्यता सफलतापूर्वक नवीनीकृत की गई!', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showAlert(data.message || 'नवीनीकरण विफल हुआ', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('सर्वर त्रुटि हुई', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>नवीनीकरण पूरा करें';
        });
    });

    // Copy to clipboard function
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            showAlert('UPI ID कॉपी किया गया!', 'success');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            showAlert('कॉपी विफल हुआ', 'danger');
        });
    };

    // Show alert function
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});