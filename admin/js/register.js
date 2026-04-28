// Register Page JavaScript

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    strengthBar.className = 'strength-bar';
    
    if (strength <= 2) {
        strengthBar.classList.add('weak');
        strengthText.textContent = 'Strength: Weak';
        strengthText.className = 'text-danger';
    } else if (strength === 3) {
        strengthBar.classList.add('fair');
        strengthText.textContent = 'Strength: Fair';
        strengthText.className = 'text-warning';
    } else {
        strengthBar.classList.add('strong');
        strengthText.textContent = 'Strength: Strong';
        strengthText.className = 'text-success';
    }
}

async function handleRegister(event) {
    event.preventDefault();

    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm_password = document.getElementById('confirm_password').value;
    const registerBtn = document.getElementById('registerBtn');

    // Validation
    if (!username || !email || !password || !confirm_password) {
        showAlert('Please fill all fields', 'danger');
        return;
    }

    if (username.length < 3) {
        showAlert('Username must be at least 3 characters', 'danger');
        return;
    }

    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        showAlert('Username can only contain letters, numbers, and underscores', 'danger');
        return;
    }

    if (password.length < 8) {
        showAlert('Password must be at least 8 characters', 'danger');
        return;
    }

    if (password !== confirm_password) {
        showAlert('Passwords do not match', 'danger');
        return;
    }

    // Check email format
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showAlert('Invalid email address', 'danger');
        return;
    }

    // Disable button and show loading
    registerBtn.disabled = true;
    const originalText = registerBtn.innerHTML;
    registerBtn.innerHTML = '<span class="spinner border border-2 border-right-0" role="status"></span>Creating...';

    try {
        const response = await fetch('api/register-admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                email: email,
                password: password
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response format - Server did not return JSON');
        }

        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            throw new Error('Failed to parse server response as JSON');
        }

        if (data.success) {
            showAlert('✅ Admin user created successfully! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            showAlert(data.message || 'Registration failed', 'danger');
            registerBtn.disabled = false;
            registerBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Register error:', error);
        showAlert('Error: ' + error.message, 'danger');
        registerBtn.disabled = false;
        registerBtn.innerHTML = originalText;
    }
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'danger' ? 'alert-danger' : 'alert-success';
    
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.innerHTML = alertHTML;

    if (type === 'success') {
        // Don't auto-dismiss success message (user will see redirect)
    } else {
        // Auto-dismiss after 5 seconds for errors
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
}

// Auto-focus username on page load
document.getElementById('username').focus();
