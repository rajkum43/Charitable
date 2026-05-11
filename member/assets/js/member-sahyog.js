// Member Sahyog JavaScript - Dynamic Beneficiary System
// यह system automatically सबसे कम sahyog पाने वाले को दिखाता है

// ===== GLOBAL HELPER FUNCTIONS =====
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

function loadSection(sectionName) {
    const sectionMap = {
        'dashboard': 'index.php',
        'profile': 'index.php#profile',
        'membership': 'index.php#membership',
        'payment': 'index.php#payment',
        'documents': 'index.php#documents',
        'settings': 'index.php#settings'
    };

    if (sectionMap[sectionName]) {
        window.location.href = sectionMap[sectionName];
    } else {
        window.location.href = 'index.php';
    }
}

function logoutMember() {
    if (!confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
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
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'लॉगआउट विफल');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('लॉगआउट में त्रुटि: ' + error.message);
    });
}

// Helper function - Format date
window.formatDate = function(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('hi-IN', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// ===== MAIN API FUNCTIONS =====

// Load beneficiary information
window.loadBeneficiaryInfo = function() {
    const button = document.querySelector('.view-beneficiary-btn');
    
    if (!button) return;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>लोड हो रहा है...';

    const formData = new FormData();
    formData.append('action', 'verify_member');
    formData.append('member_id', CURRENT_MEMBER_ID);
    formData.append('mobile', '');

    fetch('../api/dynamic_sahyog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.displayMemberAndBeneficiary(data.data);
        } else {
            alert('त्रुटि: ' + data.message);
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-search me-2"></i>सहायता की आवश्यकता वाले को देखें';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('सूचना लोड करने में विफल');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-search me-2"></i>सहायता की आवश्यकता वाले को देखें';
    });
};

// Display member and beneficiary information
window.displayMemberAndBeneficiary = function(data) {
    const memberInfo = document.getElementById('memberInfo');
    if (memberInfo) {
        const memberNameDisplay = document.getElementById('memberNameDisplay');
        const memberIdDisplay = document.getElementById('memberIdDisplay');
        if (memberNameDisplay) memberNameDisplay.textContent = data.member.full_name;
        if (memberIdDisplay) memberIdDisplay.textContent = data.member.member_id;
        memberInfo.style.display = 'block';
    }

    // Update sidebar member info
    const sidebarMemberName = document.getElementById('sidebarMemberName');
    const sidebarMemberId = document.getElementById('sidebarMemberId');
    if (sidebarMemberName) sidebarMemberName.textContent = data.member.full_name;
    if (sidebarMemberId) sidebarMemberId.textContent = 'ID: ' + data.member.member_id;

    // Update navbar member name
    const navbarMemberName = document.getElementById('navbarMemberName');
    if (navbarMemberName) navbarMemberName.textContent = data.member.full_name;

    // Update navbar dropdown member name
    const dropdownMemberName = document.getElementById('dropdownMemberName');
    if (dropdownMemberName) dropdownMemberName.textContent = data.member.full_name;

    if (data.current_beneficiary) {
        window.displayCurrentBeneficiary(data.current_beneficiary);
    } else {
        window.displayNoBeneficiary();
    }

    const button = document.querySelector('.view-beneficiary-btn');
    if (button) {
        button.style.display = 'none';
    }
};

// Display current beneficiary
window.displayCurrentBeneficiary = function(beneficiary) {
    const beneficiaryType = beneficiary.type === 'vivah' ? 'विवाह सहायता' : 'मृत्यु लाभ';
    const progress = beneficiary.total_collected || 0;
    const targetAmount = 5000;
    const progressPercentage = (progress / targetAmount) * 100;

    const html = `
        <div class="col-lg-8 mx-auto">
            <div class="card beneficiary-card shadow-lg">
                <div class="card-header bg-gradient text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-hands-helping me-2"></i>
                        सहायता की आवश्यकता
                    </h4>
                </div>
                <div class="card-body">
                    <div class="beneficiary-profile mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-2">${beneficiary.full_name}</h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-id-card me-2"></i>ID: ${beneficiary.member_id}
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-tag me-2"></i>${beneficiaryType}
                                </p>
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar me-2"></i>आवेदन स्वीकृत: ${window.formatDate(beneficiary.approved_date)}
                                </small>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="donation-badge">
                                    <div class="donation-amount">
                                        ₹<strong>${progress}</strong>
                                    </div>
                                    <small class="text-muted">एकत्र किया गया</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">संग्रह प्रगति</span>
                            <span>${Math.round(progressPercentage)}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: ${Math.min(progressPercentage, 100)}%"></div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            लक्ष्य: ₹${targetAmount} | अभी एकत्र: ₹${progress}
                        </small>
                    </div>

                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>विवरण
                        </h6>
                        <p class="mb-0">
                            ${beneficiary.full_name} को ${beneficiaryType} के लिए सहायता की आवश्यकता है। 
                            कृपया उन्हें अपना योगदान दें और महत्वपूर्ण क्षण में परिवार का हिस्सा बन जाएं।
                        </p>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg w-100 donate-now-btn" 
                            onclick="window.togglePaymentDetails('${beneficiary.member_id}', this)">
                        <i class="fas fa-gift me-2"></i>दान करें (Donate Now)
                    </button>

                    <div id="paymentDetailsSection" class="payment-details-toggle mt-4" style="display: none;">
                    </div>

                    <small class="text-muted d-block mt-3 text-center">
                        <i class="fas fa-shield-alt me-1"></i>
                        सभी लेनदेन सुरक्षित और एन्क्रिप्टेड हैं
                    </small>
                </div>
            </div>
        </div>
    `;

    const beneficiaryContainer = document.getElementById('beneficiaryContainer');
    if (beneficiaryContainer) {
        beneficiaryContainer.innerHTML = html;
        beneficiaryContainer.style.display = 'block';
    }
};

// Toggle payment details display
window.togglePaymentDetails = function(beneficiaryId, buttonElement) {
    const detailsSection = document.getElementById('paymentDetailsSection');
    
    if (!detailsSection) return;
    
    if (detailsSection.style.display === 'none') {
        detailsSection.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">भुगतान जानकारी लोड हो रही है...</span>
                </div>
                <p class="text-muted mt-2">कृपया प्रतीक्षा करें...</p>
            </div>
        `;
        detailsSection.style.display = 'block';

        buttonElement.classList.add('active');
        buttonElement.innerHTML = '<i class="fas fa-chevron-up me-2"></i>विवरण छिपाएं';

        window.fetchAndDisplayPaymentDetailsForToggle(beneficiaryId);
    } else {
        detailsSection.style.display = 'none';
        buttonElement.classList.remove('active');
        buttonElement.innerHTML = '<i class="fas fa-gift me-2"></i>दान करें (Donate Now)';
    }
};

// Fetch and display payment details
window.fetchAndDisplayPaymentDetailsForToggle = function(beneficiaryId) {
    const formData = new FormData();
    formData.append('action', 'get_payment_details');
    formData.append('beneficiary_id', beneficiaryId);

    fetch('../api/dynamic_sahyog.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.displayPaymentDetailsInlineToggle(data.data);
        } else {
            const detailsSection = document.getElementById('paymentDetailsSection');
            if (detailsSection) {
                detailsSection.innerHTML = 
                    '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>भुगतान विवरण लोड नहीं हो सके</div>';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const detailsSection = document.getElementById('paymentDetailsSection');
        if (detailsSection) {
            detailsSection.innerHTML = 
                '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle me-2"></i>त्रुटि: भुगतान विवरण लोड करने में विफल</div>';
        }
    });
};

// Display payment details inline
window.displayPaymentDetailsInlineToggle = function(paymentData) {
    let html = '<div class="payment-details-inline">';

    if (paymentData.upi) {
        html += `
            <div class="payment-method-inline upi-section mb-4">
                <h6 class="mb-3">
                    <i class="fas fa-mobile-alt me-2 text-primary"></i>
                    <strong>UPI से दान करें</strong>
                </h6>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0 text-center">
                        <div id="qrCodeInline" class="qr-code-container"></div>
                        <small class="text-muted d-block mt-2">QR कोड स्कैन करें</small>
                    </div>
                    <div class="col-md-6">
                        <div class="upi-details">
                            <p class="mb-2"><strong>UPI ID:</strong></p>
                            <div class="upi-copy-box mb-3">
                                <input type="text" class="form-control fw-bold" value="${paymentData.upi}" readonly>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="window.copyToClipboard('${paymentData.upi}', 'UPI ID')" 
                                        title="कॉपी करें">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                UPI ऐप खोलें और UPI ID डालें
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    if (paymentData.bank) {
        html += `
            <div class="payment-method-inline bank-section">
                <h6 class="mb-3">
                    <i class="fas fa-university me-2 text-success"></i>
                    <strong>बैंक ट्रांसफर</strong>
                </h6>
                <div class="bank-details-box p-3 bg-light rounded">
                    <div class="detail-row mb-2">
                        <span class="label">खाता नाम:</span>
                        <strong>${paymentData.bank.account_name || 'N/A'}</strong>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="label">खाता नंबर:</span>
                        <strong>${paymentData.bank.account_number || 'N/A'}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="window.copyToClipboard('${paymentData.bank.account_number}', 'खाता नंबर')" 
                                title="कॉपी करें">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="label">IFSC कोड:</span>
                        <strong>${paymentData.bank.ifsc || 'N/A'}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="window.copyToClipboard('${paymentData.bank.ifsc}', 'IFSC कोड')" 
                                title="कॉपी करें">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="detail-row mb-2">
                        <span class="label">बैंक नाम:</span>
                        <strong>${paymentData.bank.bank_name || 'N/A'}</strong>
                    </div>
                    <div class="detail-row border-top pt-2 mt-2">
                        <span class="label">दान की राशि:</span>
                        <strong class="text-success fs-5">₹${paymentData.amount}</strong>
                    </div>
                </div>
            </div>
        `;
    }

    html += '</div>';
    const paymentDetailsSection = document.getElementById('paymentDetailsSection');
    if (paymentDetailsSection) {
        paymentDetailsSection.innerHTML = html;
    }

    // Generate QR code
    if (paymentData.upi) {
        setTimeout(() => {
            const qrContainer = document.getElementById('qrCodeInline');
            if (qrContainer) {
                qrContainer.innerHTML = '';
                const upiString = `upi://pay?pa=${paymentData.upi}&pn=BRCT&am=${paymentData.amount}&tr=SAHYOG${Date.now()}`;
                new QRCode(qrContainer, {
                    text: upiString,
                    width: 180,
                    height: 180,
                    colorDark: '#000000',
                    colorLight: '#ffffff'
                });
            }
        }, 100);
    }
};

// Copy to clipboard
window.copyToClipboard = function(text, label) {
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> कॉपी हुआ!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }
    }).catch(() => {
        alert('कॉपी करने में विफल');
    });
};

// Display no beneficiary
window.displayNoBeneficiary = function() {
    const html = `
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-smile-wink fa-3x text-success mb-3"></i>
                    <h4>सभी को पर्याप्त सहायता मिल गई!</h4>
                    <p class="text-muted">वर्तमान में सभी अनुमोदित लाभार्थियों को पर्याप्त धन मिल चुका है।</p>
                    <p class="text-muted">जल्द ही नए लाभार्थी जोड़े जाएंगे।</p>
                </div>
            </div>
        </div>
    `;

    const beneficiaryContainer = document.getElementById('beneficiaryContainer');
    if (beneficiaryContainer) {
        beneficiaryContainer.innerHTML = html;
        beneficiaryContainer.style.display = 'block';
    }
};
