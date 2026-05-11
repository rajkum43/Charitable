document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('changePasswordForm');
    const alertContainer = document.getElementById('formAlert');
    const submitButton = document.getElementById('passwordSubmitBtn');
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        alertContainer.innerHTML = '';

        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();

        if (!newPassword || !confirmPassword) {
            showAlert('कृपया दोनों पासवर्ड फ़ील्ड भरें।', 'danger');
            return;
        }

        if (newPassword.length < 6) {
            showAlert('पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।', 'danger');
            return;
        }

        if (newPassword !== confirmPassword) {
            showAlert('पासवर्ड मेल नहीं खा रहे हैं। कृपया पुनः जांचें।', 'danger');
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>अपडेट कर रहे हैं...';

        try {
            const response = await fetch('../api/member_change_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            });

            const result = await response.json();
            if (!response.ok) {
                showAlert(result.message || 'कुछ गलत हो गया। कृपया पुनः प्रयास करें।', 'danger');
            } else {
                showAlert(result.message || 'पासवर्ड सफलतापूर्वक अपडेट हो गया।', 'success');
                form.reset();
            }
        } catch (error) {
            showAlert('नेटवर्क त्रुटि। कृपया बाद में फिर से प्रयास करें।', 'danger');
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-key me-1"></i> पासवर्ड अपडेट करें';
        }
    });

    toggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            button.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });

    function showAlert(message, type) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-custom" role="alert">
                ${message}
            </div>
        `;
    }
});

function logoutMember() {
    if (!confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
        return;
    }
    
    fetch('api/logout.php', {
        method: 'POST'
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'लॉगआउट विफल');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('लॉगआउट में त्रुटि: ' + error.message);
    });
}
