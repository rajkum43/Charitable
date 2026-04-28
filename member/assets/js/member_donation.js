/**
 * Member Donation Page JavaScript
 * assets/js/member_donation.js
 */

// Global variables
let memberData = null;
let donationsData = [];

// ===========================
// Initialize on Page Load
// ===========================
document.addEventListener('DOMContentLoaded', function() {
    loadMemberDonations();
});

// ===========================
// Load Member Donations via API
// ===========================
async function loadMemberDonations() {
    const container = document.getElementById('donationCardsContainer');
    
    try {
        // Show loading state
        showLoadingState(true);
        
        // Fetch API
        const response = await fetch(window.API_URL + 'get_member_donations.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load donations');
        }
        
        // Debug log
        console.log('API Response:', data);
        console.log('Debug Info:', data.debug);
        console.log('Total donations:', data.data.donations.length);
        data.data.donations.forEach((d, i) => {
            console.log(`Donation ${i}:`, {
                type: d.application_type,
                claim: d.claim_number,
                name: d.full_name || d.deceased_name || d.bride_name
            });
        });
        
        // Store data globally
        memberData = data.data.member;
        donationsData = data.data.donations;
        
        // Update UI
        updateMemberInfo();
        renderDonationCards();
        
    } catch (error) {
        console.error('Error loading donations:', error);
        showToast('दान डेटा लोड करने में त्रुटि: ' + error.message, 'error');
        showNoDataMessage();
    } finally {
        showLoadingState(false);
    }
}

