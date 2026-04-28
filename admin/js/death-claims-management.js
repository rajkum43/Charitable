// Death Claims Management - JavaScript

let currentClaimId = null;
let claims = [];

// Load claims on page load
document.addEventListener('DOMContentLoaded', function() {
    loadClaims();
    
    // Refresh every 30 seconds
    setInterval(loadClaims, 30000);
});

// Load claims with filters
function loadClaims() {
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchInput').value;
    
    const url = `../api/get_death_claims.php?status=${status}&search=${encodeURIComponent(search)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayClaims(data.claims || []);
            updateStats(data.claims || []);
        })
        .catch(error => {
            document.getElementById('claimsContainer').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <h5>त्रुटि आई है</h5>
                    <p class="text-secondary">आवेदन लोड करने में विफल। कृपया पुनः प्रयास करें।</p>
                </div>
            `;
        });
}

// Update statistics
function updateStats(claimsList) {
    let pending = 0, review = 0, approved = 0, rejected = 0;
    
    claimsList.forEach(claim => {
        switch(claim.status) {
            case 'Pending': pending++; break;
            case 'Under Review': review++; break;
            case 'Approved': approved++; break;
            case 'Rejected': rejected++; break;
        }
    });
    
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('reviewCount').textContent = review;
    document.getElementById('approvedCount').textContent = approved;
    document.getElementById('rejectedCount').textContent = rejected;
}

