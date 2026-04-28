// Member Sahyog JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const verificationForm = document.getElementById('verificationForm');
    const pollsContainer = document.getElementById('pollsContainer');
    const memberInfo = document.getElementById('memberInfo');
    const pollsList = document.getElementById('pollsList');
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    
    let currentMember = null;
    let currentPoll = null;

    // Handle verification form submission
    verificationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        verifyMember();
    });

    // Verify member function
    function verifyMember() {
        const memberId = document.getElementById('memberId').value.trim();
        const mobile = document.getElementById('mobile').value.trim();

        if (!memberId || !mobile) {
            alert('कृपया सभी फील्ड भरें');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'verify_member');
        formData.append('member_id', memberId);
        formData.append('mobile', mobile);

        showLoading();

        fetch('../api/poll_member_verification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentMember = data.data.member;
                displayMemberInfo(data.data.member);
                displayPolls(data.data.polls);
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('सत्यापन विफल। कृपया फिर से प्रयास करें।');
        })
        .finally(() => hideLoading());
    }

    // Display member information
    function displayMemberInfo(member) {
        document.getElementById('memberNameDisplay').textContent = member.full_name;
        document.getElementById('memberIdDisplay').textContent = member.member_id;
        memberInfo.style.display = 'block';
    }

    // Display polls
    function displayPolls(polls) {
        if (polls.length === 0) {
            pollsList.innerHTML = '<div class="col-12"><div class="alert alert-info">आपके लिए कोई सक्रिय पोल उपलब्ध नहीं है।</div></div>';
            pollsContainer.style.display = 'block';
            return;
        }

        pollsList.innerHTML = '';
        polls.forEach(poll => {
            const pollCard = createPollCard(poll);
            pollsList.appendChild(pollCard);
        });
        pollsContainer.style.display = 'block';
    }

    // Create poll card
    function createPollCard(poll) {
        const col = document.createElement('div');
        col.className = 'col-lg-6 mb-4';

        const pollType = poll.poll_type === 'vivah' ? 'विवाह सहायता' : 'मृत्यु लाभ';
        const paymentStatus = poll.payment_status || 'Pending';
        const statusBadgeClass = paymentStatus === 'Paid' ? 'badge-paid' : 
                                  paymentStatus === 'Pending' ? 'badge-pending' : 'badge-failed';
        const statusText = paymentStatus === 'Paid' ? 'भुगतान किया गया' :
                          paymentStatus === 'Pending' ? 'लंबित' : 'विफल';

        const paidCount = poll.paid_count || 0;
        const progressPercentage = (paidCount / poll.total_members) * 100;

        col.innerHTML = `
            <div class="card poll-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="poll-header">${poll.poll_name}</h5>
                            <small class="text-muted">पोल कोड: <strong>${poll.poll_code}</strong></small>
                        </div>
                        <span class="badge ${statusBadgeClass}">${statusText}</span>
                    </div>

                    <div class="alert alert-info mb-3">
                        <strong>लाभार्थी:</strong> ${poll.beneficiary_name}
                    </div>

                    <div class="amount-display">
                        <span class="currency">₹</span>${poll.donation_amount}
                    </div>

                    <div class="progress-section">
                        <div class="progress">
                            <div class="progress-bar" style="width: ${progressPercentage}%">
                                ${Math.round(progressPercentage)}%
                            </div>
                        </div>
                        <div class="progress-text">
                            ${paidCount} / ${poll.total_members} सदस्यों ने भुगतान किया
                        </div>
                    </div>

                    ${paymentStatus === 'Pending' ? `
                        <button class="btn btn-success w-100 mt-3 pay-btn" data-poll-id="${poll.id}">
                            <i class="fas fa-money-bill-wave me-2"></i>अभी भुगतान करें
                        </button>
                    ` : `
                        <button class="btn btn-info w-100 mt-3 view-details-btn" data-poll-id="${poll.id}">
                            <i class="fas fa-eye me-2"></i>विवरण देखें
                        </button>
                    `}
                </div>
            </div>
        `;

        // Add event listeners
        const payBtn = col.querySelector('.pay-btn');
        if (payBtn) {
            payBtn.addEventListener('click', function() {
                currentPoll = poll;
                showPaymentForm(poll);
                paymentModal.show();
            });
        }

        const viewBtn = col.querySelector('.view-details-btn');
        if (viewBtn) {
            viewBtn.addEventListener('click', function() {
                currentPoll = poll;
                showPaymentDetails(poll);
                paymentModal.show();
            });
        }

        return col;
    }

    // Show payment form
    function showPaymentForm(poll) {
        const paymentDetails = document.getElementById('paymentDetails');
        paymentDetails.innerHTML = `
            <form id="paymentForm" class="needs-validation" novalidate>
                <div class="beneficiary-info">
                    <strong>लाभार्थी:</strong> ${poll.beneficiary_name}<br>
                    <strong>भुगतान राशि:</strong> ₹${poll.donation_amount}
                </div>

                <div class="mb-3">
                    <label for="paymentMethod" class="form-label">भुगतान विधि</label>
                    <select class="form-control" id="paymentMethod" required>
                        <option value="">विधि चुनें</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">बैंक ट्रांसफर</option>
                        <option value="Cheque">चेक</option>
                    </select>
                </div>

                <div id="upiSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">UPI QR कोड</label>
                        <div id="qrCode" class="qr-container"></div>
                    </div>
                </div>

                <div id="bankSection" style="display: none;">
                    <div class="bank-details">
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">खाता:</span>
                            <span class="bank-detail-value">XXXX XXXX XXXX 1234</span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">IFSC:</span>
                            <span class="bank-detail-value">SBIN0001234</span>
                        </div>
                        <div class="bank-detail-row">
                            <span class="bank-detail-label">बैंक:</span>
                            <span class="bank-detail-value">भारतीय स्टेट बैंक</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="utrNumber" class="form-label">UTR / संदर्भ संख्या</label>
                    <input type="text" class="form-control" id="utrNumber" placeholder="UTR नंबर दर्ज करें" required>
                </div>

                <div class="mb-3">
                    <label for="transactionId" class="form-label">लेनदेन ID (यदि है)</label>
                    <input type="text" class="form-control" id="transactionId" placeholder="लेनदेन ID दर्ज करें">
                </div>

                <div class="mb-3">
                    <label for="screenshot" class="form-label">स्क्रीनशॉट अपलोड करें</label>
                    <input type="file" class="form-control" id="screenshot" accept=".jpg,.jpeg,.png,.pdf" required>
                    <small class="text-muted">JPG, PNG या PDF (अधिकतम 500KB)</small>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check me-2"></i>भुगतान सबमिट करें
                </button>
            </form>
        `;

        // Handle payment method change
        document.getElementById('paymentMethod').addEventListener('change', function() {
            const upiSection = document.getElementById('upiSection');
            const bankSection = document.getElementById('bankSection');

            if (this.value === 'UPI') {
                upiSection.style.display = 'block';
                bankSection.style.display = 'none';
                generateQRCode(poll);
            } else if (this.value === 'Bank Transfer') {
                upiSection.style.display = 'none';
                bankSection.style.display = 'block';
            } else {
                upiSection.style.display = 'none';
                bankSection.style.display = 'none';
            }
        });

        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitPayment(poll);
        });
    }

    // Generate QR Code for UPI
    function generateQRCode(poll) {
        const qrContainer = document.getElementById('qrCode');
        qrContainer.innerHTML = ''; // Clear previous QR code
        
        // Generate UPI string
        const upiString = `upi://pay?pa=beneficiary@bank&pn=${encodeURIComponent(poll.beneficiary_name)}&am=${poll.donation_amount}&tn=Sahyog`;
        
        new QRCode(qrContainer, {
            text: upiString,
            width: 250,
            height: 250,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    // Submit payment
    function submitPayment(poll) {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const utrNumber = document.getElementById('utrNumber').value.trim();
        const transactionId = document.getElementById('transactionId').value.trim();
        const screenshot = document.getElementById('screenshot').files[0];

        if (!paymentMethod || !utrNumber) {
            alert('कृपया सभी आवश्यक फील्ड भरें');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'record_payment');
        formData.append('member_id', currentMember.member_id);
        formData.append('poll_id', poll.id);
        formData.append('amount', poll.donation_amount);
        formData.append('payment_method', paymentMethod);
        formData.append('utr_number', utrNumber);
        formData.append('transaction_id', transactionId);
        if (screenshot) {
            formData.append('screenshot', screenshot);
        }

        showLoading();

        fetch('../api/poll_payment_tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ ' + data.message);
                paymentModal.hide();
                // Refresh polls
                verifyMember();
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('भुगतान सबमिट करने में विफल');
        })
        .finally(() => hideLoading());
    }

    // Show payment details
    function showPaymentDetails(poll) {
        const paymentDetails = document.getElementById('paymentDetails');
        const paidCount = poll.paid_count || 0;
        const totalPaid = (poll.total_paid || 0) / 100;

        paymentDetails.innerHTML = `
            <div class="beneficiary-info">
                <h6>पोल विवरण</h6>
                <p><strong>नाम:</strong> ${poll.poll_name}</p>
                <p><strong>कोड:</strong> ${poll.poll_code}</p>
                <p><strong>लाभार्थी:</strong> ${poll.beneficiary_name}</p>
            </div>

            <div class="progress-section">
                <h6>संग्रह की गई राशि</h6>
                <div class="alert alert-success">
                    <strong>₹${totalPaid}</strong> का संग्रह हुआ है
                </div>
            </div>

            <div class="progress-section">
                <h6>भुगतान की स्थिति</h6>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: ${(paidCount / poll.total_members) * 100}%">
                        ${Math.round((paidCount / poll.total_members) * 100)}%
                    </div>
                </div>
                <div class="progress-text">
                    ${paidCount} / ${poll.total_members} सदस्यों ने भुगतान किया
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-check-circle me-2"></i>आपने इस पोल के लिए भुगतान कर दिया है।
            </div>
        `;
    }

    // Loading indicator
    function showLoading() {
        const spinner = document.createElement('div');
        spinner.id = 'loadingSpinner';
        spinner.className = 'loading';
        spinner.style.cssText = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;';
        document.body.appendChild(spinner);
    }

    function hideLoading() {
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.remove();
        }
    }
});
