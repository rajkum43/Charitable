// Member Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Member Dashboard लोड हो रहा है...');
    
    // Initialize sidebar toggle
    initializeSidebarToggle();
    
    // Load member data on page load
    loadMemberData();
    
    // Set up event listeners
    setupEventListeners();
    
    // Close sidebar when clicking outside on mobile
    closeSidebarOnClickOutside();
});

// Load member data
function loadMemberData() {
    fetch('api/get_member_data.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateMemberData(data.data);
            } else {
                if (data.message === 'कृपया लॉगिन करें' || response.status === 401) {
                    window.location.href = '../pages/login.php';
                } else {
                    showAlert(data.message || 'डेटा लोड करने में त्रुटि', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('डेटा लोड करने में त्रुटि: ' + error.message, 'danger');
        });
}

// Populate member data in UI
function populateMemberData(data) {
    // Update sidebar
    document.getElementById('sidebarMemberName').textContent = data.full_name || 'सदस्य';
    document.getElementById('sidebarMemberId').textContent = 'ID: ' + data.member_id;
    
    // Update navbar
    document.getElementById('navbarMemberName').textContent = data.full_name || 'सदस्य';
    
    // Update navbar dropdown
    document.getElementById('dropdownMemberName').textContent = data.full_name || 'सदस्य';
    
    // Populate dashboard section
    document.getElementById('memberName').textContent = data.full_name || 'N/A';
    document.getElementById('memberId').textContent = data.member_id || 'N/A';
    document.getElementById('memberStatus').textContent = data.membership_status || 'निष्क्रिय';
    document.getElementById('memberStatus').className = 
        data.status === 1 ? 'badge bg-success' : 'badge bg-warning';
    
    document.getElementById('joinDate').textContent = data.created_at || 'N/A';
    document.getElementById('paymentStatus').textContent = data.payment_status || 'लंबित';
    document.getElementById('paymentStatus').className = 
        data.payment_verified === 1 ? 'badge bg-success' : 'badge bg-danger';
    
    // Populate profile section
    populateProfileData(data);
    
    // Populate membership section
    populateMembershipData(data);
}

// Populate profile section
function populateProfileData(data) {
    const profileInfo = {
        'profileFullName': data.full_name,
        'profileEmail': data.email,
        'profileMobile': data.mobile_number,
        'profileGender': data.gender,
        'profileDOB': data.date_of_birth,
        'profileOccupation': data.occupation,
        'profileOffice': data.office_name,
        'profileOfficeAddress': data.office_address,
        'profilePermanentAddress': data.permanent_address
    };
    
    Object.keys(profileInfo).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = profileInfo[elementId] || 'N/A';
            element.setAttribute('data-original', profileInfo[elementId] || '');
        }
    });
}

// Populate membership section
function populateMembershipData(data) {
    const membershipInfo = {
        'membershipAadhar': data.aadhar_masked,
        'membershipFather': data.father_husband_name,
        'membershipState': data.state,
        'membershipDistrict': data.district,
        'membershipBlock': data.block,
        'membershipUTR': data.utr_number,
        'membershipPaymentStatus': data.payment_status,
        'membershipJoinDate': data.created_at
    };
    
    Object.keys(membershipInfo).forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = membershipInfo[elementId] || 'N/A';
        }
    });
}

// Set up event listeners
function setupEventListeners() {
    // Profile edit buttons
    const profileEditButtons = document.querySelectorAll('.btn-edit-profile');
    profileEditButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fieldId = this.getAttribute('data-field');
            enableFieldEdit(fieldId);
        });
    });
}

// Initialize sidebar toggle functionality
function initializeSidebarToggle() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navbarToggleMobile = document.querySelector('.navbar-toggle-mobile');
    
    // Sidebar toggle button click
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }
    
    // Navbar mobile toggle button click
    if (navbarToggleMobile && sidebar) {
        navbarToggleMobile.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }
}

