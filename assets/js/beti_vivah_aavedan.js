// Beti Vivah Aavedan JavaScript

// Initialize date constraints when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initial call
    setWeddingDateConstraints();
    
    // Call again after a short delay to ensure DOM is fully ready
    setTimeout(setWeddingDateConstraints, 500);

    // Initialize auto-capitalization for text inputs
    initializeAutoCapitalization();
});

// Auto-capitalization functions
function initializeAutoCapitalization() {
    // Text inputs that need auto-capitalization (first letter of each word)
    const capitalizeInputs = [
        'bride_name',
        'groom_name',
        'groom_father_name',
        'branch_name',
        'bank_name',
        'account_holder_name',
        'remarks'
    ];

    // Add event listeners for auto-capitalization
    capitalizeInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                this.value = capitalizeWords(this.value);
            });
            input.addEventListener('blur', function() {
                this.value = capitalizeWords(this.value);
            });
        }
    });

    // IFSC code should be uppercase
    const ifscInput = document.getElementById('ifsc_code');
    if (ifscInput) {
        ifscInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        ifscInput.addEventListener('blur', function() {
            this.value = this.value.toUpperCase();
        });
    }
}

// Function to capitalize first letter of each word
function capitalizeWords(str) {
    return str.replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });
}

// Also set constraints when the form becomes visible
document.addEventListener('shown.bs.tab', function(e) {
    if (e.target && e.target.getAttribute('data-bs-target') === '#couple') {
        setWeddingDateConstraints();
    }
});

// Set wedding date min and max constraints (1 year before and 1 year after today)
function setWeddingDateConstraints() {
    const weddingDateInput = document.getElementById('wedding_date');
    if (!weddingDateInput) {
        return;
    }

    const today = new Date();
    
    // Calculate 1 year before today
    const minDate = new Date(today);
    minDate.setFullYear(minDate.getFullYear() - 1);
    
    // Calculate 1 year after today
    const maxDate = new Date(today);
    maxDate.setFullYear(maxDate.getFullYear() + 1);
    
    // Format dates as YYYY-MM-DD for HTML5 date input
    const minDateStr = formatDateForInput(minDate);
    const maxDateStr = formatDateForInput(maxDate);
    
    // Set min and max attributes
    weddingDateInput.setAttribute('min', minDateStr);
    weddingDateInput.setAttribute('max', maxDateStr);
    
    // Also update the title for better UX
    weddingDateInput.setAttribute('title', `Please select a date between ${minDateStr} and ${maxDateStr}`);
}

// Helper function to format date as YYYY-MM-DD
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
function verifyMember() {
    const memberId = document.getElementById('search_member_id').value.trim();
    
    if (!memberId) {
        document.getElementById('verification-error').textContent = 'कृपया सदस्य ID दर्ज करें';
        document.getElementById('verification-error').style.display = 'block';
        return;
    }

    document.getElementById('verification-loader').style.display = 'block';
    document.getElementById('verification-error').style.display = 'none';
    document.getElementById('verification-success').style.display = 'none';

    // API call to fetch member details
    fetch('../api/get_member_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ member_id: memberId })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('verification-loader').style.display = 'none';
        
        if (data.success) {
            // Set member data
            document.getElementById('hidden_member_id').value = data.data.member_id;
            
            // Load member details into form
            loadMemberDetailsData(data.data);
            
            // Hide verification form and show main form
            document.getElementById('verification-form').style.display = 'none';
            document.getElementById('main-form').style.display = 'block';
            
            // Scroll to form
            setTimeout(() => {
                document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
            }, 300);
        } else {
            document.getElementById('verification-error').textContent = data.message || 'सदस्य नहीं मिला। कृपया सदस्य ID जांचें।';
            document.getElementById('verification-error').style.display = 'block';
        }
    })
    .catch(error => {
        document.getElementById('verification-loader').style.display = 'none';
        document.getElementById('verification-error').textContent = 'नेटवर्क त्रुटि: ' + error.message;
        document.getElementById('verification-error').style.display = 'block';
    });
}

// Load member details data
function loadMemberDetailsData(member) {
    document.getElementById('member_name').value = member.full_name || '';
    document.getElementById('member_id_display').value = member.member_id || '';
    document.getElementById('member_mobile').value = member.mobile_number || '';
    document.getElementById('member_email').value = member.email || '';
    document.getElementById('member_dob').value = member.dob || '';
    document.getElementById('member_address').value = member.address || '';
}

