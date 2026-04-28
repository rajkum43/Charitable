// Death Aavedan JavaScript

// Member Verification Function (for non-logged-in users)
function verifyMember() {
    const memberId = document.getElementById('search_member_id').value.trim();
    
    console.log('Verifying member ID:', memberId);
    
    if (!memberId) {
        document.getElementById('verification-error').textContent = 'कृपया सदस्य ID दर्ज करें';
        document.getElementById('verification-error').style.display = 'block';
        console.warn('Member ID is empty');
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
    .then(response => {
        console.log('API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('API response data:', data);
        document.getElementById('verification-loader').style.display = 'none';
        
        if (data.success) {
            // Set member data
            document.getElementById('hidden_member_id').value = data.data.member_id;
            
            // Load member details into form
            loadMemberDetailsData(data.data);
            
            // Show success message
            document.getElementById('verification-success').style.display = 'block';
            document.getElementById('success-text').textContent = 'सदस्य मिल गया! आवेदन भरते रहें...';
            
            // Hide verification form and show main form after 1 second
            setTimeout(() => {
                document.getElementById('verification-form').style.display = 'none';
                document.getElementById('main-form').style.display = 'block';
                
                // Scroll to form
                setTimeout(() => {
                    document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }, 1000);
        } else {
            document.getElementById('verification-error').textContent = data.message || 'सदस्य नहीं मिला। कृपया सदस्य ID जांचें।';
            document.getElementById('verification-error').style.display = 'block';
            console.warn('Verification failed:', data.message);
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
    console.log('Loading member details:', member);
    
    // Tab 1: Member Details (auto-filled, readonly)
    document.getElementById('member_name').value = member.full_name || '';
    document.getElementById('member_id_display').value = member.member_id || '';
    document.getElementById('member_mobile').value = member.mobile_number || '';
    document.getElementById('member_dob').value = member.dob || '';
    document.getElementById('member_address').value = member.address || '';
    
    // Tab 3: Deceased Details (auto-filled from searched member)
    // This is the member who has passed away
    const deceasedName = document.getElementById('deceased_name');
    const deceasedMemberId = document.getElementById('deceased_member_id');
    const deceasedDob = document.getElementById('deceased_dob');
    
    if (deceasedName) {
        deceasedName.value = member.full_name || '';
        console.log('Auto-filled deceased name:', member.full_name);
    }
    
    if (deceasedMemberId) {
        deceasedMemberId.value = member.member_id || '';
        console.log('Auto-filled deceased member ID:', member.member_id);
    }
    
    if (deceasedDob) {
        deceasedDob.value = member.dob || '';
        console.log('Auto-filled deceased DOB:', member.dob);
    }
    
    console.log('Member details loaded successfully');
}

// ========== AUTO-CALCULATE AGE AT TIME OF DEATH ==========
// Calculate age at a specific date (used for deceased age)
function calculateAgeAtDate(birthDate, referenceDate) {
    const birth = new Date(birthDate);
    const reference = new Date(referenceDate);
    
    let age = reference.getFullYear() - birth.getFullYear();
    const monthDiff = reference.getMonth() - birth.getMonth();
    
    // Adjust if birthday hasn't occurred yet in the reference year
    if (monthDiff < 0 || (monthDiff === 0 && reference.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

// Auto-calculate age at time of death
function autoCalculateDeceasedAge() {
    const dobInput = document.getElementById('deceased_dob');
    const dodInput = document.getElementById('death_date');
    const ageInput = document.getElementById('deceased_age');
    const errorMsg = document.getElementById('deceased_age_error');
    
    if (!dobInput.value || !dodInput.value) {
        ageInput.value = '';
        if (errorMsg) errorMsg.textContent = '';
        return;
    }
    
    try {
        const dob = dobInput.value;
        const dod = dodInput.value;
        
        const dobDate = new Date(dob);
        const dodDate = new Date(dod);
        
        // Calculate age at time of death
        const age = calculateAgeAtDate(dob, dod);
        
        // Auto-populate age field
        ageInput.value = age;
        
        // Validate age range (18-60)
        if (age < 18 || age > 60) {
            if (errorMsg) {
                errorMsg.textContent = `आयु 18 से 60 वर्ष के बीच होनी चाहिए (वर्तमान: ${age} वर्ष)`;
                errorMsg.style.color = '#c92a2a';
            }
        } else {
            if (errorMsg) {
                errorMsg.textContent = '';
            }
        }
        
        // Validate that DOD is after DOB
        if (dodDate <= dobDate) {
            if (errorMsg) {
                errorMsg.textContent = 'मृत्यु की तारीख जन्म तिथि से बाद की होनी चाहिए';
                errorMsg.style.color = '#c92a2a';
            }
        }
        
    } catch (error) {
        console.error('Error calculating age:', error);
        if (errorMsg) {
            errorMsg.textContent = 'तारीख में त्रुटि - कृपया सही प्रारूप में दर्ज करें';
            errorMsg.style.color = '#c92a2a';
        }
    }
}

// ========== END AUTO-CALCULATE AGE ==========

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

// UPI Validation function (optional field)
function validateUPI() {
    const upiId = document.getElementById('upi_id').value.trim();
    const upiError = document.getElementById('upi_error') || null;
    
    if (!upiId) {
        // Empty is OK since it's optional
        if (upiError) {
            upiError.style.display = 'none';
        }
        return true;
    }
    
    // UPI format: ^[a-zA-Z0-9._-]+@[a-zA-Z]{3,}$
    // Example: yourname@upi, yourname@okaxis, etc.
    const upiRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z]{3,}$/;
    
    if (!upiRegex.test(upiId)) {
        if (upiError) {
            upiError.textContent = 'UPI ID सही प्रारूप में नहीं है (उदाहरण: yourname@upi या yourname@okaxis)';
            upiError.style.display = 'block';
        }
        // Highlight field red
        document.getElementById('upi_id').style.borderColor = '#dc3545';
        document.getElementById('upi_id').style.backgroundColor = '#ffe8e8';
        return false;
    }
    
    if (upiError) {
        upiError.style.display = 'none';
    }
    // Highlight field green
    document.getElementById('upi_id').style.borderColor = '#28a745';
    document.getElementById('upi_id').style.backgroundColor = '#e8f5e9';
    return true;
}

function showTab(tabId) {
    // Remove active class from all tabs and tab panes
    document.querySelectorAll('#deathTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('#deathTabContent .tab-pane').forEach(pane => {
        pane.classList.remove('show', 'active');
    });
    
    // Add active class to clicked tab
    const tabElement = document.getElementById(tabId);
    if (tabElement) {
        tabElement.classList.add('active');
        
        // Get the target pane from data-bs-target
        const targetPane = tabElement.getAttribute('data-bs-target');
        const paneElement = document.querySelector(targetPane);
        if (paneElement) {
            paneElement.classList.add('show', 'active');
        }
    }
}

function updatePreview() {
    document.getElementById('preview_member_name').textContent = document.getElementById('member_name').value || '-';
    document.getElementById('preview_member_id').textContent = document.getElementById('member_id_display').value || '-';
    document.getElementById('preview_applicant_name').textContent = document.getElementById('applicant_name').value || '-';
    document.getElementById('preview_applicant_dob').textContent = document.getElementById('applicant_dob').value || '-';
    const selectedRelation = document.querySelector('input[name="applicant_relation"]:checked');
    document.getElementById('preview_applicant_relation').textContent = selectedRelation ? selectedRelation.value : '-';
    document.getElementById('preview_applicant_parent_name').textContent = document.getElementById('applicant_parent_name').value || '-';
    document.getElementById('preview_deceased_name').textContent = document.getElementById('deceased_name').value || '-';
    document.getElementById('preview_deceased_relationship').textContent = document.getElementById('deceased_relationship').value || '-';
    document.getElementById('preview_death_date').textContent = document.getElementById('death_date').value || '-';
    document.getElementById('preview_family_income').textContent = '₹' + (document.getElementById('family_income').value || '0');
    document.getElementById('preview_family_members').textContent = document.getElementById('family_members').value || '-';
    document.getElementById('preview_bank_name').textContent = document.getElementById('bank_name').value || '-';
    document.getElementById('preview_account_holder_name').textContent = document.getElementById('account_holder_name').value || '-';
    document.getElementById('preview_account_number').textContent = '**** **** **** ' + (document.getElementById('account_number').value.slice(-4) || '****');
}

// Validate Deceased Member Information
function validateDeceasedMemberInfo() {
    const deceasedMemberId = document.getElementById('deceased_member_id');
    const deceasedDob = document.getElementById('deceased_dob');
    const deceasedAge = document.getElementById('deceased_age');
    const deathDate = document.getElementById('death_date');
    
    // Early exit if elements don't exist
    if (!deceasedMemberId || !deceasedDob || !deceasedAge || !deathDate) {
        return;
    }
    
    // Clear previous errors (with null checks)
    const errorElements = ['deceased_member_id_error', 'deceased_dob_error', 'deceased_age_error', 'death_date_error'];
    errorElements.forEach(errorId => {
        const elem = document.getElementById(errorId);
        if (elem) elem.textContent = '';
    });
    
    let isValid = true;
    
    // Validate deceased member ID is provided
    if (!deceasedMemberId.value.trim()) {
        const errorElem = document.getElementById('deceased_member_id_error');
        if (errorElem) errorElem.textContent = 'मृत व्यक्ति का सदस्य ID आवश्यक है';
        isValid = false;
    }
    
    // Validate deceased DOB is provided
    if (!deceasedDob.value) {
        const errorElem = document.getElementById('deceased_dob_error');
        if (errorElem) errorElem.textContent = 'जन्म तिथि आवश्यक है';
        isValid = false;
    }
    
    // Validate death age is between 18-60 (client-side check)
    const deceasedAgeValue = parseInt(deceasedAge.value);
    if (isNaN(deceasedAgeValue) || deceasedAgeValue < 18 || deceasedAgeValue > 60) {
        const errorElem = document.getElementById('deceased_age_error');
        if (errorElem) errorElem.textContent = 'मृत्यु के समय आयु 18 से 60 वर्ष के बीच होनी चाहिए';
        isValid = false;
    }
    
    // Validate death date is provided
    if (!deathDate.value) {
        const errorElem = document.getElementById('death_date_error');
        if (errorElem) errorElem.textContent = 'मृत्यु की तारीख आवश्यक है';
        isValid = false;
    }
    
    // Additional validations if basic fields are valid
    if (isValid && deceasedMemberId.value && deceasedDob.value && deathDate.value) {
        // Check if DOB is before death date
        const dobDate = new Date(deceasedDob.value);
        const deathDateObj = new Date(deathDate.value);
        
        if (dobDate >= deathDateObj) {
            const errorElem = document.getElementById('deceased_dob_error');
            if (errorElem) errorElem.textContent = 'जन्म तिथि मृत्यु की तारीख से पहले होनी चाहिए';
            isValid = false;
        }
        
        // Check if DOB and age match (approximately)
        const calcAge = Math.floor((deathDateObj - dobDate) / (365.25 * 24 * 60 * 60 * 1000));
        if (Math.abs(calcAge - deceasedAgeValue) > 2) {
            const errorElem = document.getElementById('deceased_age_error');
            if (errorElem) errorElem.textContent = 'जन्म तिथि और आयु में मेल नहीं है। कृपया जांचें।';
            isValid = false;
        }
    }
    
    return isValid;
}

// Validate deceased member exists and meets requirements
async function validateDeceasedMembership() {
    const deceasedMemberId = document.getElementById('deceased_member_id').value.trim();
    const deceasedDob = document.getElementById('deceased_dob').value;
    const deathDate = document.getElementById('death_date').value;
    const deceasedAge = parseInt(document.getElementById('deceased_age').value);
    
    if (!deceasedMemberId || !deceasedDob || !deathDate) {
        return { valid: false, message: 'कृपया सभी आवश्यक विवरण भरें' };
    }
    
    try {
        const response = await fetch('../api/validate_deceased_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                deceased_member_id: deceasedMemberId,
                deceased_dob: deceasedDob,
                death_date: deathDate,
                deceased_age: deceasedAge
            })
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Validation error:', error);
        return { valid: false, message: 'नेटवर्क त्रुटि: ' + error.message };
    }
}

// Add event listeners for real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const deceasedMemberId = document.getElementById('deceased_member_id');
    const deceasedDob = document.getElementById('deceased_dob');
    const deceasedAge = document.getElementById('deceased_age');
    const deathDate = document.getElementById('death_date');
    
    if (deceasedMemberId) {
        deceasedMemberId.addEventListener('change', validateDeceasedMemberInfo);
        deceasedMemberId.addEventListener('blur', validateDeceasedMemberInfo);
    }
    
    if (deceasedDob) {
        deceasedDob.addEventListener('change', function() {
            autoCalculateDeceasedAge();
            validateDeceasedMemberInfo();
        });
        deceasedDob.addEventListener('blur', function() {
            autoCalculateDeceasedAge();
            validateDeceasedMemberInfo();
        });
    }
    
    if (deathDate) {
        deathDate.addEventListener('change', function() {
            autoCalculateDeceasedAge();
            validateDeceasedMemberInfo();
        });
        deathDate.addEventListener('blur', function() {
            autoCalculateDeceasedAge();
            validateDeceasedMemberInfo();
        });
    }

    if (deceasedAge) {
        deceasedAge.addEventListener('change', validateDeceasedMemberInfo);
        deceasedAge.addEventListener('blur', validateDeceasedMemberInfo);
    }

    // Allow Enter key on member search
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

    // Add UPI validation event listener
    const upiInput = document.getElementById('upi_id');
    if (upiInput) {
        upiInput.addEventListener('change', validateUPI);
        upiInput.addEventListener('blur', validateUPI);
    }

    // Handle file uploads
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const nameSpan = document.getElementById(this.id + '_name') || null;
            if (nameSpan && this.files.length > 0) {
                nameSpan.style.display = 'block';
                nameSpan.querySelector('span').textContent = this.files[0].name;
            }
        });

        // Drag and drop
        const uploadBox = input.previousElementSibling;
        if (uploadBox && uploadBox.classList.contains('file-upload-box')) {
            uploadBox.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadBox.style.borderColor = '#8B0000';
                uploadBox.style.background = '#ffe8e8';
            });

            uploadBox.addEventListener('dragleave', () => {
                uploadBox.style.borderColor = 'var(--accent-color)';
                uploadBox.style.background = '#f0f8ff';
            });

            uploadBox.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadBox.style.borderColor = 'var(--accent-color)';
                uploadBox.style.background = '#f0f8ff';
                if (e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    });
});

$(document).ready(function() {
    const hiddenInput = document.getElementById('hidden_member_id');
    const memberId = hiddenInput ? hiddenInput.value : '';

    // Load member details on page load (if logged in)
    function loadMemberDetails() {
        if (!memberId || memberId === '') return;
        
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
            },
            error: function(error) {
                console.error('Failed to load member details:', error);
            }
        });
    }

    loadMemberDetails();

    // Update preview when switching to review tab
    const reviewTab = document.getElementById('review-tab');
    if (reviewTab) {
        reviewTab.addEventListener('click', function() {
            setTimeout(updatePreview, 100);
        });
    }

    // Comprehensive form validation function
    function validateAllFields() {
        const errors = [];
        
        // Tab 1: Member Details (read-only, auto-filled)
        const memberName = document.getElementById('member_name');
        const memberIdDisplay = document.getElementById('member_id_display');
        const memberMobile = document.getElementById('member_mobile');
        const memberDob = document.getElementById('member_dob');
        const memberAddress = document.getElementById('member_address');
        
        if (!memberName || !memberName.value.trim()) {
            errors.push('📋 <strong>सदस्य विवरण:</strong> सदस्य का नाम अनुपलब्ध');
        }
        if (!memberIdDisplay || !memberIdDisplay.value.trim()) {
            errors.push('📋 <strong>सदस्य विवरण:</strong> सदस्य ID अनुपलब्ध');
        }
        
        // Tab 2: Applicant Details
        const applicantName = document.getElementById('applicant_name');
        const applicantDob = document.getElementById('applicant_dob');
        const applicantParentName = document.getElementById('applicant_parent_name');
        const applicantRelation = document.querySelector('input[name="applicant_relation"]:checked');
        
        if (!applicantName || !applicantName.value.trim()) {
            errors.push('👤 <strong>आवेदक विवरण:</strong> आवेदक का नाम आवश्यक है');
        }
        if (!applicantDob || !applicantDob.value) {
            errors.push('👤 <strong>आवेदक विवरण:</strong> आवेदक की जन्म तारीख आवश्यक है');
        }
        if (!applicantRelation) {
            errors.push('👤 <strong>आवेदक विवरण:</strong> संबंध चुनना आवश्यक है');
        }
        if (!applicantParentName || !applicantParentName.value.trim()) {
            errors.push('👤 <strong>आवेदक विवरण:</strong> अभिभावक का नाम आवश्यक है');
        }
        
        // Tab 3: Deceased Details
        const deceasedName = document.getElementById('deceased_name');
        const deceasedMemberId = document.getElementById('deceased_member_id');
        const deceasedDob = document.getElementById('deceased_dob');
        const deceasedAge = document.getElementById('deceased_age');
        const deathDate = document.getElementById('death_date');
        const deceasedRelationship = document.getElementById('deceased_relationship');
        const causeOfDeath = document.getElementById('cause_of_death');
        
        if (!deceasedName || !deceasedName.value.trim()) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत व्यक्ति का नाम आवश्यक है');
        }
        if (!deceasedMemberId || !deceasedMemberId.value.trim()) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत व्यक्ति का सदस्य ID आवश्यक है');
        }
        if (!deceasedDob || !deceasedDob.value) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत व्यक्ति की जन्म तारीख आवश्यक है');
        }
        if (!deceasedAge || !deceasedAge.value) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत्यु के समय आयु आवश्यक है');
        } else {
            const age = parseInt(deceasedAge.value);
            if (age < 18 || age > 60) {
                errors.push('⚰️ <strong>मृत विवरण:</strong> मृत्यु के समय आयु 18 से 60 वर्ष के बीच होनी चाहिए');
            }
        }
        if (!deathDate || !deathDate.value) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत्यु की तारीख आवश्यक है');
        }
        if (!deceasedRelationship || !deceasedRelationship.value) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> संबंध आवश्यक है');
        }
        if (!causeOfDeath || !causeOfDeath.value.trim()) {
            errors.push('⚰️ <strong>मृत विवरण:</strong> मृत्यु का कारण आवश्यक है');
        }
        
        // Tab 4: Family Income
        const familyIncome = document.getElementById('family_income');
        const familyMembers = document.getElementById('family_members');
        
        if (!familyIncome || !familyIncome.value) {
            errors.push('💰 <strong>आर्थिक जानकारी:</strong> वार्षिक पारिवारिक आय आवश्यक है');
        }
        if (!familyMembers || !familyMembers.value) {
            errors.push('💰 <strong>आर्थिक जानकारी:</strong> परिवार के सदस्य आवश्यक हैं');
        }
        
        // Tab 5: Bank Details
        const bankName = document.getElementById('bank_name');
        const branchName = document.getElementById('branch_name');
        const accountNumber = document.getElementById('account_number');
        const ifscCode = document.getElementById('ifsc_code');
        const accountHolderName = document.getElementById('account_holder_name');
        
        if (!bankName || !bankName.value.trim()) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> बैंक का नाम आवश्यक है');
        }
        if (!branchName || !branchName.value.trim()) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> शाखा का नाम आवश्यक है');
        }
        if (!accountNumber || !accountNumber.value.trim()) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> खाता संख्या आवश्यक है');
        } else if (!/^\d{9,18}$/.test(accountNumber.value.trim())) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> खाता संख्या 9 से 18 अंकों की होनी चाहिए');
        }
        if (!ifscCode || !ifscCode.value.trim()) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> IFSC कोड आवश्यक है');
        } else if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscCode.value.trim())) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> IFSC कोड सही प्रारूप में नहीं है (उदाहरण: SBIN0001234)');
        }
        if (!accountHolderName || !accountHolderName.value.trim()) {
            errors.push('🏦 <strong>बैंक विवरण:</strong> खाता धारक का नाम आवश्यक है');
        }
        
        // Tab 6: Documents
        const deceasedAadhar = document.getElementById('deceased_aadhar');
        const deathCertificate = document.getElementById('death_certificate');
        
        if (!deceasedAadhar || !deceasedAadhar.files || deceasedAadhar.files.length === 0) {
            errors.push('📄 <strong>दस्तावेज़:</strong> मृत का आधार कार्ड आवश्यक है');
        }
        if (!deathCertificate || !deathCertificate.files || deathCertificate.files.length === 0) {
            errors.push('📄 <strong>दस्तावेज़:</strong> मृत्यु प्रमाण पत्र आवश्यक है');
        }
        
        // Tab 7: Review - Confirmation checkbox
        const confirmDetails = document.getElementById('confirm_details');
        if (!confirmDetails || !confirmDetails.checked) {
            errors.push('✓ <strong>पुष्टि:</strong> कृपया यह पुष्टि करें कि सभी जानकारी सही है');
        }
        
        return errors;
    }
    
    // Function to show validation errors in modal
    function showValidationErrors(errors) {
        const errorList = document.getElementById('validationErrorList');
        errorList.innerHTML = '';
        
        if (errors.length === 0) {
            return false;
        }
        
        const ul = document.createElement('ul');
        ul.style.marginBottom = '0';
        ul.style.paddingLeft = '20px';
        
        errors.forEach(error => {
            const li = document.createElement('li');
            li.innerHTML = error;
            li.style.marginBottom = '10px';
            li.style.color = '#333';
            li.style.lineHeight = '1.6';
            ul.appendChild(li);
        });
        
        errorList.appendChild(ul);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('validationErrorModal'));
        modal.show();
        
        return true;
    }

    // Handle form submission
    const form = document.getElementById('death-aavedan-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Run comprehensive validation
            const validationErrors = validateAllFields();
            if (validationErrors.length > 0) {
                showValidationErrors(validationErrors);
                return;
            }
            
            // Validate deceased membership on server
            const membershipValidation = await validateDeceasedMembership();
            if (!membershipValidation.valid) {
                showValidationErrors([membershipValidation.message]);
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

            $.ajax({
                url: '../api/death_aavedan.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
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
                    document.getElementById('error-text').textContent = 'आवेदन जमा नहीं हो सका। कृपया पुनः प्रयास करें।';
                    errorMsg.show();
                    console.error('Error:', error);
                    submitBtn.prop('disabled', false);
                    loader.hide();
                }
            });
        });
    }
});
