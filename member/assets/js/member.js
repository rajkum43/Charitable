// Member Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    
    // Initialize sidebar toggle
    initializeSidebarToggle();
    
    // Load member data on page load
    loadMemberData();
    
    // Load referral count
    loadReferralCount();
    
    // Load active donations
    loadActiveDonations();
    
    // Set up event listeners
    setupEventListeners();
    
    // Close sidebar when clicking outside on mobile
    closeSidebarOnClickOutside();

    // If the page is opened with a section hash, show that section.
    const hash = window.location.hash.replace('#', '');
    if (hash) {
        loadSection(hash);
    }
});

// Load member data
function loadMemberData() {
    fetch('../api/get_member_data.php')
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

// Load referral count
function loadReferralCount() {
    fetch('../api/get_member_referral_count.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('referralCount').textContent = data.referral_count || 0;
            } else {
                console.error('Error loading referral count:', data.error);
                document.getElementById('referralCount').textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('referralCount').textContent = '0';
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
    
    // Display renewal date if available
    const renewalDate = data.renew_exp_date ? new Date(data.renew_exp_date).toLocaleDateString('hi-IN') : 'N/A';
    document.getElementById('memberStatus').innerHTML = 
        (data.membership_status || 'निष्क्रिय') + '<br><small>अंतिम नवीनीकरण: ' + renewalDate + '</small>';
    
    document.getElementById('joinDate').textContent = data.created_at || 'N/A';
    
    // Display renewal expiry date
    const renewalExpiryDate = data.renew_exp_date ? new Date(data.renew_exp_date).toLocaleDateString('hi-IN') : 'N/A';
    document.getElementById('renewalDate').textContent = renewalExpiryDate;
    
    // Populate info card
    document.getElementById('memberID2').textContent = data.member_id || 'N/A';
    document.getElementById('infoGender').textContent = data.gender || 'N/A';
    document.getElementById('infoMobile').textContent = data.mobile_number || 'N/A';
    document.getElementById('infoDistrict').textContent = data.district || 'N/A';
    document.getElementById('infoBlock').textContent = data.block || 'N/A';
    document.getElementById('infoFather').textContent = data.father_husband_name || 'N/A';
    document.getElementById('infoNominee').textContent = data.nominee_name || 'N/A';
    document.getElementById('infoPoll').textContent = data.poll_option || 'N/A';
    document.getElementById('infoJoinDate').textContent = data.created_at || 'N/A';
    document.getElementById('infoAddress').textContent = data.permanent_address || 'N/A';
    
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
        'membershipJoinDate': data.created_at,
        'membershipPoll': data.poll_option,
        'membershipNominee': data.nominee_name,
        'membershipGender': data.gender,
        'membershipMobile': data.mobile_number,
        'membershipAddress': data.permanent_address,
        'membershipUniqueId': data.member_id
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
    
    fetch('../api/update_profile.php', {
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
    
    fetch('../api/logout.php', {
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

// ===========================
// Active Donations Functionality
// ===========================

// Global variables for donations
let donationsData = [];

// Load active donations
function loadActiveDonations() {
    const container = document.getElementById('activeDonationsContainer');
    
    if (!container) return;
    
    fetch(window.API_URL + 'get_member_donations.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                donationsData = data.data.donations;
                renderActiveDonations();
            } else {
                console.error('API Error:', data.message);
                showNoActiveDonations();
            }
        })
        .catch(error => {
            console.error('Error loading donations:', error);
            showNoActiveDonations();
        });
}

// Render active donations
function renderActiveDonations() {
    const container = document.getElementById('activeDonationsContainer');
    
    if (!container || donationsData.length === 0) {
        showNoActiveDonations();
        return;
    }
    
    // Clear loading state
    container.innerHTML = '';
    
    // Render up to 3 donation cards (to keep dashboard concise)
    const maxCards = 3;
    const cardsToShow = donationsData.slice(0, maxCards);
    
    cardsToShow.forEach((donation, index) => {
        const card = createDonationCard(donation);
        container.appendChild(card);
    });
    
    // If there are more than 3, add a "View All" link
    if (donationsData.length > maxCards) {
        const viewAllDiv = document.createElement('div');
        viewAllDiv.className = 'text-center mt-3';
        viewAllDiv.innerHTML = `
            <a href="member_donation.php" class="btn btn-outline-primary">
                <i class="fas fa-eye me-2"></i>सभी दान अवसर देखें (${donationsData.length - maxCards} और)
            </a>
        `;
        container.appendChild(viewAllDiv);
    }
}

// Create donation card element (simplified version)
function createDonationCard(donation) {
    const isDeathClaim = donation.application_type === 'Death_Claims' || donation.application_type === 'Death';
    const cardTypeClass = isDeathClaim ? 'death' : 'vivah';
    const cardIcon = isDeathClaim ? 'fa-heart-broken' : 'fa-ring';
    const cardTitle = isDeathClaim ? 'मृत्यु सहायता' : 'बेटी विवाह सहायता';
    const badgeText = isDeathClaim ? 'मृत्यु दावा' : 'विवाह सहायता';
    
    // Calculate days remaining
    const expireDate = new Date(donation.expire_date);
    const today = new Date();
    const daysRemaining = Math.ceil((expireDate - today) / (1000 * 60 * 60 * 24));
    const isExpiringSoon = daysRemaining < 2;
    
    const card = document.createElement('div');
    card.className = 'donation-card';
    card.innerHTML = `
        <!-- Card Header -->
        <div class="donation-card-header ${cardTypeClass}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-2">
                        <i class="fas fa-${cardIcon} me-2"></i>${cardTitle}
                    </h5>
                    <div class="donation-badge">${badgeText}</div>
                </div>
                <div class="text-end">
                    <div class="poll-option-badge">${escapeHtml(donation.poll_option)}</div>
                    <small class="d-block mt-2">पोल विकल्प</small>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="donation-body">
            <!-- Main Info -->
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">${isDeathClaim ? 'दिवंगत सदस्य नाम' : 'बेटी का नाम'}</div>
                    <div class="info-value">
                        ${escapeHtml(isDeathClaim ? 
                            (donation.full_name || donation.deceased_name || 'N/A') : 
                            donation.bride_name
                        )}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">${isDeathClaim ? 'मृत्यु तारीख' : 'विवाह तारीख'}</div>
                    <div class="info-value">${formatDate(isDeathClaim ? donation.death_date : donation.wedding_date)}</div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">आवेदनकर्ता का नाम</div>
                    <div class="info-value">
                        ${escapeHtml(isDeathClaim ? 
                            (donation.applicant_name || donation.nominee_name || 'N/A') : 
                            donation.member_name
                        )}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">स्थान</div>
                    <div class="info-value">
                        ${escapeHtml(isDeathClaim ? 
                            'N/A' : 
                            ((donation.city || '') + ', ' + (donation.district || ''))
                        )}
                    </div>
                </div>
            </div>

            <!-- Poll Timeline -->
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <strong>दान अवधि:</strong>
                    <span class="date-badge">${formatDate(donation.start_date)}</span>
                    से
                    <span class="date-badge ${isExpiringSoon ? 'expire-soon' : ''}">
                        ${formatDate(donation.expire_date)}
                    </span>
                    <span class="badge bg-warning text-dark ms-2">${daysRemaining} दिन बाकी</span>
                </small>
            </div>

            <!-- Action Buttons -->
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <a href="member_donation.php" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-hand-holding-heart me-2"></i>दान करें
                </a>
            </div>
        </div>
    `;
    
    return card;
}

// Show no active donations message
function showNoActiveDonations() {
    const container = document.getElementById('activeDonationsContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-hand-holding-heart fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">कोई सक्रिय दान अवसर नहीं</h5>
            <p class="text-muted">वर्तमान में आपके पोल विकल्प के लिए कोई सक्रिय दान अनुरोध नहीं हैं।</p>
            <a href="member_donation.php" class="btn btn-outline-primary">
                <i class="fas fa-eye me-2"></i>सभी अवसर देखें
            </a>
        </div>
    `;
}

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('hi-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
    }
});