// IFSC Code lookup function
function fetchBankDetails() {
    const ifscCode = document.getElementById('ifsc_code').value.trim().toUpperCase();
    
    if (!ifscCode) {
        document.getElementById('bank_name').value = '';
        document.getElementById('branch_name').value = '';
        return;
    }

    // Validate IFSC format (standard format: ABCD0123456)
    if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscCode)) {
        return;
    }

    // Use free Razorpay IFSC API
    fetch('https://ifsc.razorpay.com/' + ifscCode)
        .then(response => {
            if (!response.ok) {
                throw new Error('IFSC not found');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.BANK && data.BRANCH) {
                document.getElementById('bank_name').value = data.BANK;
                document.getElementById('branch_name').value = data.BRANCH;
            }
        })
        .catch(error => {
        });
}

// ========== VALIDATION FUNCTIONS ==========

// Calculate age from date of birth
function calculateAge(dob) {
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

// Validate Bride Age (>= 18)
function validateBrideAge() {
    const brideDob = document.getElementById('bride_dob').value;
    if (!brideDob) return true;
    
    const age = calculateAge(brideDob);
    if (age < 18) {
        alert('दुल्हन की आयु 18 वर्ष या उससे अधिक होनी चाहिए');
        return false;
    }
    return true;
}

// Validate Groom Age (>= 18)
function validateGroomAge() {
    const groomDob = document.getElementById('groom_dob').value;
    if (!groomDob) return true;
    
    const age = calculateAge(groomDob);
    if (age < 18) {
        alert('दूल्हे की आयु 18 वर्ष या उससे अधिक होनी चाहिए');
        return false;
    }
    return true;
}

// Validate Wedding Date (must be within 1 year before and 1 year after today)
function validateWeddingDate() {
    const weddingDate = new Date(document.getElementById('wedding_date').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Calculate 1 year before today
    const oneYearBefore = new Date(today);
    oneYearBefore.setFullYear(oneYearBefore.getFullYear() - 1);
    
    // Calculate 1 year after today
    const oneYearAfter = new Date(today);
    oneYearAfter.setFullYear(oneYearAfter.getFullYear() + 1);
    
    if (weddingDate < oneYearBefore || weddingDate > oneYearAfter) {
        alert('विवाह की तारीख आज से 1 साल पहले या 1 साल बाद तक हो सकती है');
        return false;
    }
    return true;
}

// Validate Account Number Format (9-18 digits)
function validateAccountNumber() {
    const accountNo = document.getElementById('account_number').value.trim();
    if (!accountNo) return true;
    
    if (!/^\d{9,18}$/.test(accountNo)) {
        alert('खाता संख्या 9 से 18 अंकों के बीच होनी चाहिए');
        return false;
    }
    return true;
}

// Validate UPI ID Format (if provided)
function validateUPI() {
    const upiId = document.getElementById('upi_id').value.trim();
    if (!upiId) return true;
    
    if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+$/.test(upiId)) {
        alert('UPI ID का प्रारूप अमान्य है। उदाहरण: name@okhdfcbank');
        return false;
    }
    return true;
}

// Validate IFSC Code Format
function validateIFSC() {
    const ifscCode = document.getElementById('ifsc_code').value.trim().toUpperCase();
    if (!ifscCode) return true;
    
    if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscCode)) {
        alert('IFSC कोड का प्रारूप अमान्य है। उदाहरण: SBIN0001234');
        return false;
    }
    return true;
}

// Master validation function for all form fields
function validateAllFields() {
    const validations = [
        { fn: validateBrideAge, name: 'दुल्हन की आयु' },
        { fn: validateGroomAge, name: 'दूल्हे की आयु' },
        { fn: validateWeddingDate, name: 'विवाह की तारीख' },
        { fn: validateAccountNumber, name: 'खाता संख्या' },
        { fn: validateUPI, name: 'UPI ID' },
        { fn: validateIFSC, name: 'IFSC कोड' }
    ];

    for (let validation of validations) {
        if (!validation.fn()) {
            return false;
        }
    }
    return true;
}