// Display claims in list
function displayClaims(claimsList) {
    claims = claimsList;
    const container = document.getElementById('claimsContainer');
    
    if (claimsList.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <h5>कोई आवेदन नहीं मिला</h5>
                <p class="text-secondary">क्षमा करें, आपकी खोज के लिए कोई मृत्यु सहायता आवेदन नहीं मिला।</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="p-3">';
    
    claimsList.forEach(claim => {
        const status = claim.status || 'Pending';
        const statusClass = `status-${status.toLowerCase().replace(' ', '-')}`;
        const cardClass = `application-card ${status.toLowerCase().replace(' ', '-')}`;
        
        html += `
            <div class="${cardClass}" onclick="viewClaimDetails(${claim.id})">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-2 fw-bold">
                            <i class="fas fa-file-alt me-2"></i>क्लेम #${claim.claim_id}
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-secondary">मृत सदस्य:</small>
                                <div class="fw-600">${claim.full_name}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-secondary">नॉमिनी:</small>
                                <div class="fw-600">${claim.nominee_name}</div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-secondary">Member ID:</small>
                                <div>${claim.member_id}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-secondary">आवेदन तिथि:</small>
                                <div>${formatDate(claim.created_at)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="status-badge ${statusClass}">${translateStatus(status)}</span>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewClaimDetails(${claim.id})">
                                <i class="fas fa-eye me-1"></i>देखें
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// View claim details in modal
function viewClaimDetails(claimId) {
    const claim = claims.find(c => c.id === claimId);
    if (!claim) return;
    
    currentClaimId = claimId;
    
    let detailsHtml = `
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">क्लेम संख्या</div>
                <div class="info-value">${claim.claim_id}</div>
            </div>
            <div class="info-item">
                <div class="info-label">स्थिति</div>
                <div class="info-value"><span class="status-badge status-${(claim.status || 'Pending').toLowerCase().replace(' ', '-')}">${translateStatus(claim.status || 'Pending')}</span></div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-user me-2"></i>मृत सदस्य विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">नाम</div>
                <div class="info-value">${claim.full_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Member ID</div>
                <div class="info-value">${claim.member_id}</div>
            </div>
            <div class="info-item">
                <div class="info-label">पिता/पति का नाम</div>
                <div class="info-value">${claim.father_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">जन्मतिथि</div>
                <div class="info-value">${formatDate(claim.dob)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">पता</div>
                <div class="info-value">${claim.address}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-heart-broken me-2"></i>मृत्यु विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">मृत्यु तिथि</div>
                <div class="info-value">${formatDate(claim.death_date)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">आयु (मृत्यु के समय)</div>
                <div class="info-value">${claim.age_at_death} वर्ष</div>
            </div>
            <div class="info-item">
                <div class="info-label">स्थान</div>
                <div class="info-value">${claim.death_place}</div>
            </div>
            <div class="info-item">
                <div class="info-label">कारण</div>
                <div class="info-value">${claim.death_reason || 'N/A'}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-address-card me-2"></i>नॉमिनी विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">नाम</div>
                <div class="info-value">${claim.nominee_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">संबंध</div>
                <div class="info-value">${claim.nominee_relation}</div>
            </div>
            <div class="info-item">
                <div class="info-label">मोबाइल नंबर</div>
                <div class="info-value">${claim.nominee_mobile}</div>
            </div>
            <div class="info-item">
                <div class="info-label">जन्मतिथि</div>
                <div class="info-value">${formatDate(claim.nominee_dob) || 'N/A'}</div>
            </div>
            <div class="info-item">
                <div class="info-label">पता</div>
                <div class="info-value">${claim.nominee_address || 'N/A'}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-university me-2"></i>बैंक विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">बैंक का नाम</div>
                <div class="info-value">${claim.bank_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">IFSC कोड</div>
                <div class="info-value">${claim.ifsc_code}</div>
            </div>
            <div class="info-item">
                <div class="info-label">शाखा</div>
                <div class="info-value">${claim.branch_name || 'N/A'}</div>
            </div>
            <div class="info-item">
                <div class="info-label">खाता संख्या</div>
                <div class="info-value">XXXX-XXXX-${claim.account_number.slice(-4)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">खाते धारक का नाम</div>
                <div class="info-value">${claim.account_holder_name}</div>
            </div>
            ${claim.upi_id ? `<div class="info-item">
                <div class="info-label">UPI ID</div>
                <div class="info-value">${claim.upi_id}</div>
            </div>` : ''}
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-file me-2"></i>अपलोड किए गए दस्तावेज़</h6>
        <div class="row">
            <div class="col-md-6">
                <strong>दस्तावेज़:</strong>
                <ul class="list-unstyled mt-2">
                    ${claim.aadhaar_deceased ? `<li class="mb-2">
                        <a href="../uploads/death_claims/${claim.aadhaar_deceased}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>मृत व्यक्ति का आधार
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ मृत व्यक्ति का आधार</span></li>'}
                    ${claim.death_certificate ? `<li class="mb-2">
                        <a href="../uploads/death_claims/${claim.death_certificate}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>मृत्यु प्रमाण पत्र
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ मृत्यु प्रमाण पत्र</span></li>'}
                    ${claim.postmortem_report ? `<li class="mb-2">
                        <a href="../uploads/death_claims/${claim.postmortem_report}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>पोस्टमॉर्टम रिपोर्ट
                        </a>
                    </li>` : ''}
                </ul>
            </div>
            <div class="col-md-6">
                <strong>नॉमिनी दस्तावेज़:</strong>
                <ul class="list-unstyled mt-2">
                    ${claim.nominee_aadhaar ? `<li class="mb-2">
                        <a href="../uploads/death_claims/${claim.nominee_aadhaar}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>नॉमिनी का आधार
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ नॉमिनी का आधार</span></li>'}
                </ul>
            </div>
        </div>
    `;
    
    document.getElementById('claimDetails').innerHTML = detailsHtml;
    
    // Enable/Disable buttons based on status
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    
    if ((claim.status || '').toLowerCase() === 'approved' || (claim.status || '').toLowerCase() === 'rejected') {
        approveBtn.disabled = true;
        rejectBtn.disabled = true;
    } else {
        approveBtn.disabled = false;
        rejectBtn.disabled = false;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('claimModal'));
    modal.show();
}

// Approve Claim
function approveClaim() {
    if (!currentClaimId) return;
    
    if (confirm('क्या आप इस आवेदन को स्वीकृत करना चाहते हैं?')) {
        fetch('../api/admin_approval.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'death_claim',
                claim_id: currentClaimId,
                action: 'approve'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('आवेदन स्वीकृत कर दिया गया।');                document.getElementById('approveBtn').blur();                bootstrap.Modal.getInstance(document.getElementById('claimModal')).hide();
                loadClaims();
            } else {
                alert('त्रुटि: ' + (data.message || 'आवेदन स्वीकृत नहीं किया जा सका'));
            }
        })
        .catch(error => {
            alert('त्रुटि: ' + error.message);
        });
    }
}

// Reject Claim
function rejectClaim() {
    if (!currentClaimId) return;
    
    const reason = prompt('अस्वीकार करने का कारण बताएं:');
    if (reason === null) return;
    
    fetch('../api/admin_approval.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            type: 'death_claim',
            claim_id: currentClaimId,
            action: 'reject',
            remark: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('आवेदन अस्वीकार कर दिया गया।');
            document.getElementById('rejectBtn').blur();
            bootstrap.Modal.getInstance(document.getElementById('claimModal')).hide();
            loadClaims();
        } else {
            alert('त्रुटि: ' + (data.message || 'आवेदन अस्वीकार नहीं किया जा सका'));
        }
    })
    .catch(error => {
        alert('त्रुटि: ' + error.message);
    });
}

// Format date function
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('hi-IN', { year: 'numeric', month: 'long', day: 'numeric' });
}

// Translate status to Hindi
function translateStatus(status) {
    const statusMap = {
        'Pending': 'प्रतीक्षारत',
        'Under Review': 'समीक्षाधीन',
        'Approved': 'स्वीकृत',
        'Rejected': 'अस्वीकृत'
    };
    return statusMap[status] || status;
}
