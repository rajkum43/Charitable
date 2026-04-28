// Tab Navigation Functions
function nextTab(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
    
    // Wait for tab animation to complete, then scroll to the active tab content
    setTimeout(() => {
        const activeTabPane = document.querySelector('.tab-pane.active');
        if (activeTabPane) {
            // Scroll to the tab pane content with offset
            const tabOffset = activeTabPane.offsetTop - 150;
            window.scrollTo({
                top: tabOffset,
                behavior: 'smooth'
            });
        }
    }, 150);
}

function prevTab(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
    
    // Wait for tab animation to complete, then scroll to the active tab content
    setTimeout(() => {
        const activeTabPane = document.querySelector('.tab-pane.active');
        if (activeTabPane) {
            // Scroll to the tab pane content with offset
            const tabOffset = activeTabPane.offsetTop - 150;
            window.scrollTo({
                top: tabOffset,
                behavior: 'smooth'
            });
        }
    }, 150);
}

// Copy to Clipboard Function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>कॉपी हुआ!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(() => {
        alert('कॉपी नहीं हो सका।');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const aadharInput = document.getElementById('aadharNumber');
    const loginIdDisplay = document.getElementById('loginIdDisplay');
    const loginIdDisplay2 = document.getElementById('loginIdDisplay2');

    if (!form) {
        return;
    }

    // Form validation object
    const validation = {
        paymentConfirm: {
            validate: (value) => value === true,
            error: 'पहले भुगतान की पुष्टि करें'
        },
        utrNumber: {
            validate: (value) => /^[A-Z0-9]{12,22}$/.test(value.toUpperCase().replace(/\s/g, '')),
            error: 'वैध UTR नंबर दर्ज करें (12-22 वर्णाक्षर)'
        },
        fullName: {
            validate: (value) => value.trim().length >= 3,
            error: 'नाम कम से कम 3 वर्ण होना चाहिए'
        },
        aadharNumber: {
            validate: (value) => /^\d{12}$/.test(value.replace(/\s/g, '')),
            error: 'आधार संख्या 12 अंकों की होनी चाहिए'
        },
        fatherName: {
            validate: (value) => value.trim().length >= 2,
            error: 'पिता/पति का नाम कम से कम 2 वर्ण होना चाहिए'
        },
        dob: {
            validate: (value) => {
                if (!value) return false;
                const date = new Date(value);
                const today = new Date();
                return date < today && date.getFullYear() >= 1950;
            },
            error: 'वैध तारीख भरें (1950 के बाद की, आज से पहले की)'
        },
        mobile: {
            validate: (value) => /^[6-9]\d{9}$/.test(value.replace(/\s/g, '')),
            error: 'वैध 10 अंकीय मोबाइल नंबर भरें'
        },
        password: {
            validate: (value) => value.length >= 6,
            error: 'पासवर्ड कम से कम 6 वर्ण होना चाहिए'
        },
        state: {
            validate: (value) => {
                // Check if manual state field is visible
                const manualField = document.getElementById('manualState');
                if (manualField && manualField.style.display !== 'none') {
                    // Validate manual input
                    const manualValue = document.getElementById('manualStateName').value;
                    return manualValue.trim().length > 0;
                }
                // Otherwise validate dropdown
                return value.trim().length > 0;
            },
            error: 'राज्य दर्ज करें या चुनें'
        },
        district: {
            validate: (value) => {
                // Check if manual field is visible
                const manualField = document.getElementById('manualDistrictField');
                if (manualField && manualField.style.display !== 'none') {
                    // Validate manual input
                    const manualValue = document.getElementById('manualDistrict').value;
                    return manualValue.trim().length > 0;
                }
                // Otherwise validate dropdown
                return value.trim().length > 0;
            },
            error: 'जिला दर्ज करें या चुनें'
        },
        block: {
            validate: (value) => {
                // Check if manual field is visible
                const manualField = document.getElementById('manualBlockField');
                if (manualField && manualField.style.display !== 'none') {
                    // Validate manual input
                    const manualValue = document.getElementById('manualBlock').value;
                    return manualValue.trim().length > 0;
                }
                // Otherwise validate dropdown
                return value.trim().length > 0;
            },
            error: 'ब्लॉक दर्ज करें या चुनें'
        },
        terms: {
            validate: (value) => value === true,
            error: 'नियम और शर्तों को स्वीकार करें'
        },
        // NEW: Nominee field validations
        nomineeName: {
            validate: (value) => value.trim().length >= 3,
            error: 'नामांकित व्यक्ति का नाम कम से कम 3 वर्ण होना चाहिए'
        },
        nomineeRelation: {
            validate: (value) => value.trim().length > 0,
            error: 'नामांकित व्यक्ति का संबंध दर्ज करें'
        },
        nomineeMobile: {
            validate: (value) => {
                // Optional field - empty is fine
                if (!value || value.trim().length === 0) return true;
                // If provided, must be 10 digits starting with 6-9
                return /^[6-9]\d{9}$/.test(value.replace(/\s/g, ''));
            },
            error: 'नामांकित के लिए वैध 10 अंकीय मोबाइल नंबर भरें'
        },
        nomineeAadhar: {
            validate: (value) => {
                // Optional field - empty is fine
                if (!value || value.trim().length === 0) return true;
                // If provided, must be 12 digits
                return /^\d{12}$/.test(value.replace(/\s/g, ''));
            },
            error: 'आधार संख्या 12 अंकों की होनी चाहिए'
        },
        referrerMemberId: {
            validate: (value) => {
                // Optional field - empty is fine
                if (!value || value.trim().length === 0) return true;
                // If provided, must be numeric
                return /^\d{1,8}$/.test(value.replace(/\s/g, ''));
            },
            error: 'वैध Member ID दर्ज करें'
        }
    };

    // Clear error message
    function clearError(fieldId) {
        const errorElement = document.getElementById(fieldId + 'Error');
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
    }

    // Show error message
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + 'Error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            errorElement.style.color = '#dc3545';
            errorElement.style.fontSize = '0.85rem';
            errorElement.style.marginTop = '5px';
        }
    }

    // Update Login ID display based on Aadhar
    if (aadharInput) {
        aadharInput.addEventListener('input', function() {
            const aadhar = this.value.replace(/\s/g, '');
            if (aadhar.length === 12) {
                const loginId = aadhar.slice(-8);
                if (loginIdDisplay) {
                    loginIdDisplay.textContent = loginId;
                    loginIdDisplay.style.fontWeight = 'bold';
                    loginIdDisplay.style.color = '#0d6efd';
                }
                if (loginIdDisplay2) {
                    loginIdDisplay2.textContent = loginId;
                    loginIdDisplay2.style.fontWeight = 'bold';
                    loginIdDisplay2.style.color = '#0d6efd';
                }
            } else {
                if (loginIdDisplay) {
                    loginIdDisplay.textContent = 'आधार के अंतिम 8 अंक';
                    loginIdDisplay.style.color = '#999';
                    loginIdDisplay.style.fontWeight = 'normal';
                }
                if (loginIdDisplay2) {
                    loginIdDisplay2.textContent = 'आधार के अंतिम 8 अंक';
                    loginIdDisplay2.style.color = '#999';
                    loginIdDisplay2.style.fontWeight = 'normal';
                }
            }
        });
    }

    // Real-time validation
    Object.keys(validation).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', function() {
                const value = fieldId === 'terms' || fieldId === 'paymentConfirm' ? this.checked : this.value;
                if (validation[fieldId].validate(value)) {
                    clearError(fieldId);
                    this.style.borderColor = '#28a745';
                } else {
                    showError(fieldId, validation[fieldId].error);
                    this.style.borderColor = '#dc3545';
                }
            });

            field.addEventListener('focus', function() {
                this.style.borderColor = '#0d6efd';
                clearError(fieldId);
            });

            if (fieldId !== 'terms' && fieldId !== 'paymentConfirm') {
                field.addEventListener('input', function() {
                    this.style.borderColor = '#e9ecef';
                });
            }
        }
    });

    // Manual input fields validation
    const manualStateInput = document.getElementById('manualStateName');
    const manualDistrictInput = document.getElementById('manualDistrict');
    const manualBlockInput = document.getElementById('manualBlock');

    if (manualStateInput) {
        manualStateInput.addEventListener('blur', function() {
            if (this.value.trim().length > 0) {
                clearError('state');
                this.style.borderColor = '#28a745';
            } else {
                showError('state', validation['state'].error);
                this.style.borderColor = '#dc3545';
            }
        });

        manualStateInput.addEventListener('focus', function() {
            this.style.borderColor = '#0d6efd';
            clearError('state');
        });

        manualStateInput.addEventListener('input', function() {
            this.style.borderColor = '#e9ecef';
        });
    }

    if (manualDistrictInput) {
        manualDistrictInput.addEventListener('blur', function() {
            if (this.value.trim().length > 0) {
                clearError('district');
                this.style.borderColor = '#28a745';
            } else {
                showError('district', validation['district'].error);
                this.style.borderColor = '#dc3545';
            }
        });

        manualDistrictInput.addEventListener('focus', function() {
            this.style.borderColor = '#0d6efd';
            clearError('district');
        });

        manualDistrictInput.addEventListener('input', function() {
            this.style.borderColor = '#e9ecef';
        });
    }

    if (manualBlockInput) {
        manualBlockInput.addEventListener('blur', function() {
            if (this.value.trim().length > 0) {
                clearError('block');
                this.style.borderColor = '#28a745';
            } else {
                showError('block', validation['block'].error);
                this.style.borderColor = '#dc3545';
            }
        });

        manualBlockInput.addEventListener('focus', function() {
            this.style.borderColor = '#0d6efd';
            clearError('block');
        });

        manualBlockInput.addEventListener('input', function() {
            this.style.borderColor = '#e9ecef';
        });
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate all fields
            let isValid = true;
            Object.keys(validation).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    const value = fieldId === 'terms' || fieldId === 'paymentConfirm' ? field.checked : field.value;
                    if (!validation[fieldId].validate(value)) {
                        showError(fieldId, validation[fieldId].error);
                        isValid = false;
                    } else {
                        clearError(fieldId);
                    }
                }
            });

            if (!isValid) {
                scrollToFirstError();
                return;
            }

            // Submit form via AJAX
            submitForm();
        });
    }

    // Scroll to first error
    function scrollToFirstError() {
        const errorElement = document.querySelector('[id$="Error"]');
        if (errorElement && errorElement.style.display !== 'none') {
            errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Submit form via AJAX
    function submitForm() {
        const formData = new FormData(form);
        let originalText = '';
        
        // Handle manual state entry
        const manualStateField = document.getElementById('manualState');
        if (manualStateField && manualStateField.style.display !== 'none') {
            const manualState = document.getElementById('manualStateName').value;
            formData.set('state', manualState);
        }

        // Handle manual district entry
        const manualDistrictField = document.getElementById('manualDistrictField');
        if (manualDistrictField && manualDistrictField.style.display !== 'none') {
            const manualDistrict = document.getElementById('manualDistrict').value;
            formData.set('district', manualDistrict);
        }

        // Handle manual block entry
        const manualBlockField = document.getElementById('manualBlockField');
        if (manualBlockField && manualBlockField.style.display !== 'none') {
            const manualBlock = document.getElementById('manualBlock').value;
            formData.set('block', manualBlock);
        }

        // Disable submit button
        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> भेज रहे हैं...';
        }

        // Make AJAX request
        fetch('../api/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Always get the response text first
            return response.text().then(text => ({
                status: response.status,
                ok: response.ok,
                text: text
            }));
        })
        .then(({status, ok, text}) => {
            // Try to parse JSON
            if (!text) {
                throw new Error('सर्वर से कोई प्रतिक्रिया नहीं मिली');
            }
            
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                throw new Error('सर्वर की प्रतिक्रिया बिगड़ी हुई है। कृपया दोबारा प्रयास करें।');
            }
        })
        .then(data => {
            if (data.success) {
                // Show success modal with receipt info
                form.reset();
                aadharInput.value = '';
                if (loginIdDisplay) {
                    loginIdDisplay.textContent = 'आधार के अंतिम 8 अंक';
                }
                if (loginIdDisplay2) {
                    loginIdDisplay2.textContent = 'आधार के अंतिम 8 अंक';
                }
                
                // Show success modal with receipt info
                if (data.data) {
                    showSuccessModal(data.data);
                } else {
                    showSuccessAlert(data.message);
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2500);
                }
            } else {
                // Handle field-specific errors
                if (data.errors && Object.keys(data.errors).length > 0) {
                    // Switch to the first error tab
                    if (data.firstErrorTab) {
                        switchToTab(data.firstErrorTab);
                    }
                    
                    showErrorsModal(data.errors);
                    
                    // Add field error styling
                    Object.keys(data.errors).forEach(fieldId => {
                        showError(fieldId, data.errors[fieldId].message || data.errors[fieldId]);
                    });
                    
                    // Scroll to first error field
                    scrollToFirstError();
                } else {
                    // Show general error message
                    showErrorAlert(data.message || 'रजिस्ट्रेशन विफल रहा। कृपया सभी विवरण जांचकर दोबारा प्रयास करें।');
                }
            }
        })
        .catch(error => {
            showErrorAlert('📌 रजिस्ट्रेशन में त्रुटि:\n\n' + (error.message || 'कृपया दोबारा प्रयास करें।'));
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // Show error modal with details
    function showErrorsModal(errors) {
        // Create error modal
        const errorCount = Object.keys(errors).length;
        let errorHTML = '<div style="text-align: left; max-height: 300px; overflow-y: auto;">';
        
        Object.keys(errors).forEach((field, index) => {
            const fieldsMap = {
                'paymentConfirm': '💳 भुगतान की पुष्टि',
                'utrNumber': '🔢 UTR नंबर',
                'fullName': '👤 पूरा नाम',
                'aadharNumber': '🆔 आधार नंबर',
                'fatherName': '👨 पिता/पति का नाम',
                'dob': '📅 जन्म तारीख',
                'mobile': '📱 मोबाइल नंबर',
                'gender': '⚧️ लिंग',
                'occupation': '💼 व्यवसाय',
                'officeName': '🏢 कार्यालय का नाम',
                'officeAddress': '📍 कार्यालय पता',
                'nomineeName': '🤝 नामांकित व्यक्ति का नाम',
                'nomineeRelation': '👥 नामांकित का संबंध',
                'nomineeMobile': '📱 नामांकित का मोबाइल',
                'nomineeAadhar': '🆔 नामांकित का आधार',
                'state': '🗺️ राज्य',
                'district': '📦 जिला',
                'block': '🏘️ ब्लॉक',
                'permanentAddress': '🏠 स्थायी पता',
                'email': '📧 ईमेल',
                'password': '🔐 पासवर्ड',
                'terms': '✅ नियम और शर्तें',
                'referrerMemberId': '🔗 रेफरर ID',
                'paymentReceipt': '📄 भुगतान रसीद'
            };
            
            const fieldLabel = fieldsMap[field] || field;
            // Extract message from error object if it has message property
            const errorMessage = errors[field].message ? errors[field].message : errors[field];
            errorHTML += `<div style="margin-bottom: 12px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">
                <strong style="color: #856404;">${fieldLabel}:</strong>
                <p style="margin: 5px 0 0 0; color: #721c24; font-size: 13px;">${errorMessage}</p>
            </div>`;
        });
        
        errorHTML += '</div>';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'modal fade';
        alertDiv.id = 'errorModal';
        alertDiv.tabIndex = '-1';
        alertDiv.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">⚠️ रजिस्ट्रेशन त्रुटि (${errorCount} समस्या)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 15px;">कृपया निम्नलिखित त्रुटियों को ठीक करके दोबारा प्रयास करें:</p>
                        ${errorHTML}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ठीक है, मुझे समझ आ गया</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        const modal = new bootstrap.Modal(alertDiv);
        modal.show();
        
        // Remove modal from DOM after close
        alertDiv.addEventListener('hidden.bs.modal', () => {
            alertDiv.remove();
        });
    }

    // Switch to a specific tab
    function switchToTab(tabName) {
        const tabElement = document.querySelector(`[data-bs-target="#${tabName}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
            // Small delay to ensure tab is switched before scrolling
            setTimeout(() => {
                scrollToFirstError();
            }, 100);
        }
    }

    // Show success modal with receipt info
    function showSuccessModal(receiptData) {
        const memberName = receiptData.member_name || receiptData.member_id || 'User';
        const memberId = receiptData.member_id || 'N/A';
        const receiptUrl = receiptData.receipt_download ? '../api/' + receiptData.receipt_download : null;
        
        const modalDiv = document.createElement('div');
        modalDiv.className = 'modal fade';
        modalDiv.id = 'successModal';
        modalDiv.tabIndex = '-1';
        modalDiv.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-success" style="border-width: 2px;">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-check-circle me-2"></i>पंजीकरण सफल!
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div style="font-size: 50px; margin-bottom: 20px;">✅</div>
                        <h4 style="margin-bottom: 10px; color: #333; word-wrap: break-word;">नमस्ते!</h4>
                        <p style="font-size: 14px; color: #666; margin-bottom: 20px; word-wrap: break-word;">
                            <strong>आपका सदस्यता पंजीकरण आवेदन मिल चुका है।</strong><br/>
                            <small style="color: #999;">Member ID: <strong>${memberId}</strong></small>
                        </p>
                        <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 12px; border-radius: 4px; margin-bottom: 20px; text-align: left; font-size: 13px;">
                            <p style="margin: 5px 0; color: #2e7d32;">
                                ✓ आपकी राशि प्राप्त हुई है<br/>
                                ✓ 24-48 घंटों में सत्यापित की जाएगी<br/>
                                ✓ सत्यापन के बाद लॉगिन कर सकेंगे
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer" style="flex-wrap: wrap; gap: 10px; justify-content: center;">
                        ${receiptUrl ? `
                            <button type="button" class="btn btn-sm btn-success" onclick="downloadReceipt('${receiptUrl.replace(/'/g, "\\'")}')">
                                <i class="fas fa-file-pdf me-1"></i>रसीद
                            </button>
                        ` : ''}
                        <button type="button" class="btn btn-sm btn-success" data-bs-dismiss="modal" onclick="redirectToLogin()">
                            <i class="fas fa-arrow-right me-1"></i>आगे बढ़ें
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modalDiv);
        const modal = new bootstrap.Modal(modalDiv, {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
        
        // Remove modal from DOM after close
        modalDiv.addEventListener('hidden.bs.modal', () => {
            redirectToLogin();
        });
    }

    // Show success alert
    function showSuccessAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.marginBottom = '20px';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            <strong>✅ सफल!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        form.parentElement.insertBefore(alertDiv, form);
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Show error alert
    function showErrorAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.style.marginBottom = '20px';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>⚠️ त्रुटि!</strong><br/><span style="font-size: 14px;">${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        form.parentElement.insertBefore(alertDiv, form);
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Remove after 10 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 10000);
    }

    // Format Aadhar input (add spaces after every 4 digits)
    if (aadharInput) {
        aadharInput.addEventListener('input', function() {
            let value = this.value.replace(/\s/g, '');
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            const formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            this.value = formatted;
        });
    }

    // Format mobile input
    const mobileInput = document.getElementById('mobile');
    if (mobileInput) {
        mobileInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            this.value = value;
        });
    }

    // Optional: Auto-fill email if provided
    const emailInput = document.getElementById('email');
    const mobileForEmail = document.getElementById('mobile');
    if (emailInput && mobileForEmail) {
        mobileForEmail.addEventListener('change', function() {
            if (!emailInput.value && this.value) {
                emailInput.placeholder = `user${this.value}@example.com`;
            }
        });
    }
});

// Global functions - available outside DOMContentLoaded
// Download receipt as PDF
function downloadReceipt(receiptUrl) {
    // Open receipt in new window - user can click PDF download button
    window.open(receiptUrl, 'Receipt', 'width=450,height=700,menubar=no,toolbar=no');
}

// Print receipt
function printReceipt(receiptUrl) {
    const printWindow = window.open(receiptUrl, 'ReceiptPrint', 'width=800,height=900');
    if (printWindow) {
        setTimeout(() => {
            printWindow.print();
        }, 500);
    }
}

// Redirect to login page
function redirectToLogin() {
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 500);
}