// Add event listeners for real-time validation
function setupValidationListeners() {
    document.getElementById('bride_dob').addEventListener('change', validateBrideAge);
    document.getElementById('groom_dob').addEventListener('change', validateGroomAge);
    document.getElementById('wedding_date').addEventListener('change', validateWeddingDate);
    document.getElementById('account_number').addEventListener('blur', validateAccountNumber);
    document.getElementById('upi_id').addEventListener('blur', validateUPI);
    document.getElementById('ifsc_code').addEventListener('blur', validateIFSC);
}

// Comprehensive form validation with tab switching
function validateFormAndNavigate() {
    const fieldTabMapping = {
        // User Details Tab
        'member_name': 'userdetails-tab',
        'member_id_display': 'userdetails-tab',
        'member_mobile': 'userdetails-tab',
        'member_address': 'userdetails-tab',
        
        // Couple Tab
        'bride_name': 'couple-tab',
        'bride_dob': 'couple-tab',
        'groom_name': 'couple-tab',
        'groom_dob': 'couple-tab',
        'groom_father_name': 'couple-tab',
        'wedding_date': 'couple-tab',
        
        // Family Tab
        'ifsc_code': 'family-tab',
        'bank_name': 'family-tab',
        'branch_name': 'family-tab',
        'account_number': 'family-tab',
        'account_holder_name': 'family-tab',
        
        // Documents Tab
        'marriage_certificate': 'documents-tab',
        'confirm_details': 'documents-tab'
    };

    const requiredFields = [
        'member_name', 'member_id_display', 'member_mobile', 'member_address',
        'bride_name', 'bride_dob', 'groom_name', 'groom_dob', 'groom_father_name', 'wedding_date',
        'ifsc_code', 'bank_name', 'branch_name', 'account_number', 'account_holder_name',
        'marriage_certificate'
    ];

    // Check each required field
    for (const field of requiredFields) {
        const elem = document.getElementById(field);
        if (!elem || !elem.value.trim()) {
            // Switch to the appropriate tab
            const tabId = fieldTabMapping[field];
            if (tabId) {
                const tab = new bootstrap.Tab(document.getElementById(tabId));
                tab.show();
                
                // Focus on the empty field after a short delay to ensure tab is visible
                setTimeout(() => {
                    if (elem) {
                        elem.focus();
                        elem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 300);
            }
            
            // Show appropriate error message
            const fieldNames = {
                'member_name': 'सदस्य का नाम',
                'member_id_display': 'सदस्य ID',
                'member_mobile': 'मोबाइल नंबर',
                'member_address': 'पता',
                'bride_name': 'दुल्हन का नाम',
                'bride_dob': 'दुल्हन की जन्म तिथि',
                'groom_name': 'दूल्हे का नाम',
                'groom_dob': 'दूल्हे की जन्म तिथि',
                'groom_father_name': 'दूल्हे के पिता का नाम',
                'wedding_date': 'विवाह की तारीख',
                'ifsc_code': 'IFSC कोड',
                'bank_name': 'बैंक का नाम',
                'branch_name': 'शाखा का नाम',
                'account_number': 'खाता संख्या',
                'account_holder_name': 'खाता धारक का नाम',
                'marriage_certificate': 'विवाह कार्ड/निमंत्रण'
            };
            
            alert(`कृपया ${fieldNames[field] || field} भरें।`);
            return false;
        }
    }

    // Check confirm_details checkbox
    const confirmDetails = document.getElementById('confirm_details');
    if (!confirmDetails.checked) {
        const tab = new bootstrap.Tab(document.getElementById('documents-tab'));
        tab.show();
        
        setTimeout(() => {
            confirmDetails.focus();
            confirmDetails.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
        
        alert('कृपया सभी शर्तों को स्वीकार करें।');
        return false;
    }

    return true;
}

function showTab(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
}

function updatePreview() {
    document.getElementById('preview_member_name').textContent = document.getElementById('member_name').value || '-';
    document.getElementById('preview_member_id').textContent = document.getElementById('member_id_display').value || '-';
    document.getElementById('preview_bride_name').textContent = document.getElementById('bride_name').value || '-';
    document.getElementById('preview_bride_dob').textContent = document.getElementById('bride_dob').value || '-';
    document.getElementById('preview_groom_name').textContent = document.getElementById('groom_name').value || '-';
    document.getElementById('preview_groom_dob').textContent = document.getElementById('groom_dob').value || '-';
    document.getElementById('preview_wedding_date').textContent = document.getElementById('wedding_date').value || '-';
    document.getElementById('preview_account_number').textContent = '**** **** **** ' + (document.getElementById('account_number').value.slice(-4) || '****');
}

// Handle file upload
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const nameSpan = document.getElementById(this.id + '_name') || null;
        if (nameSpan && this.files.length > 0) {
            nameSpan.style.display = 'block';
            nameSpan.querySelector('span').textContent = this.files[0].name;
        }
    });
});

