// Death Claim Form JavaScript

// DOM Elements
const searchAadhaarInput = document.getElementById('search_aadhaar');
const searchLoader = document.getElementById('search-loader');
const searchError = document.getElementById('search-error');
const memberSearchSection = document.getElementById('member-search-section');
const mainFormSection = document.getElementById('main-form-section');
const deathClaimForm = document.getElementById('death-claim-form');

// Form elements
const memberIdInput = document.getElementById('member_id');
const memberNameInput = document.getElementById('member_name');
const memberFatherInput = document.getElementById('member_father_name');
const memberDobInput = document.getElementById('member_dob');
const memberAddressInput = document.getElementById('member_address');

// Death details
const deathDateInput = document.getElementById('death_date');
const ageAtDeathInput = document.getElementById('age_at_death');

// Nominee details
const nomineeNameInput = document.getElementById('nominee_name');
const nomineeRelationSelect = document.getElementById('nominee_relation');
const nomineeDobInput = document.getElementById('nominee_dob');
const nomineeMobileInput = document.getElementById('nominee_mobile');
const mobileError = document.getElementById('mobile_error');

// File inputs
const fileAadhaarDeceased = document.getElementById('file_aadhaar_deceased');
const fileDeathCertificate = document.getElementById('file_death_certificate');
const filePostmortem = document.getElementById('file_postmortem');
const fileNomineeAadhaar = document.getElementById('file_nominee_aadhaar');

// Bank details
const ifscCodeInput = document.getElementById('ifsc_code');
const bankNameInput = document.getElementById('bank_name');
const branchNameInput = document.getElementById('branch_name');

// Review elements
const reviewMemberDetails = document.getElementById('review_member_details');
const reviewDeathDetails = document.getElementById('review_death_details');
const reviewNomineeDetails = document.getElementById('review_nominee_details');
const reviewBankDetails = document.getElementById('review_bank_details');
const reviewDocuments = document.getElementById('review_documents');

// Submit
const submitLoader = document.getElementById('submit-loader');
const submitSuccess = document.getElementById('submit-success');
const submitError = document.getElementById('submit-error');
const submitBtn = document.getElementById('submit-btn');

let memberData = null;

// Search Member by Aadhaar
function searchMember() {
    const aadhaar = searchAadhaarInput.value.trim();

    if (!aadhaar || aadhaar.length !== 8 || isNaN(aadhaar)) {
        showError('कृपया आधार के अंतिम 8 अंक दर्ज करें');
        return;
    }

    searchLoader.style.display = 'block';
    searchError.style.display = 'none';

    fetch('../api/death_claims_fetch.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            action: 'search_member',
            aadhaar: aadhaar
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            searchLoader.style.display = 'none';

            if (data && data.success && data.data) {
                memberData = data.data;
                fillMemberData(memberData);
                memberSearchSection.style.display = 'none';
                mainFormSection.style.display = 'block';
                goToTab('tab-member-tab');
            } else {
                const message = data && data.message ? data.message : 'Error searching member';
                showError(message);
            }
        })
        .catch(error => {
            searchLoader.style.display = 'none';
            console.error('Error:', error);
            showError('⚠️ Error: ' + error.message + '\nPlease check if the API is accessible.');
        });
}

// Fill Member Data
function fillMemberData(data) {
    memberIdInput.value = data.member_id || '';
    memberNameInput.value = data.full_name || '';
    memberFatherInput.value = data.father_name || '';
    memberDobInput.value = data.dob || '';
    memberAddressInput.value = data.address || '';
    document.getElementById('hidden_member_id').value = data.member_id;
}

// Calculate Age
function calculateAge() {
    const dob = new Date(memberDobInput.value);
    const deathDate = new Date(deathDateInput.value);
    const deathDateError = document.getElementById('death_date_error');

    if (!deathDateInput.value) {
        ageAtDeathInput.value = '';
        deathDateError.textContent = '';
        return;
    }

    if (deathDate <= dob) {
        deathDateError.textContent = 'मृत्यु तिथि जन्म तिथि से बाद की होनी चाहिए';
        ageAtDeathInput.value = '';
        return;
    }

    let age = deathDate.getFullYear() - dob.getFullYear();
    const monthDiff = deathDate.getMonth() - dob.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && deathDate.getDate() < dob.getDate())) {
        age--;
    }

    ageAtDeathInput.value = age;
    deathDateError.textContent = '';

    // Validation: Age should be 18-60
    if (age < 18 || age > 60) {
        deathDateError.textContent = 'सदस्य की आयु 18-60 वर्ष के बीच होनी चाहिए';
    }
}

