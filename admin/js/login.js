// Login Page JavaScript

// Check for expired session
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('expired')) {
    showAlert('Session expired. Please login again.', 'danger');
}

async function handleLogin(event) {
    event.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const loginBtn = document.getElementById('loginBtn');

    // Validate input
    if (!username || !password) {
        showAlert('Please enter username and password', 'danger');
        return;
    }

    // Disable button and show loading
    loginBtn.disabled = true;
    const originalText = loginBtn.innerHTML;
    loginBtn.innerHTML = '<span class="spinner border border-2 border-right-0" role="status"></span>Logging in...';

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });

        // First check if response is okay
        if (!response.ok && response.status !== 401 && response.status !== 400) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Try to parse as JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Response is not JSON:', text);
            showAlert('Server error: Invalid response format. Check console.', 'danger');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalText;
            return;
        }

        const data = await response.json();

        if (data.success) {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.message || 'Login failed', 'danger');
            loginBtn.disabled = false;
            loginBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Login error:', error);
        showAlert('Error: ' + error.message, 'danger');
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalText;
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

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Auto-focus username on page load
document.getElementById('username').focus();