// ===========================
// Update Member Info Display
// ===========================
function updateMemberInfo() {
    if (!memberData) return;
    
    const pollOptionEl = document.getElementById('memberPollOption');
    const pollLabelEl = document.getElementById('pollOptionLabel');
    
    if (pollOptionEl) {
        pollOptionEl.textContent = memberData.poll_option ? memberData.poll_option : 'Not Assigned';
    }
    
    if (pollLabelEl) {
        if (memberData.poll_option) {
            pollLabelEl.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                <strong>आपका पोल विकल्प:</strong> 
                <span class="badge bg-info">${escapeHtml(memberData.poll_option)}</span>
                <span class="text-muted ms-2">(नीचे दिए गए सभी दान विकल्प ${escapeHtml(memberData.poll_option)} के लिए हैं)</span>
            `;
        }
    }
}

// ===========================
// Render Donation Cards
// ===========================
function renderDonationCards() {
    const container = document.getElementById('donationCardsContainer');
    
    if (!container) return;
    
    // Clear existing content
    container.innerHTML = '';
    
    if (donationsData.length === 0) {
        showNoDataMessage();
        return;
    }
    
    // Render each donation card
    donationsData.forEach((donation, index) => {
        const card = createDonationCard(donation);
        container.appendChild(card);
    });
}

// ===========================
// Create Donation Card Element
// ===========================
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

            <!-- Address -->
            <div class="info-item">
                <div class="info-label">पता</div>
                <div class="info-value">${escapeHtml(donation.address || 'N/A')}</div>
            </div>

            <!-- Payment Section -->
            <div class="payment-section">
                <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>भुगतान जानकारी</h6>

                ${renderUPISection(donation)}

                ${renderBankSection(donation)}
            </div>

            <!-- Poll Timeline -->
            <div class="mt-4 pt-3 border-top">
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
            <div class="mt-4 d-flex gap-2 flex-wrap">
                ${donation.upi_id ? `
                    <a href="upi://pay?pa=${encodeURIComponent(donation.upi_id)}&pn=${encodeURIComponent(donation.full_name || donation.bride_name || donation.applicant_name || donation.member_name || '')}" 
                       class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-money-check me-2"></i>अभी दान करें (UPI)
                    </a>
                ` : ''}
                
                <button class="btn btn-outline-secondary btn-sm" 
                        onclick="copyBankDetails(this, event)" 
                        data-bank-details='${JSON.stringify({
                            bank: donation.bank_name || '',
                            account: donation.account_number || '',
                            ifsc: donation.ifsc_code || '',
                            holder: donation.account_holder_name || ''
                        })}'>
                    <i class="fas fa-copy me-2"></i>जानकारी कॉपी करें
                </button>
            </div>

            <!-- Transaction Receipt Section -->
            <div class="mt-4 pt-4 border-top">
                <h6 class="mb-3"><i class="fas fa-receipt me-2"></i>दान की रसीद अपलोड करें</h6>
                
                <form class="transaction-form" onsubmit="handleTransactionSubmit(event, '${escapeHtml(donation.id)}', '${escapeHtml(donation.claim_number)}', '${escapeHtml(donation.application_type)}')">
                    
                    <!-- Donation Amount Input -->
                    <div class="mb-3">
                        <label for="donation_amount_${donation.id}" class="form-label">
                            <i class="fas fa-money-bill-wave me-1"></i>दान की राशि (रुपये में) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="donation_amount_${donation.id}"
                                   name="donation_amount"
                                   placeholder="जैसे: 1000, 5000, 10000"
                                   min="1"
                                   max="999999"
                                   step="1"
                                   required>
                        </div>
                        <small class="text-muted">दान की राशि को रुपये में दर्ज करें</small>
                    </div>

                    <!-- Transaction Number Input -->
                    <div class="mb-3">
                        <label for="txn_number_${donation.id}" class="form-label">
                            <i class="fas fa-hashtag me-1"></i>ट्रांजेक्शन नंबर या UTR नंबर <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="txn_number_${donation.id}"
                               name="transaction_number"
                               placeholder="जैसे: TXN12345678 या UTR123456789"
                               required
                               maxlength="100">
                        <small class="text-muted">बैंक हस्तांतरण या UPI से मिलने वाली ट्रांजेक्शन ID</small>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="receipt_${donation.id}" class="form-label">
                            <i class="fas fa-file-upload me-1"></i>रसीद अपलोड करें <span class="text-danger">*</span>
                        </label>
                        <div class="file-upload-wrapper">
                            <input type="file" 
                                   class="form-control" 
                                   id="receipt_${donation.id}"
                                   name="receipt_file"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required
                                   onchange="validateFile(this, ${donation.id})">
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>अनुमत प्रकार: PDF, JPG, PNG (अधिकतम 500 KB)
                                <br>
                                <i class="fas fa-magic me-1"></i><strong>तस्वीरें स्वचालित रूप से 300-500 KB तक संपीड़ित होती हैं</strong>
                            </small>
                            <div class="file-info mt-2" id="file_info_${donation.id}" style="display:none;">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <span id="file_name_${donation.id}"></span> 
                                    <span id="file_size_${donation.id}"></span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Remarks (Optional) -->
                    <div class="mb-3">
                        <label for="remarks_${donation.id}" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>टिप्पणी (वैकल्पिक)
                        </label>
                        <textarea class="form-control" 
                                  id="remarks_${donation.id}"
                                  name="remarks"
                                  rows="2"
                                  placeholder="कोई अतिरिक्त जानकारी..."
                                  maxlength="500"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success transaction-submit-btn" id="submit_btn_${donation.id}">
                            <i class="fas fa-cloud-upload-alt me-2"></i>रसीद जमा करें
                        </button>
                    </div>

                    <!-- Status Messages -->
                    <div class="transaction-status mt-3" id="status_${donation.id}" style="display:none;">
                        <div class="alert" id="alert_${donation.id}"></div>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    return card;
}

// ===========================
// Render UPI Section
// Render UPI Section
// ===========================
function renderUPISection(donation) {
    if (!donation.upi_id) return '';
    
    const isDeathClaim = donation.application_type === 'Death_Claims' || donation.application_type === 'Death';
    const recipientName = isDeathClaim ? 
        (donation.full_name || donation.deceased_name || donation.applicant_name || '') :
        (donation.bride_name || donation.member_name || '');
    
    const qrDataUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=${encodeURIComponent(donation.upi_id)}&pn=${encodeURIComponent(recipientName)}`;
    
    return `
        <div class="payment-method">
            <div class="payment-method-title">
                <i class="fas fa-mobile-alt text-primary"></i>
                UPI के माध्यम से भुगतान करें
            </div>
            <div class="qr-code-container">
                <img src="${qrDataUrl}" 
                     alt="UPI QR Code" 
                     title="UPI ID को स्कैन करें">
                <p class="small mt-2 mb-0">
                    <strong>UPI ID:</strong> ${escapeHtml(donation.upi_id)}
                </p>
            </div>
        </div>
    `;
}

// ===========================
// Render Bank Section
// ===========================
function renderBankSection(donation) {
    if (!donation.bank_name) return '';
    
    return `
        <div class="payment-method">
            <div class="payment-method-title">
                <i class="fas fa-university text-success"></i>
                बैंक खाते में भुगतान करें
            </div>
            <div class="bank-details">
                ${donation.bank_name ? `
                    <div class="bank-detail-item">
                        <strong>बैंक का नाम:</strong>
                        <span>${escapeHtml(donation.bank_name)}</span>
                    </div>
                ` : ''}

                ${donation.branch_name ? `
                    <div class="bank-detail-item">
                        <strong>शाखा:</strong>
                        <span>${escapeHtml(donation.branch_name)}</span>
                    </div>
                ` : ''}

                ${donation.account_holder_name ? `
                    <div class="bank-detail-item">
                        <strong>खाता धारक:</strong>
                        <span>${escapeHtml(donation.account_holder_name)}</span>
                    </div>
                ` : ''}

                ${donation.account_number ? `
                    <div class="bank-detail-item">
                        <strong>खाता संख्या:</strong>
                        <span>${escapeHtml(donation.account_number)}</span>
                    </div>
                ` : ''}

                ${donation.ifsc_code ? `
                    <div class="bank-detail-item">
                        <strong>IFSC कोड:</strong>
                        <span>${escapeHtml(donation.ifsc_code)}</span>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
}

// ===========================
// Copy Bank Details to Clipboard
// ===========================
function copyBankDetails(button, event) {
    event.preventDefault();
    
    try {
        const details = JSON.parse(button.dataset.bankDetails);
        const text = `बैंक: ${details.bank}\nखाता: ${details.account}\nIFSC: ${details.ifsc}\nनाम: ${details.holder}`;
        
        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-2"></i>कॉपी किया गया';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 2000);
            
            showToast('बैंक जानकारी कॉपी की गई', 'success');
        });
    } catch (e) {
        console.error('Copy error:', e);
        showToast('कॉपी करने में त्रुटि', 'error');
    }
}

