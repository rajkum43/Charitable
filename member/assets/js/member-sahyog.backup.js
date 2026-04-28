// Member Sahyog JavaScript - Dynamic Beneficiary System
// यह system automatically सबसे कम sahyog पाने वाले को दिखाता है

// ===== GLOBAL HELPER FUNCTIONS =====
// Sidebar toggle function
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Load section function (for navbar links)
function loadSection(sectionName) {
    console.log('Loading section:', sectionName);
}

// Logout function
function logoutMember() {
    if (confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
        window.location.href = '../includes/logout.php';
    }
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

// Load beneficiary information - AUTO CALLED
window.loadBeneficiaryInfo = function() {
    const button = document.querySelector('.view-beneficiary-btn');
    
    if (!button) return;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>लोड हो रहा है...';

    const formData = new FormData();
    formData.append('action', 'verify_member');
    formData.append('member_id', CURRENT_MEMBER_ID);
    formData.append('mobile', ''); // Empty for auto-load

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
    // Display member info
    const memberInfo = document.getElementById('memberInfo');
    if (memberInfo) {
        const memberNameDisplay = document.getElementById('memberNameDisplay');
        const memberIdDisplay = document.getElementById('memberIdDisplay');
        if (memberNameDisplay) memberNameDisplay.textContent = data.member.full_name;
        if (memberIdDisplay) memberIdDisplay.textContent = data.member.member_id;
        memberInfo.style.display = 'block';
    }

    // Display beneficiary or no beneficiary message
    if (data.current_beneficiary) {
        window.displayCurrentBeneficiary(data.current_beneficiary);
    } else {
        window.displayNoBeneficiary();
    }

    // Hide button
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
                    <!-- Beneficiary Profile -->
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

                    <!-- Progress Bar -->
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

                    <!-- About this beneficiary -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>विवरण
                        </h6>
                        <p class="mb-0">
                            ${beneficiary.full_name} को ${beneficiaryType} के लिए सहायता की आवश्यकता है। 
                            कृपया उन्हें अपना योगदान दें और महत्वपूर्ण क्षण में परिवार का हिस्सा बन जाएं।
                        </p>
                    </div>

                    <!-- Donate Now Button -->
                    <button type="button" class="btn btn-primary btn-lg w-100 donate-now-btn" 
                            onclick="window.togglePaymentDetails('${beneficiary.member_id}', this)">
                        <i class="fas fa-gift me-2"></i>दान करें (Donate Now)
                    </button>

                    <!-- Payment Details Section (Hidden by default) -->
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

    // Open payment modal
    window.openPaymentModal = function(beneficiaryId) {
        if (!currentMember) {
            alert('पहले सदस्य को सत्यापित करें');
            return;
        }

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
                displayPaymentOptions(data.data, beneficiaryId);
                paymentModal.show();
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('भुगतान विवरण लोड करने में विफल');
        });
    };

    // Display payment options
    function displayPaymentOptions(paymentData, beneficiaryId) {
        let html = `
            <h5 class="mb-4">
                <i class="fas fa-credit-card me-2"></i>भुगतान विधि चुनें
            </h5>
        `;

        if (paymentData.upi) {
            html += `
                <div class="payment-method mb-4">
                    <h6><i class="fas fa-mobile me-2"></i>UPI से भुगतान करें</h6>
                    <div id="qrCode" class="text-center mb-3"></div>
                    <div class="alert alert-info mb-3">
                        <strong>UPI ID:</strong> ${paymentData.upi}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" 
                            onclick="submitUPIPayment('${beneficiaryId}')">
                        UPI ऐप से भुगतान करें
                    </button>
                    <small class="text-muted d-block">
                        या QR कोड स्कैन करें
                    </small>
                </div>
            `;

            // Generate QR code
            setTimeout(() => {
                const qrContainer = document.getElementById('qrCode');
                if (qrContainer) {
                    qrContainer.innerHTML = '';
                    const upiString = `upi://pay?pa=${paymentData.upi}&pn=BRCT&am=${paymentData.amount}&tr=SAHYOG${Date.now()}`;
                    new QRCode(qrContainer, {
                        text: upiString,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#ffffff'
                    });
                }
            }, 100);
        }

        if (paymentData.bank) {
            html += `
                <div class="payment-method mb-4">
                    <h6><i class="fas fa-university me-2"></i>बैंक ट्रांसफर</h6>
                    <div class="alert alert-info mb-3">
                        <p><strong>खाता नाम:</strong> ${paymentData.bank.account_name || '-'}</p>
                        <p><strong>खाता नंबर:</strong> ${paymentData.bank.account_number || '-'}</p>
                        <p><strong>IFSC:</strong> ${paymentData.bank.ifsc || '-'}</p>
                        <p><strong>बैंक नाम:</strong> ${paymentData.bank.bank_name || '-'}</p>
                        <p class="mb-0 fw-bold"><strong>राशि: ₹${paymentData.amount}</strong></p>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" 
                            onclick="submitBankPayment('${beneficiaryId}')">
                        भुगतान की पुष्टि करें
                    </button>
                </div>
            `;
        }

        document.getElementById('paymentDetails').innerHTML = html;
    }

    // Submit UPI payment proof
    window.submitUPIPayment = function(beneficiaryId) {
        const screenshotInput = document.createElement('input');
        screenshotInput.type = 'file';
        screenshotInput.accept = 'image/*,.pdf';
        screenshotInput.onchange = function() {
            uploadPaymentProof(beneficiaryId, this.files[0], 'UPI');
        };
        screenshotInput.click();
    };

    // Submit bank payment proof
    window.submitBankPayment = function(beneficiaryId) {
        const screenshotInput = document.createElement('input');
        screenshotInput.type = 'file';
        screenshotInput.accept = 'image/*,.pdf';
        screenshotInput.onchange = function() {
            uploadPaymentProof(beneficiaryId, this.files[0], 'Bank Transfer');
        };
        screenshotInput.click();
    };

    // Upload payment proof
    function uploadPaymentProof(beneficiaryId, file, method) {
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert('कृपया JPG, PNG या PDF फाइल चुनें');
            return;
        }

        if (file.size > 500 * 1024) {
            alert('फाइल का आकार 500KB से अधिक नहीं होना चाहिए');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'record_payment');
        formData.append('beneficiary_id', beneficiaryId);
        formData.append('member_id', currentMember.member_id);
        formData.append('amount', 50);
        formData.append('payment_method', method);

        showLoading();

        fetch('../api/dynamic_sahyog.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('भुगतान दर्ज किया गया! धन्यवाद आपके सहायता के लिए।');
                paymentModal.hide();
                
                // Show next beneficiary
                if (data.data.next_beneficiary) {
                    displayCurrentBeneficiary(data.data.next_beneficiary);
                } else {
                    displayNoBeneficiary();
                }
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('भुगतान दर्ज करने में विफल');
        })
        .finally(() => hideLoading());
    }

    // Loading indicators
    function showLoading() {
        verificationForm.disabled = true;
        const btn = verificationForm.querySelector('button');
        if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>प्रक्रिया जारी है...';
    }

    function hideLoading() {
        verificationForm.disabled = false;
        const btn = verificationForm.querySelector('button');
        if (btn) btn.innerHTML = '<i class="fas fa-search me-2"></i>सत्यापित करें';
    }

    // Helper function
    window.formatDate = function(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('hi-IN', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    };
});
