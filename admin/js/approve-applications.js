// Approve Applications - JavaScript

let currentApplicationId = null;
let applications = [];

// Load applications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadApplications();
    
    // Refresh every 30 seconds
    setInterval(loadApplications, 30000);
});

// Load applications with filters
function loadApplications() {
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchInput').value;
    
    const url = `../api/get_applications.php?status=${status}&search=${encodeURIComponent(search)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            displayApplications(data.applications || []);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('applicationsContainer').innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <h5>त्रुटि आई है</h5>
                    <p class="text-secondary">आवेदन लोड करने में विफल। कृपया पुनः प्रयास करें।</p>
                </div>
            `;
        });
}

// Display applications in list
function displayApplications(apps) {
    applications = apps;
    const container = document.getElementById('applicationsContainer');
    
    if (apps.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                <h5>कोई आवेदन नहीं मिला</h5>
                <p class="text-secondary">क्षमा करें, आपकी खोज के लिए कोई आवेदन नहीं मिला।</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="p-3">';
    
    apps.forEach(app => {
        const statusClass = `status-${app.status.toLowerCase().replace(' ', '-')}`;
        const cardClass = `application-card ${app.status.toLowerCase().replace(' ', '-')}`;
        
        html += `
            <div class="${cardClass}" onclick="viewApplicationDetails(${app.id})">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-2 fw-bold">
                            <i class="fas fa-file-alt me-2"></i>आवेदन #${app.application_number}
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-secondary">सदस्य:</small>
                                <div class="fw-600">${app.member_name}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-secondary">बेटी:</small>
                                <div class="fw-600">${app.bride_name}</div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <small class="text-secondary">Member ID:</small>
                                <div>${app.member_id}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-secondary">आवेदन तिथि:</small>
                                <div>${formatDate(app.created_at)}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="status-badge ${statusClass}">${translateStatus(app.status)}</span>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); viewApplicationDetails(${app.id})">
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

// View application details in modal
function viewApplicationDetails(appId) {
    const app = applications.find(a => a.id === appId);
    if (!app) return;
    
    currentApplicationId = appId;
    
    let detailsHtml = `
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">आवेदन संख्या</div>
                <div class="info-value">${app.application_number}</div>
            </div>
            <div class="info-item">
                <div class="info-label">स्थिति</div>
                <div class="info-value"><span class="status-badge status-${app.status.toLowerCase().replace(' ', '-')}">${translateStatus(app.status)}</span></div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-user me-2"></i>सदस्य विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">नाम</div>
                <div class="info-value">${app.member_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Member ID</div>
                <div class="info-value">${app.member_id}</div>
            </div>
            <div class="info-item">
                <div class="info-label">पिता/पति का नाम</div>
                <div class="info-value">${app.member_father}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-venus me-2"></i>बेटी का विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">नाम</div>
                <div class="info-value">${app.bride_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">जन्म तिथि</div>
                <div class="info-value">${formatDate(app.bride_dob)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">आधार</div>
                <div class="info-value">${app.bride_aadhar ? 'XXX-XXXX-' + app.bride_aadhar.slice(-4) : 'नहीं दिया गया'}</div>
            </div>
            <div class="info-item">
                <div class="info-label">स्वास्थ्य स्थिति</div>
                <div class="info-value">${app.bride_health}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-home me-2"></i>पारिवारिक विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">वार्षिक आय</div>
                <div class="info-value">₹ ${app.family_income.toLocaleString('en-IN')}</div>
            </div>
            <div class="info-item">
                <div class="info-label">परिवार के सदस्य</div>
                <div class="info-value">${app.family_members}</div>
            </div>
            <div class="info-item">
                <div class="info-label">जिला</div>
                <div class="info-value">${app.district}</div>
            </div>
            <div class="info-item">
                <div class="info-label">ब्लॉक</div>
                <div class="info-value">${app.block}</div>
            </div>
            <div class="info-item">
                <div class="info-label">शहर</div>
                <div class="info-value">${app.city}</div>
            </div>
            <div class="info-item">
                <div class="info-label">राज्य</div>
                <div class="info-value">${app.state}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-mars me-2"></i>वर का विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">नाम</div>
                <div class="info-value">${app.groom_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">जन्म तिथि</div>
                <div class="info-value">${formatDate(app.groom_dob)}</div>
            </div>
            <div class="info-item">
                <div class="info-label">उम्र</div>
                <div class="info-value">${app.groom_age} वर्ष</div>
            </div>
            <div class="info-item">
                <div class="info-label">व्यवसाय</div>
                <div class="info-value">${app.groom_occupation}</div>
            </div>
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-university me-2"></i>बैंक विवरण</h6>
        <div class="application-info">
            <div class="info-item">
                <div class="info-label">IFSC कोड</div>
                <div class="info-value">${app.ifsc_code}</div>
            </div>
            <div class="info-item">
                <div class="info-label">बैंक का नाम</div>
                <div class="info-value">${app.bank_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">शाखा</div>
                <div class="info-value">${app.branch_name}</div>
            </div>
            <div class="info-item">
                <div class="info-label">खाता संख्या</div>
                <div class="info-value">XXXX-XXXX-${app.account_number.slice(-4)}</div>
            </div>
            ${app.upi_id ? `<div class="info-item">
                <div class="info-label">UPI ID</div>
                <div class="info-value">${app.upi_id}</div>
            </div>` : ''}
        </div>
        
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-file me-2"></i>दस्तावेज़</h6>
        <div class="row">
            <div class="col-md-6">
                <strong>आवश्यक दस्तावेज़:</strong>
                <ul class="list-unstyled mt-2">
                    ${app.aadhar_proof ? `<li class="mb-2">
                        <a href="../uploads/beti_vivah/${app.aadhar_proof}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>आधार कार्ड
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ आधार कार्ड</span></li>'}
                    ${app.address_proof ? `<li class="mb-2">
                        <a href="../uploads/beti_vivah/${app.address_proof}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>पता प्रमाण
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ पता प्रमाण</span></li>'}
                    ${app.income_proof ? `<li class="mb-2">
                        <a href="../uploads/beti_vivah/${app.income_proof}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>आय प्रमाण
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ आय प्रमाण</span></li>'}
                    ${app.marriage_certificate ? `<li class="mb-2">
                        <a href="../uploads/beti_vivah/${app.marriage_certificate}" target="_blank" class="text-primary">
                            <i class="fas fa-file me-1"></i>विवाह कार्ड
                        </a>
                    </li>` : '<li class="mb-2"><span class="text-danger">✗ विवाह कार्ड</span></li>'}
                </ul>
            </div>
        </div>
        
        <div class="decision-section">
            <h6 class="fw-bold">निर्णय लें</h6>
            <div class="remarks-area">
                <label class="form-label">टिप्पणी (वैकल्पिक)</label>
                <textarea id="remarksText" class="form-control" rows="3" placeholder="स्वीकृति या अस्वीकृति का कारण बताएं..."></textarea>
            </div>
        </div>
    `;
    
    document.getElementById('applicationDetails').innerHTML = detailsHtml;
    document.getElementById('modalTitle').textContent = `आवेदन #${app.application_number}`;
    
    // Show/hide approve/reject buttons based on status
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    
    if (app.status === 'Approved' || app.status === 'Rejected') {
        approveBtn.style.display = 'none';
        rejectBtn.style.display = 'none';
    } else {
        approveBtn.style.display = 'inline-block';
        rejectBtn.style.display = 'inline-block';
    }
    
    new bootstrap.Modal(document.getElementById('applicationModal')).show();
}

// Approve application
function approveApplication() {
    if (!currentApplicationId) return;
    
    const remarks = document.getElementById('remarksText').value;
    
    if (!confirm('क्या आप इस आवेदन को स्वीकृत करना चाहते हैं?')) {
        return;
    }
    
    fetch('../api/approve_application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            application_id: currentApplicationId,
            action: 'approve',
            remarks: remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('आवेदन स्वीकृत हो गया।');
            bootstrap.Modal.getInstance(document.getElementById('applicationModal')).hide();
            loadApplications();
        } else {
            alert('त्रुटि: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('कुछ गलत हुआ। कृपया पुनः प्रयास करें।');
    });
}

// Reject application
function rejectApplication() {
    if (!currentApplicationId) return;
    
    const remarks = document.getElementById('remarksText').value;
    
    if (!remarks.trim()) {
        alert('कृपया अस्वीकृति का कारण दर्ज करें।');
        return;
    }
    
    if (!confirm('क्या आप इस आवेदन को अस्वीकृत करना चाहते हैं?')) {
        return;
    }
    
    fetch('../api/approve_application.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            application_id: currentApplicationId,
            action: 'reject',
            remarks: remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('आवेदन अस्वीकृत हो गया।');
            bootstrap.Modal.getInstance(document.getElementById('applicationModal')).hide();
            loadApplications();
        } else {
            alert('त्रुटि: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('कुछ गलत हुआ। कृपया पुनः प्रयास करें।');
    });
}

// Helper functions
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('hi-IN', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
    });
}

function translateStatus(status) {
    const statusMap = {
        'Pending': 'प्रतीक्षारत',
        'Under Review': 'समीक्षाधीन',
        'Approved': 'स्वीकृत',
        'Rejected': 'अस्वीकृत'
    };
    return statusMap[status] || status;
}