// Allow Enter key on member search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_member_id');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyMember();
            }
        });
    }

    // Add IFSC change event listener
    const ifscInput = document.getElementById('ifsc_code');
    if (ifscInput) {
        ifscInput.addEventListener('change', fetchBankDetails);
        ifscInput.addEventListener('blur', fetchBankDetails);
    }

    // Setup validation listeners
    setupValidationListeners();
});

$(document).ready(function() {
    const memberId = document.getElementById('hidden_member_id').value;

    // Load member details on page load (if logged in)
    function loadMemberDetails() {
        if (!memberId) return;
        
        $.ajax({
            url: '../api/get_member_details.php',
            type: 'POST',
            data: JSON.stringify({ member_id: memberId }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    const member = response.data;
                    loadMemberDetailsData(member);
                    updatePreview();
                }
            }
        });
    }

    loadMemberDetails();

    // Update preview when switching to review tab
    const reviewTab = document.getElementById('review-tab');
    if (reviewTab) {
        reviewTab.addEventListener('click', function() {
            updatePreview();
        });
    }

    // Debug: Log form data before submission
    function logFormData() {
        const requiredFields = [
            'member_name', 'member_id_display',
            'bride_name', 'bride_dob',
            'groom_name', 'groom_dob', 'groom_father_name',
            'wedding_date',
            'member_address',
            'ifsc_code', 'bank_name', 'branch_name', 'account_number', 'account_holder_name'
        ];

        const missingInForm = [];
        requiredFields.forEach(field => {
            const elem = document.getElementById(field);
            if (elem) {
                const value = elem.value;
                if (!value) {
                    missingInForm.push(field);
                }
            } else {
                missingInForm.push(field);
            }
        });
        
        if (missingInForm.length > 0) {
            // Missing fields warning removed for production
        }
    }

    // Handle form submission
    const form = document.getElementById('beti-vivah-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Comprehensive validation with tab navigation
            if (!validateFormAndNavigate()) {
                return;
            }

            // Additional field validations
            if (!validateAllFields()) {
                return;
            }

            const loader = $('#loader');
            const submitBtn = $('#submit-btn');
            loader.show();
            submitBtn.prop('disabled', true);

            const formData = new FormData(this);
            if (memberId) {
                formData.append('member_id', memberId);
            }
            formData.append('action', 'submit_application');

            // Debug: Log FormData being sent
            for (let [key, value] of formData.entries()) {
                // Logging removed for production
            }

            $.ajax({
                url: '../api/beti_vivah_aavedan.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const successMsg = $('#success-message');
                        document.getElementById('application-number').textContent = (response.data && response.data.application_number) ? response.data.application_number : 'N/A';
                        successMsg.show();
                        form.reset();
                        setTimeout(() => {
                            window.location.href = '/member/application_status.php';
                        }, 3000);
                    } else {
                        const errorMsg = $('#error-message');
                        document.getElementById('error-text').textContent = response.message || 'अज्ञात त्रुटि';
                        errorMsg.show();
                        submitBtn.prop('disabled', false);
                        loader.hide();
                    }
                },
                error: function(xhr, status, error) {
                    const errorMsg = $('#error-message');
                    let errorText = 'आवेदन जमा नहीं हो सका। कृपया पुनः प्रयास करें।';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorText = response.message;
                        }
                    } catch(e) {
                        if (xhr.status === 400) {
                            errorText = 'कृपया सभी आवश्यक फील्ड सही तरीके से भरें।';
                        } else if (xhr.status === 409) {
                            errorText = 'आपका आवेदन पहले से लंबित है।';
                        }
                    }
                    
                    document.getElementById('error-text').textContent = errorText;
                    errorMsg.show();
                    submitBtn.prop('disabled', false);
                    loader.hide();
                }
            });
        });
    }

    // Date validation - don't allow past dates
    const weddingDateInput = document.getElementById('wedding_date');
    if (weddingDateInput) {
        const today = new Date().toISOString().split('T')[0];
        weddingDateInput.setAttribute('min', today);
    }
});
