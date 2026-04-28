// Edit Member JavaScript

// API Base URL (adjust if needed)
const API_BASE_URL = './api';

// DOM Elements
const searchMemberIdInput = document.getElementById('searchMemberId');
const searchMobileInput = document.getElementById('searchMobileNumber');
const searchBtn = document.getElementById('searchBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const memberDetailsSection = document.getElementById('memberDetailsSection');
const noMemberSection = document.getElementById('noMemberSection');
const alertContainer = document.getElementById('alertContainer');
const memberEditForm = document.getElementById('memberEditForm');

let currentMemberId = null;

// Event Listeners
if (searchBtn) {
    searchBtn.addEventListener('click', searchMember);
}

document.addEventListener('DOMContentLoaded', function() {
    // Allow Enter key to search
    searchMemberIdInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchMember();
    });
    searchMobileInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchMember();
    });

    // Form submission
    if (memberEditForm) {
        memberEditForm.addEventListener('submit', updateMember);
    }
});

/**
 * Search for member by ID or Mobile Number
 */
async function searchMember() {
    const memberId = searchMemberIdInput.value.trim();
    const mobileNumber = searchMobileInput.value.trim();

    if (!memberId && !mobileNumber) {
        showAlert('कृपया सदस्य ID या मोबाइल नंबर दर्ज करें', 'warning');
        return;
    }

    // Validate mobile number format if provided
    if (mobileNumber && !isValidMobileNumber(mobileNumber)) {
        showAlert('कृपया वैध 10 अंकीय मोबाइल नंबर दर्ज करें', 'warning');
        return;
    }

    loadingSpinner.style.display = 'block';
    memberDetailsSection.style.display = 'none';
    noMemberSection.style.display = 'none';

    try {
        const response = await fetch(`${API_BASE_URL}/search-member.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                member_id: memberId,
                mobile_number: mobileNumber
            })
        });

        const data = await response.json();

        if (data.success && data.member) {
            displayMemberDetails(data.member);
            memberDetailsSection.style.display = 'block';
            noMemberSection.style.display = 'none';
            currentMemberId = data.member.member_id;
        } else {
            memberDetailsSection.style.display = 'none';
            noMemberSection.style.display = 'block';
            showAlert(data.message || 'सदस्य नहीं मिला', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('त्रुटि: ' + error.message, 'danger');
        memberDetailsSection.style.display = 'none';
        noMemberSection.style.display = 'block';
    } finally {
        loadingSpinner.style.display = 'none';
    }
}

/**
 * Display member details in the form
 */
function displayMemberDetails(member) {
    // Display basic info
    document.getElementById('displayMemberId').textContent = member.member_id || '-';
    document.getElementById('displayLoginId').textContent = member.login_id || '-';
    document.getElementById('displayAadhar').textContent = maskAadhar(member.aadhar_number);
    
    // Status badge
    const statusSpan = document.getElementById('displayStatus');
    statusSpan.textContent = member.status == 1 ? 'सक्रिय' : 'निष्क्रिय';
    statusSpan.className = member.status == 1 ? 'active' : 'inactive';

    document.getElementById('displayCreatedAt').textContent = formatDate(member.created_at);
    document.getElementById('displayUpdatedAt').textContent = formatDate(member.updated_at);

    // Personal Details Tab
    document.getElementById('editFullName').value = member.full_name || '';
    document.getElementById('editAadharNumber').value = maskAadhar(member.aadhar_number);
    document.getElementById('editFatherName').value = member.father_husband_name || '';
    document.getElementById('editDob').value = member.date_of_birth || '';
    document.getElementById('editMobile').value = member.mobile_number || '';
    document.getElementById('editGender').value = member.gender || '';
    document.getElementById('editEmail').value = member.email || '';

    // Additional Details Tab
    document.getElementById('editOccupation').value = member.occupation || '';
    document.getElementById('editOfficeName').value = member.office_name || '';
    document.getElementById('editOfficeAddress').value = member.office_address || '';

    // Location Details Tab
    document.getElementById('editState').value = member.state || '';
    document.getElementById('editDistrict').value = member.district || '';
    document.getElementById('editBlock').value = member.block || '';
    document.getElementById('editPermanentAddress').value = member.permanent_address || '';

    // Nominee Details Tab
    document.getElementById('editNomineeName').value = member.nominee_name || '';
    document.getElementById('editNomineeRelation').value = member.nominee_relation || '';
    document.getElementById('editNomineeMobile').value = member.nominee_mobile || '';
    document.getElementById('editNomineeAadhar').value = member.nominee_aadhar || '';

    // Account Details Tab
    document.getElementById('displayUtrNumber').value = member.utr_number || '-';
    document.getElementById('editPaymentVerified').value = member.payment_verified || '0';
    document.getElementById('editStatus').value = member.status || '0';

    // Scroll to details section
    setTimeout(() => {
        document.getElementById('memberDetailsSection').scrollIntoView({ behavior: 'smooth' });
    }, 300);
}

/**
 * Update member information
 */
async function updateMember(e) {
    e.preventDefault();

    if (!currentMemberId) {
        showAlert('कृपया पहले सदस्य खोजें', 'warning');
        return;
    }

    // Validation
    const fullName = document.getElementById('editFullName').value.trim();
    const fatherName = document.getElementById('editFatherName').value.trim();
    const dob = document.getElementById('editDob').value;
    const mobile = document.getElementById('editMobile').value.trim();
    const gender = document.getElementById('editGender').value;
    const occupation = document.getElementById('editOccupation').value;
    const state = document.getElementById('editState').value.trim();
    const district = document.getElementById('editDistrict').value.trim();
    const block = document.getElementById('editBlock').value.trim();
    const permanentAddress = document.getElementById('editPermanentAddress').value.trim();

    // Validate required fields
    if (!fullName) {
        showAlert('पूरा नाम दर्ज करें', 'danger');
        return;
    }
    if (!fatherName) {
        showAlert('पिता/पति का नाम दर्ज करें', 'danger');
        return;
    }
    if (!dob) {
        showAlert('जन्म तिथि दर्ज करें', 'danger');
        return;
    }
    if (!mobile || !isValidMobileNumber(mobile)) {
        showAlert('वैध 10 अंकीय मोबाइल नंबर दर्ज करें', 'danger');
        return;
    }
    if (!gender) {
        showAlert('लिंग का चयन करें', 'danger');
        return;
    }
    if (!occupation) {
        showAlert('व्यवसाय का चयन करें', 'danger');
        return;
    }
    if (!state) {
        showAlert('राज्य दर्ज करें', 'danger');
        return;
    }
    if (!district) {
        showAlert('जिला दर्ज करें', 'danger');
        return;
    }
    if (!block) {
        showAlert('ब्लॉक दर्ज करें', 'danger');
        return;
    }
    if (!permanentAddress) {
        showAlert('स्थायी पता दर्ज करें', 'danger');
        return;
    }

    // Prepare form data
    const formData = {
        member_id: currentMemberId,
        full_name: fullName,
        father_husband_name: fatherName,
        date_of_birth: dob,
        mobile_number: mobile,
        gender: gender,
        email: document.getElementById('editEmail').value.trim(),
        occupation: occupation,
        office_name: document.getElementById('editOfficeName').value.trim(),
        office_address: document.getElementById('editOfficeAddress').value.trim(),
        state: state,
        district: district,
        block: block,
        permanent_address: permanentAddress,
        nominee_name: document.getElementById('editNomineeName').value.trim(),
        nominee_relation: document.getElementById('editNomineeRelation').value.trim(),
        nominee_mobile: document.getElementById('editNomineeMobile').value.trim(),
        nominee_aadhar: document.getElementById('editNomineeAadhar').value.trim(),
        payment_verified: document.getElementById('editPaymentVerified').value,
        status: document.getElementById('editStatus').value
    };

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>सहेजा जा रहा है...';

    try {
        const response = await fetch(`${API_BASE_URL}/update-member.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            showAlert(data.message || 'सदस्य विवरण सफलतापूर्वक अपडेट किया गया', 'success');
            // Refresh member details
            setTimeout(() => {
                searchMember();
            }, 1500);
        } else {
            showAlert(data.message || 'अपडेट विफल', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('त्रुटि: ' + error.message, 'danger');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

/**
 * Reset search and hide details
 */
function resetSearch() {
    searchMemberIdInput.value = '';
    searchMobileInput.value = '';
    memberDetailsSection.style.display = 'none';
    noMemberSection.style.display = 'none';
    currentMemberId = null;
    memberEditForm.reset();
    searchMemberIdInput.focus();
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    
    const iconClass = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';

    alertDiv.innerHTML = `
        <i class="fas ${iconClass} me-2"></i>
        <strong>${message}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    alertContainer.innerHTML = '';
    alertContainer.appendChild(alertDiv);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Utility Functions
 */

/**
 * Validate mobile number
 */
function isValidMobileNumber(mobile) {
    return /^[6-9]\d{9}$/.test(mobile.replace(/\s+/g, ''));
}

/**
 * Mask Aadhar number (show only last 4 digits)
 */
function maskAadhar(aadhar) {
    if (!aadhar) return '-';
    return 'XXXX XXXX ' + aadhar.slice(-4);
}

/**
 * Format date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('hi-IN', options);
}

/**
 * Format date and time
 */
function formatDateTime(dateString) {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('hi-IN', options);
}