// Close sidebar when clicking outside on mobile
function closeSidebarOnClickOutside() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (!sidebar) return;
    
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = event.target.closest('.sidebar-toggle') || 
                               event.target.closest('.navbar-toggle-mobile');
        
        if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth <= 768) {
            sidebar.classList.remove('active');
        }
    });
}

// Load section content
function loadSection(sectionName) {
    console.log('सेक्शन लोड कर रहे हैं:', sectionName);
    
    // Hide all sections
    document.querySelectorAll('.content-box').forEach(box => {
        box.classList.remove('active');
    });
    
    // Show selected section
    const section = document.getElementById(sectionName + '-section');
    if (section) {
        section.classList.add('active');
        
        // Update navbar title
        const sectionTitles = {
            'dashboard': 'डैशबोर्ड',
            'profile': 'प्रोफाइल',
            'membership': 'सदस्यता विवरण',
            'payment': 'भुगतान',
            'documents': 'दस्तावेज',
            'settings': 'सेटिंग्स'
        };
        
        const navbarTitle = document.getElementById('navbarTitle') || 
                           document.querySelector('.navbar-title');
        if (navbarTitle) {
            navbarTitle.textContent = sectionTitles[sectionName] || 'डैशबोर्ड';
        }
    }
    
    // Update sidebar active link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`[data-section="${sectionName}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
    
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('active');
        }
    }
}

// Enable field edit
function enableFieldEdit(fieldId) {
    const element = document.getElementById(fieldId);
    if (!element) return;
    
    const currentValue = element.textContent || '';
    const fieldName = fieldId.replace('profile', '');
    const fieldMap = {
        'Email': 'email',
        'Mobile': 'mobile_number',
        'Office': 'office_name',
        'OfficeAddress': 'office_address',
        'PermanentAddress': 'permanent_address'
    };
    
    const apiField = fieldMap[fieldName];
    if (!apiField) return;
    
    // Create edit input
    const inputElement = document.createElement('input');
    inputElement.type = fieldName === 'Email' ? 'email' : 'text';
    inputElement.value = currentValue;
    inputElement.className = 'form-control form-control-sm mb-2';
    
    // Save button
    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn btn-sm btn-success me-2';
    saveBtn.innerHTML = '<i class="fas fa-check"></i> बचाएं';
    saveBtn.onclick = () => saveFieldChange(fieldId, apiField, inputElement.value);
    
    // Cancel button
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-sm btn-secondary';
    cancelBtn.innerHTML = '<i class="fas fa-times"></i> रद्द करें';
    cancelBtn.onclick = () => disableFieldEdit(fieldId);
    
    // Replace element with input
    const parent = element.parentElement;
    parent.innerHTML = '';
    parent.appendChild(inputElement);
    parent.appendChild(saveBtn);
    parent.appendChild(cancelBtn);
    
    // Focus input
    inputElement.focus();
}

// Save field change
function saveFieldChange(fieldId, apiField, value) {
    const formData = new FormData();
    formData.append('field', apiField);
    formData.append('value', value);
    
    fetch('api/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            // Reload member data
            setTimeout(() => {
                loadMemberData();
            }, 1000);
        } else {
            showAlert(data.message || 'अपडेट विफल', 'danger');
            disableFieldEdit(fieldId);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('अपडेट करने में त्रुटि: ' + error.message, 'danger');
        disableFieldEdit(fieldId);
    });
}

// Disable field edit
function disableFieldEdit(fieldId) {
    loadMemberData();
}

// Toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}

// Logout member
function logoutMember() {
    if (!confirm('क्या आप निश्चित रूप से लॉगआउट करना चाहते हैं?')) {
        return;
    }
    
    fetch('api/logout.php', {
        method: 'POST'
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message || 'लॉगआउट विफल', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('लॉगआउट में त्रुटि: ' + error.message, 'danger');
    });
}

// Show alert message
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        // Create container if it doesn't exist
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.style.position = 'fixed';
        container.style.top = '80px';
        container.style.right = '20px';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
        return showAlert(message, type);
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    alertContainer.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebar && sidebarToggle && window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
    }
});
