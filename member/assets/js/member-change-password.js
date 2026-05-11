document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('changePasswordForm');
    const alertContainer = document.getElementById('formAlert');
    const submitButton = document.getElementById('passwordSubmitBtn');
    const toggleButtons = document.querySelectorAll('.password-toggle-btn');

    /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    */
    form.addEventListener('submit', async function (event) {

        event.preventDefault();

        alertContainer.innerHTML = '';

        const newPassword = document.getElementById('newPassword').value.trim();

        const confirmPassword = document.getElementById('confirmPassword').value.trim();

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        if (!newPassword || !confirmPassword) {

            showAlert('कृपया दोनों पासवर्ड फ़ील्ड भरें।', 'danger');

            return;
        }

        if (newPassword.length < 6) {

            showAlert('पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।', 'danger');

            return;
        }

        if (newPassword !== confirmPassword) {

            showAlert('पासवर्ड मेल नहीं खा रहे हैं।', 'danger');

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Loading State
        |--------------------------------------------------------------------------
        */
        submitButton.disabled = true;

        submitButton.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            अपडेट हो रहा है...
        `;

        try {

            /*
            |--------------------------------------------------------------------------
            | API Request
            |--------------------------------------------------------------------------
            */
            const response = await fetch('../api/member_change_password.php', {

                method: 'POST',

                credentials: 'same-origin',

                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                body: JSON.stringify({
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            });

            /*
            |--------------------------------------------------------------------------
            | Parse Response
            |--------------------------------------------------------------------------
            */
            let result;

            try {
                result = await response.json();
            } catch (jsonError) {

                throw new Error('सर्वर से अमान्य प्रतिक्रिया प्राप्त हुई।');
            }

            /*
            |--------------------------------------------------------------------------
            | Handle Success/Error
            |--------------------------------------------------------------------------
            */
            if (!response.ok || !result.success) {

                throw new Error(
                    result.message || 'पासवर्ड अपडेट नहीं हो सका।'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */
            showAlert(
                result.message || 'पासवर्ड सफलतापूर्वक अपडेट हो गया।',
                'success'
            );

            form.reset();

        } catch (error) {

            console.error('Password Change Error:', error);

            showAlert(
                error.message || 'नेटवर्क त्रुटि। कृपया पुनः प्रयास करें।',
                'danger'
            );

        } finally {

            /*
            |--------------------------------------------------------------------------
            | Reset Button
            |--------------------------------------------------------------------------
            */
            submitButton.disabled = false;

            submitButton.innerHTML = `
                <i class="fas fa-key me-1"></i>
                पासवर्ड अपडेट करें
            `;
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Password Toggle
    |--------------------------------------------------------------------------
    */
    toggleButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const targetId = button.getAttribute('data-target');

            const input = document.getElementById(targetId);

            if (!input) return;

            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';

            button.innerHTML = isPassword
                ? '<i class="fas fa-eye-slash"></i>'
                : '<i class="fas fa-eye"></i>';
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Alert Function
    |--------------------------------------------------------------------------
    */
    function showAlert(message, type) {

        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }
});

/*
|--------------------------------------------------------------------------
| Logout Function
|--------------------------------------------------------------------------
*/
function logoutMember() {

    if (!confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
        return;
    }

    fetch('../api/logout.php', {

        method: 'POST',

        credentials: 'same-origin'

    })
    .then(response => response.json())

    .then(data => {

        if (data.success) {

            window.location.href = data.redirect;

        } else {

            alert(data.message || 'लॉगआउट विफल');
        }
    })

    .catch(error => {

        console.error('Logout Error:', error);

        alert('लॉगआउट में त्रुटि हुई।');
    });
}