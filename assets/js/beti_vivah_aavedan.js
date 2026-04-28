// Beti Vivah Aavedan JavaScript

// Member Verification Function (for non-logged-in users)
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
        console.error('Error:', error);
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
            console.error('IFSC lookup error:', error);
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

// Validate Wedding Date (must be future)
function validateWeddingDate() {
    const weddingDate = new Date(document.getElementById('wedding_date').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (weddingDate <= today) {
        alert('विवाह की तारीख आज से आगे की होनी चाहिए');
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

// Validate Income (non-negative)
function validateFamilyIncome() {
    const income = parseInt(document.getElementById('family_income').value);
    if (isNaN(income) || income < 0) {
        alert('वार्षिक पारिवारिक आय नकारात्मक नहीं हो सकती');
        return false;
    }
    return true;
}

// Validate Family Members (>= 1)
function validateFamilyMembers() {
    const members = parseInt(document.getElementById('family_members').value);
    if (isNaN(members) || members < 1) {
        alert('परिवार के सदस्यों की संख्या कम से कम 1 होनी चाहिए');
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
        { fn: validateFamilyIncome, name: 'पारिवारिक आय' },
        { fn: validateFamilyMembers, name: 'परिवार के सदस्य' },
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
    document.getElementById('family_income').addEventListener('blur', validateFamilyIncome);
    document.getElementById('family_members').addEventListener('blur', validateFamilyMembers);
    document.getElementById('upi_id').addEventListener('blur', validateUPI);
    document.getElementById('ifsc_code').addEventListener('blur', validateIFSC);
}

// ========== END VALIDATION FUNCTIONS ==========

function showTab(tabId) {
    const tab = new bootstrap.Tab(document.getElementById(tabId));
    tab.show();
}

function updatePreview() {
    document.getElementById('preview_member_name').textContent = document.getElementById('member_name').value || '-';
    document.getElementById('preview_member_id').textContent = document.getElementById('member_id_display').value || '-';
    document.getElementById('preview_bride_name').textContent = document.getElementById('bride_name').value || '-';
    document.getElementById('preview_bride_dob').textContent = document.getElementById('bride_dob').value || '-';
    document.getElementById('preview_bride_health').textContent = document.getElementById('bride_health').value || '-';
    document.getElementById('preview_groom_name').textContent = document.getElementById('groom_name').value || '-';
    document.getElementById('preview_groom_dob').textContent = document.getElementById('groom_dob').value || '-';
    document.getElementById('preview_groom_occupation').textContent = document.getElementById('groom_occupation').value || '-';
    document.getElementById('preview_wedding_date').textContent = document.getElementById('wedding_date').value || '-';
    document.getElementById('preview_family_income').textContent = '₹' + (document.getElementById('family_income').value || '0');
    document.getElementById('preview_family_members').textContent = document.getElementById('family_members').value || '-';
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
            'bride_name', 'bride_dob', 'bride_health',
            'groom_name', 'groom_dob', 'groom_occupation', 'groom_father_name',
            'wedding_date',
            'family_income', 'family_members', 'member_address',
            'ifsc_code', 'bank_name', 'branch_name', 'account_number', 'account_holder_name'
        ];

        console.log('=== FORM DATA DEBUG ===');
        const missingInForm = [];
        requiredFields.forEach(field => {
            const elem = document.getElementById(field);
            if (elem) {
                const value = elem.value;
                console.log(`${field}: ${value ? '✓ ' + value.substring(0, 20) : '✗ EMPTY'}`);
                if (!value) {
                    missingInForm.push(field);
                }
            } else {
                console.log(`${field}: ✗ NOT FOUND IN DOM`);
                missingInForm.push(field);
            }
        });
        
        if (missingInForm.length > 0) {
            console.warn('Missing fields:', missingInForm);
        }
        console.log('======================');
    }

    // Handle form submission
    const form = document.getElementById('beti-vivah-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!document.getElementById('confirm_details').checked) {
                alert('कृपया सभी शर्तों को स्वीकार करें।');
                return;
            }

            // Debug log
            logFormData();

            // ===== VALIDATION CHECK =====
            if (!validateAllFields()) {
                return;
            }
            // =============================

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
            console.log('=== FORMDATA BEING SENT ===');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`${key}: File(${value.name}, ${value.size} bytes)`);
                } else {
                    console.log(`${key}: ${value}`);
                }
            }
            console.log('===========================');

            $.ajax({
                url: '../api/beti_vivah_aavedan.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    console.log('API Success Response:', response);
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
                    console.error('AJAX Error:', xhr);
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
                    console.error('Error:', error);
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
