
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Password Toggle Functionality
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');

        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePasswordBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                togglePasswordBtn.title = 'पासवर्ड छुपाएं';
            } else {
                passwordInput.type = 'password';
                togglePasswordBtn.innerHTML = '<i class="fas fa-eye"></i>';
                togglePasswordBtn.title = 'पासवर्ड दिखाएं';
            }
        });

        // Login ID Validation - Only digits, max 8
        const loginIdInput = document.getElementById('loginId');
        loginIdInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
            
            // Real-time validation
            if (this.value.length === 8) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                if (this.value.length > 0) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            }
        });

        // Password Strength Indicator
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const passwordStrength = document.getElementById('passwordStrength');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0) {
                passwordStrength.style.display = 'block';
                
                // Calculate strength
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 25;
                else if (password.length >= 6) strength += 15;
                
                // Contains number
                if (/\d/.test(password)) strength += 25;
                
                // Contains lowercase
                if (/[a-z]/.test(password)) strength += 25;
                
                // Contains uppercase
                if (/[A-Z]/.test(password)) strength += 25;
                
                // Contains special character
                if (/[!@#$%^&*]/.test(password)) strength += 25;
                
                // Cap at 100
                strength = Math.min(strength, 100);
                
                // Update bar
                strengthBar.style.width = strength + '%';
                
                // Update color and text
                if (strength < 30) {
                    strengthBar.style.background = '#ef4444';
                    strengthText.textContent = 'कमजोर पासवर्ड';
                    strengthText.style.color = '#ef4444';
                } else if (strength < 60) {
                    strengthBar.style.background = '#f59e0b';
                    strengthText.textContent = 'मध्यम पासवर्ड';
                    strengthText.style.color = '#f59e0b';
                } else {
                    strengthBar.style.background = '#10b981';
                    strengthText.textContent = 'मजबूत पासवर्ड';
                    strengthText.style.color = '#10b981';
                }
            } else {
                passwordStrength.style.display = 'none';
            }
        });

        // Form Submission with Loading State
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        loginForm.addEventListener('submit', function(e) {
            const loginId = loginIdInput.value.trim();
            const password = passwordInput.value;

            // Validate Login ID
            if (!/^\d{8}$/.test(loginId)) {
                e.preventDefault();
                loginIdInput.classList.add('is-invalid');
                
                // Show error toast or alert
                showNotification('Login ID 8 अंकों की होनी चाहिए', 'error');
                return;
            }

            // Validate Password
            if (password.length < 6) {
                e.preventDefault();
                passwordInput.classList.add('is-invalid');
                
                // Show error toast or alert
                showNotification('पासवर्ड कम से कम 6 वर्ण का होना चाहिए', 'error');
                return;
            }

            // Add loading state
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
        });

        // Custom Notification Function
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.style.animation = 'slideInDown 0.3s ease';
            
            notification.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Add fadeOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
        `;
        document.head.appendChild(style);

        // Focus effects
        const formControls = document.querySelectorAll('.form-control');
        formControls.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-floating-custom').classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-floating-custom').classList.remove('focused');
            });
        });

        // Add ripple effect to buttons
        const buttons = document.querySelectorAll('.btn-login, .btn-register');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);
                
                const x = e.clientX - e.target.offsetLeft;
                const y = e.clientY - e.target.offsetTop;
                
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple styles
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            .btn-login, .btn-register {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + L to focus login ID
            if (e.ctrlKey && e.key === 'l') {
                e.preventDefault();
                loginIdInput.focus();
            }
            
            // Ctrl + P to focus password
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                passwordInput.focus();
            }
        });

        // Remember Me - Local Storage
        const rememberMeCheckbox = document.getElementById('rememberMe');
        
        // Check if remember me was previously checked
        if (localStorage.getItem('rememberedLoginId')) {
            loginIdInput.value = localStorage.getItem('rememberedLoginId');
            rememberMeCheckbox.checked = true;
        }
        
        // Save to local storage when form submits
        loginForm.addEventListener('submit', function() {
            if (rememberMeCheckbox.checked) {
                localStorage.setItem('rememberedLoginId', loginIdInput.value);
            } else {
                localStorage.removeItem('rememberedLoginId');
            }
        });