// Validate Mobile Number
nomineeMobileInput.addEventListener('input', function () {
    mobileError.textContent = '';
    if (this.value.length === 10 && /^\d{10}$/.test(this.value)) {
        mobileError.textContent = '';
    } else if (this.value.length > 0) {
        mobileError.textContent = 'कृपया 10 अंको वाला सही मोबाइल नंबर दर्ज करें';
    }
});

// File Input Handlers
fileAadhaarDeceased.addEventListener('change', function () {
    updateFileLabel(this, 'file_aadhaar_name');
    validateFile(this, 'error_aadhaar_deceased');
});

fileDeathCertificate.addEventListener('change', function () {
    updateFileLabel(this, 'file_death_cert_name');
    validateFile(this, 'error_death_certificate');
});

filePostmortem.addEventListener('change', function () {
    updateFileLabel(this, 'file_postmortem_name');
    validateFile(this, 'error_postmortem');
});

fileNomineeAadhaar.addEventListener('change', function () {
    updateFileLabel(this, 'file_nominee_name');
    validateFile(this, 'error_nominee_aadhaar');
});

// IFSC Code Auto-fetch Bank Details
ifscCodeInput.addEventListener('change', function() {
    const ifscCode = this.value.trim().toUpperCase();
    
    if (!ifscCode) {
        bankNameInput.value = '';
        branchNameInput.value = '';
        return;
    }
    
    if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifscCode)) {
        // Invalid IFSC format - don't call API
        return;
    }
    
    fetchBankDetails(ifscCode);
});