// ===========================
// Show No Data Message
// ===========================
function showNoDataMessage() {
    const container = document.getElementById('donationCardsContainer');
    if (!container) return;
    
    container.innerHTML = `
        <div class="card">
            <div class="card-body no-donation-message">
                <div class="no-donation-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h5>अभी कोई दान अनुरोध उपलब्ध नहीं है</h5>
                <p class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    आपके पोल विकल्प के लिए वर्तमान में कोई सक्रिय दान अनुरोध नहीं है।
                </p>
                <p class="text-muted small">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>यदि आपने सभी उपलब्ध दानों के लिए योगदान दिया है,</strong> तो आप अन्य दान अनुरोधों के लिए बाद में वापस आ सकते हैं।
                </p>
            </div>
        </div>
    `;
}

// ===========================
// Loading State
// ===========================
function showLoadingState(show) {
    const container = document.getElementById('donationCardsContainer');
    if (!container) return;
    
    if (show) {
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">लोड हो रहा है...</span>
                </div>
                <p class="mt-3 text-muted">दान अनुरोध लोड हो रहे हैं...</p>
            </div>
        `;
    }
}

// ===========================
// Toast Notification
// ===========================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===========================
// Utility Functions
// ===========================
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('hi-IN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch (e) {
        return dateString || 'N/A';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Global variable to store compressed/processed files
let processedFiles = {};

// ===========================
// File Validation & Compression
// ===========================
function validateFile(input, donationId) {
    const file = input.files[0];
    const maxSize = 500 * 1024; // 500 KB
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    
    const fileInfoEl = document.getElementById(`file_info_${donationId}`);
    const fileNameEl = document.getElementById(`file_name_${donationId}`);
    const fileSizeEl = document.getElementById(`file_size_${donationId}`);
    const submitBtn = document.getElementById(`submit_btn_${donationId}`);
    
    if (!file) {
        fileInfoEl.style.display = 'none';
        submitBtn.disabled = false;
        // Clear stored file
        if (processedFiles[donationId]) {
            delete processedFiles[donationId];
        }
        return;
    }
    
    // Validate file type first
    if (!allowedTypes.includes(file.type)) {
        showToast(`फ़ाइल प्रकार समर्थित नहीं है। कृपया PDF या छवि (JPG/PNG) चुनें।`, 'error');
        input.value = '';
        fileInfoEl.style.display = 'none';
        submitBtn.disabled = true;
        return;
    }
    
    // Check if it's an image that needs compression
    if (file.type === 'image/jpeg' || file.type === 'image/png') {
        // For images: compress them
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>तस्वीर संपीड़ित की जा रही है...';
        fileInfoEl.innerHTML = '<small class="text-info"><i class="fas fa-hourglass-half me-1"></i>संपीड़न प्रक्रिया चल रही है...</small>';
        fileInfoEl.style.display = 'block';
        
        compressImage(file, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn, input);
    } else {
        // For PDFs: use as-is
        if (file.size > maxSize) {
            showToast(`फ़ाइल आकार 500 KB से अधिक है। कृपया एक छोटी फ़ाइल चुनें।`, 'error');
            input.value = '';
            fileInfoEl.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }
        
        // Store the PDF file
        processedFiles[donationId] = file;
        
        // Show file info
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = `(${(file.size / 1024).toFixed(2)} KB)`;
        fileInfoEl.innerHTML = `<small class="text-success"><i class="fas fa-check-circle me-1"></i><span id="file_name_${donationId}">${file.name}</span> <span id="file_size_${donationId}">(${(file.size / 1024).toFixed(2)} KB)</span></small>`;
        fileInfoEl.style.display = 'block';
        submitBtn.disabled = false;
    }
}

// ===========================
// Compress Image to Target Size (300-500 KB)
// ===========================
function compressImage(file, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn, input) {
    const targetMinSize = 300 * 1024; // 300 KB
    const targetMaxSize = 500 * 1024; // 500 KB
    const reader = new FileReader();
    
    reader.onload = function(event) {
        const img = new Image();
        
        img.onload = function() {
            // Create canvas
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set initial dimensions
            let width = img.width;
            let height = img.height;
            
            // Calculate scaling to fit max dimensions (no blow-up)
            const maxDimension = 1920; // Max dimension to prevent oversized images
            if (width > maxDimension || height > maxDimension) {
                const scale = Math.min(maxDimension / width, maxDimension / height);
                width *= scale;
                height *= scale;
            }
            
            canvas.width = width;
            canvas.height = height;
            
            // Draw image on canvas
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            ctx.drawImage(img, 0, 0, width, height);
            
            // Compress with adaptive quality
            let quality = 0.85; // Start with 85% quality
            let compressed = null;
            let compressedSize = null;
            let attempts = 0;
            const maxAttempts = 10;
            
            function tryCompress() {
                canvas.toBlob(
                    function(blob) {
                        compressedSize = blob.size;
                        compressed = blob;
                        
                        // Check if size is in target range
                        if (compressedSize >= targetMinSize && compressedSize <= targetMaxSize) {
                            // Perfect! Size is in range
                            saveCompressedFile(compressed, file.name, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn);
                        } else if (compressedSize > targetMaxSize && quality > 0.3) {
                            // Too large, reduce quality
                            quality -= 0.05;
                            attempts++;
                            if (attempts < maxAttempts) {
                                tryCompress();
                            } else {
                                // Max attempts reached, use current compression
                                saveCompressedFile(compressed, file.name, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn);
                            }
                        } else if (compressedSize < targetMinSize && quality < 0.95) {
                            // Can increase quality for better file size
                            quality += 0.03;
                            attempts++;
                            if (attempts < maxAttempts) {
                                tryCompress();
                            } else {
                                saveCompressedFile(compressed, file.name, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn);
                            }
                        } else {
                            // Acceptable size or quality limit reached
                            saveCompressedFile(compressed, file.name, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn);
                        }
                    },
                    file.type,
                    quality
                );
            }
            
            tryCompress();
        };
        
        img.onerror = function() {
            showToast('तस्वीर लोड करने में त्रुटि', 'error');
            input.value = '';
            fileInfoEl.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>रसीद जमा करें';
        };
        
        img.src = event.target.result;
    };
    
    reader.onerror = function() {
        showToast('फ़ाइल पढ़ने में त्रुटि', 'error');
        input.value = '';
        fileInfoEl.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>रसीद जमा करें';
    };
    
    reader.readAsDataURL(file);
}

// ===========================
// Save Compressed File
// ===========================
function saveCompressedFile(blob, originalName, donationId, fileInfoEl, fileNameEl, fileSizeEl, submitBtn) {
    const compressedSize = blob.size;
    const originalSizeKB = blob.size / 1024;
    
    // Create a new File object from the blob
    const ext = originalName.split('.').pop();
    const compressedFileName = `compressed_${Date.now()}.${ext}`;
    const compressedFile = new File([blob], compressedFileName, { type: blob.type });
    
    // Store the compressed file
    processedFiles[donationId] = compressedFile;
    
    // Show compression info
    fileInfoEl.innerHTML = `
        <small class="text-success">
            <i class="fas fa-check-circle me-1"></i>
            <span id="file_name_${donationId}">${originalName}</span>
            <span id="file_size_${donationId}">(संपीड़ित: ${originalSizeKB.toFixed(2)} KB)</span>
        </small>
    `;
    fileInfoEl.style.display = 'block';
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>रसीद जमा करें';
    
    showToast(`तस्वीर सफलतापूर्वक संपीड़ित: ${originalSizeKB.toFixed(2)} KB`, 'success');
}

// ===========================
// Handle Transaction Form Submission
// ===========================
async function handleTransactionSubmit(event, donationId, claimNumber, applicationType) {
    event.preventDefault();
    
    const form = event.target;
    const donationAmountInput = form.querySelector('input[name="donation_amount"]');
    const transactionNumberInput = form.querySelector('input[name="transaction_number"]');
    const fileInput = form.querySelector('input[name="receipt_file"]');
    const remarksInput = form.querySelector('textarea[name="remarks"]');
    const submitBtn = form.querySelector('.transaction-submit-btn');
    const statusDiv = document.getElementById(`status_${donationId}`);
    const alertDiv = document.getElementById(`alert_${donationId}`);
    
    // Validate inputs
    if (!donationAmountInput.value.trim()) {
        showToast('कृपया दान की राशि दर्ज करें', 'error');
        return;
    }
    
    const amount = parseFloat(donationAmountInput.value);
    if (isNaN(amount) || amount <= 0 || amount > 999999) {
        showToast('कृपया एक वैध राशि दर्ज करें (1 से 999999)', 'error');
        return;
    }
    
    if (!transactionNumberInput.value.trim()) {
        showToast('कृपया ट्रांजेक्शन नंबर दर्ज करें', 'error');
        return;
    }
    
    // Check if file has been processed (compressed for images or selected for PDF)
    if (!processedFiles[donationId] && !fileInput.files[0]) {
        showToast('कृपया रसीद फ़ाइल चुनें', 'error');
        return;
    }
    
    try {
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>अपलोड हो रहा है...';
        
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('claim_number', claimNumber);
        formData.append('application_type', applicationType);
        formData.append('donation_amount', donationAmountInput.value.trim());
        formData.append('transaction_number', transactionNumberInput.value.trim());
        
        // Use compressed/processed file if available, otherwise use original
        const fileToUpload = processedFiles[donationId] || fileInput.files[0];
        formData.append('receipt_file', fileToUpload);
        
        if (remarksInput.value.trim()) {
            formData.append('remarks', remarksInput.value.trim());
        }
        
        // Send request
        const response = await fetch(window.API_URL + 'upload_donation_receipt.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            // Success
            statusDiv.style.display = 'block';
            alertDiv.className = 'alert alert-success';
            
            // Get transaction ID from response
            const transactionId = data.data && data.data.transaction_id ? data.data.transaction_id : 'N/A';
            
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>सफल!</strong> ${data.message}
                <div class="mt-3">
                    <a href="${window.BASE_URL}member/generate_donation_receipt.php?${new URLSearchParams({txn_id: transactionId}).toString()}" 
                       target="_blank" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-file-pdf me-1"></i>रसीद देखें / View Receipt
                    </a>
                </div>
            `;
            
            // Reset form
            form.reset();
            document.getElementById(`file_info_${donationId}`).style.display = 'none';
            
            // Show toast
            showToast('रसीद सफलतापूर्वक अपलोड हुई!', 'success');
            
            // Hide status after 8 seconds (increased to allow user to click receipt button)
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 8000);
            
        } else {
            // Error response
            statusDiv.style.display = 'block';
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>त्रुटि!</strong> ${data.message || 'अपलोड विफल रहा'}
            `;
            showToast(data.message || 'अपलोड विफल', 'error');
        }
        
    } catch (error) {
        console.error('Upload error:', error);
        statusDiv.style.display = 'block';
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>त्रुटि!</strong> अनुरोध विफल: ${error.message}
        `;
        showToast('अपलोड में त्रुटि: ' + error.message, 'error');
        
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>रसीद जमा करें';
    }
}