// Fetch Bank Details from IFSC Code
function fetchBankDetails(ifscCode) {
    // Show loading indicator on bank name field
    const originalValue = bankNameInput.value;
    bankNameInput.value = 'खोज रहे हैं...';
    branchNameInput.value = 'खोज रहे हैं...';
    
    // Using Razorpay's free IFSC API (no authentication required)
    fetch(`https://ifsc.razorpay.com/${ifscCode}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('IFSC Code not found');
            }
            return response.json();
        })
        .then(data => {
            // API returns: { IFSC, BANK, BRANCH, ADDRESS, CONTACT, CITY, DISTRICT, STATE }
            if (data && data.BANK) {
                bankNameInput.value = data.BANK || '';
                branchNameInput.value = data.BRANCH || '';
            } else {
                bankNameInput.value = originalValue || '';
                branchNameInput.value = '';
                console.error('Bank details not found');
            }
        })
        .catch(error => {
            console.error('Error fetching bank details:', error);
            bankNameInput.value = originalValue || '';
            branchNameInput.value = '';
            // Show error message but don't break form
        });
}

function updateFileLabel(input, labelId) {
    const label = document.getElementById(labelId);
    if (input.files.length > 0) {
        const fileName = input.files[0].name;
        label.textContent = fileName;
    }
}

function validateFile(input, errorId) {
    const errorElement = document.getElementById(errorId);
    const file = input.files[0];

    if (!file) {
        errorElement.textContent = '';
        return true;
    }

    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    if (file.size > maxSize) {
        errorElement.textContent = 'फाइल का आकार 2MB से कम होना चाहिए';
        input.value = '';
        return false;
    }

    if (!allowedTypes.includes(file.type)) {
        errorElement.textContent = 'कृपया JPG, PNG या PDF फाइल चुनें';
        input.value = '';
        return false;
    }

    errorElement.textContent = '';
    return true;
}

// Tab Navigation
function goToTab(tabId) {
    // Append '-tab' suffix if not already present
    const buttonId = tabId.endsWith('-tab') ? tabId : tabId + '-tab';
    const tabButton = document.getElementById(buttonId);
    
    if (!tabButton) {
        console.error('Tab button not found:', buttonId);
        return;
    }
    
    const tab = new bootstrap.Tab(tabButton);
    tab.show();

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Clear Search (Search for new member)
function clearSearch() {
    searchAadhaarInput.value = '';
    memberSearchSection.style.display = 'block';
    mainFormSection.style.display = 'none';
    document.getElementById('death-claim-form').reset();
    memberData = null;
}

// Show Error
function showError(message) {
    searchError.textContent = message;
    searchError.style.display = 'block';
    setTimeout(() => {
        searchError.style.display = 'none';
    }, 5000);
}

// Review Form Data
function updateReview() {
    // Member Details
    reviewMemberDetails.innerHTML = `
        <div class="review-row">
            <span class="review-label">सदस्य ID:</span>
            <span class="review-value">${memberIdInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">पूरा नाम:</span>
            <span class="review-value">${memberNameInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">पिता/पति का नाम:</span>
            <span class="review-value">${memberFatherInput.value || 'N/A'}</span>
        </div>
        <div class="review-row">
            <span class="review-label">जन्म तिथि:</span>
            <span class="review-value">${memberDobInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">पता:</span>
            <span class="review-value">${memberAddressInput.value || 'N/A'}</span>
        </div>
    `;

    // Death Details
    reviewDeathDetails.innerHTML = `
        <div class="review-row">
            <span class="review-label">मृत्यु तिथि:</span>
            <span class="review-value">${deathDateInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">आयु:</span>
            <span class="review-value">${ageAtDeathInput.value} वर्ष</span>
        </div>
        <div class="review-row">
            <span class="review-label">स्थान:</span>
            <span class="review-value">${document.getElementById('death_place').value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">कारण:</span>
            <span class="review-value">${document.getElementById('death_reason').value || 'N/A'}</span>
        </div>
    `;

    // Nominee Details
    reviewNomineeDetails.innerHTML = `
        <div class="review-row">
            <span class="review-label">नाम:</span>
            <span class="review-value">${nomineeNameInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">संबंध:</span>
            <span class="review-value">${nomineeRelationSelect.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">जन्म तिथि:</span>
            <span class="review-value">${nomineeDobInput.value || 'N/A'}</span>
        </div>
        <div class="review-row">
            <span class="review-label">मोबाइल:</span>
            <span class="review-value">${nomineeMobileInput.value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">पता:</span>
            <span class="review-value">${document.getElementById('nominee_address').value || 'N/A'}</span>
        </div>
    `;

    // Bank Details
    reviewBankDetails.innerHTML = `
        <div class="review-row">
            <span class="review-label">बैंक:</span>
            <span class="review-value">${document.getElementById('bank_name').value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">खाता संख्या:</span>
            <span class="review-value">${maskAccountNumber(document.getElementById('account_number').value)}</span>
        </div>
        <div class="review-row">
            <span class="review-label">IFSC:</span>
            <span class="review-value">${document.getElementById('ifsc_code').value}</span>
        </div>
        <div class="review-row">
            <span class="review-label">खाते धारक:</span>
            <span class="review-value">${document.getElementById('account_holder_name').value}</span>
        </div>
        ${document.getElementById('upi_id').value ? `
        <div class="review-row">
            <span class="review-label">UPI ID:</span>
            <span class="review-value">${document.getElementById('upi_id').value}</span>
        </div>
        ` : ''}
    `;

    // Documents
    const docs = [];
    if (fileAadhaarDeceased.files.length > 0) docs.push(`<li>✓ मृत व्यक्ति का आधार: ${fileAadhaarDeceased.files[0].name}</li>`);
    if (fileDeathCertificate.files.length > 0) docs.push(`<li>✓ मृत्यु प्रमाण पत्र: ${fileDeathCertificate.files[0].name}</li>`);
    if (filePostmortem.files.length > 0) docs.push(`<li>✓ पोस्टमॉर्टम रिपोर्ट: ${filePostmortem.files[0].name}</li>`);
    if (fileNomineeAadhaar.files.length > 0) docs.push(`<li>✓ नॉमिनी का आधार: ${fileNomineeAadhaar.files[0].name}</li>`);

    reviewDocuments.innerHTML = docs.length > 0 ? `<ul>${docs.join('')}</ul>` : '<p>कोई दस्तावेज़ अपलोड नहीं किया गया</p>';
}

function maskAccountNumber(accountNumber) {
    if (!accountNumber) return '';
    const visible = accountNumber.slice(-4);
    return `**** **** ${visible}`;
}

// Tab Change Event - Update Review
document.getElementById('tab-review-tab').addEventListener('click', function () {
    updateReview();
});

// Form Validation
function validateForm() {
    const errors = [];

    // Member Details
    if (!memberIdInput.value) errors.push('सदस्य ID को भरें');
    if (!memberNameInput.value) errors.push('सदस्य का नाम दर्ज करें');
    if (!memberDobInput.value) errors.push('जन्म तिथि दर्ज करें');

    // Death Details
    if (!deathDateInput.value) errors.push('मृत्यु तिथि दर्ज करें');
    if (!document.getElementById('death_place').value) errors.push('मृत्यु स्थान दर्ज करें');

    // Nominee Details
    if (!nomineeNameInput.value) errors.push('नॉमिनी का नाम दर्ज करें');
    if (!nomineeRelationSelect.value) errors.push('संबंध चुनें');
    if (!nomineeMobileInput.value) errors.push('नॉमिनी का मोबाइल नंबर दर्ज करें');
    if (nomineeMobileInput.value && !/^\d{10}$/.test(nomineeMobileInput.value)) {
        errors.push('नॉमिनी का मोबाइल नंबर 10 अंकों का होना चाहिए');
    }

    // Bank Details
    if (!document.getElementById('bank_name').value) errors.push('बैंक का नाम दर्ज करें');
    if (!document.getElementById('account_number').value) errors.push('खाता संख्या दर्ज करें');
    if (!document.getElementById('ifsc_code').value) errors.push('IFSC कोड दर्ज करें');
    if (!document.getElementById('account_holder_name').value) errors.push('खाते धारक का नाम दर्ज करें');

    // Documents
    if (!fileAadhaarDeceased.files.length) errors.push('मृत व्यक्ति का आधार कार्ड अपलोड करें');
    if (!fileDeathCertificate.files.length) errors.push('मृत्यु प्रमाण पत्र अपलोड करें');
    if (!fileNomineeAadhaar.files.length) errors.push('नॉमिनी का आधार कार्ड अपलोड करें');

    return errors;
}

// Submit Form
function submitForm() {
    const errors = validateForm();

    if (errors.length > 0) {
        alert('कृपया निम्नलिखित त्रुटियों को ठीक करें:\n\n' + errors.join('\n'));
        return;
    }

    submitLoader.style.display = 'block';
    submitSuccess.style.display = 'none';
    submitError.style.display = 'none';
    submitBtn.disabled = true;

    // Create FormData
    const formData = new FormData();
    formData.append('action', 'insert_claim');
    formData.append('member_id', memberIdInput.value);
    formData.append('member_name', memberNameInput.value);
    formData.append('member_father_name', memberFatherInput.value);
    formData.append('member_dob', memberDobInput.value);
    formData.append('member_address', memberAddressInput.value);
    formData.append('death_date', deathDateInput.value);
    formData.append('death_place', document.getElementById('death_place').value);
    formData.append('death_reason', document.getElementById('death_reason').value);
    formData.append('age_at_death', ageAtDeathInput.value);
    formData.append('nominee_name', nomineeNameInput.value);
    formData.append('nominee_relation', nomineeRelationSelect.value);
    formData.append('nominee_dob', nomineeDobInput.value);
    formData.append('nominee_mobile', nomineeMobileInput.value);
    formData.append('nominee_address', document.getElementById('nominee_address').value);
    formData.append('bank_name', document.getElementById('bank_name').value);
    formData.append('account_number', document.getElementById('account_number').value);
    formData.append('ifsc_code', document.getElementById('ifsc_code').value);
    formData.append('branch_name', document.getElementById('branch_name').value);
    formData.append('account_holder_name', document.getElementById('account_holder_name').value);
    formData.append('upi_id', document.getElementById('upi_id').value);
    
    // Append files only if they exist and have content
    if (fileAadhaarDeceased && fileAadhaarDeceased.files && fileAadhaarDeceased.files[0]) {
        formData.append('file_aadhaar_deceased', fileAadhaarDeceased.files[0]);
    }
    if (fileDeathCertificate && fileDeathCertificate.files && fileDeathCertificate.files[0]) {
        formData.append('file_death_certificate', fileDeathCertificate.files[0]);
    }
    if (filePostmortem && filePostmortem.files && filePostmortem.files[0]) {
        formData.append('file_postmortem', filePostmortem.files[0]);
    }
    if (fileNomineeAadhaar && fileNomineeAadhaar.files && fileNomineeAadhaar.files[0]) {
        formData.append('file_nominee_aadhaar', fileNomineeAadhaar.files[0]);
    }

    fetch('../api/death_claims_insert.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            submitLoader.style.display = 'none';
            submitBtn.disabled = false;

            if (data && data.success) {
                submitSuccess.style.display = 'block';
                document.getElementById('claim_id').textContent = data.claim_id;
                
                // Redirect to receipt page after 2 seconds
                setTimeout(() => {
                    window.location.href = '../pages/death_claims_receipt.php?id=' + encodeURIComponent(data.claim_id);
                }, 2000);
            } else {
                submitError.style.display = 'block';
                document.getElementById('error-message').textContent = (data && data.message) ? data.message : 'आवेदन जमा नहीं किया जा सका';
            }
        })
        .catch(error => {
            submitLoader.style.display = 'none';
            submitBtn.disabled = false;
            submitError.style.display = 'block';
            document.getElementById('error-message').textContent = '⚠️ Error: ' + error.message;
            console.error('Error:', error);
        });
}

// Enter key in search
searchAadhaarInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        searchMember();
    }
});
