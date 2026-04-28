// Admin Approval System JavaScript

let currentPage = 1;
let currentStatus = 0; // 0 = pending, 1 = approved, 2 = rejected
let currentSearch = '';

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Approval Dashboard loaded');
    
    // Load initial pending members
    loadMembers();
    
    // Load stats
    updateStats();
    
    // Set up event listeners
    setupEventListeners();
    
    // Add logout button listener
    document.querySelector('.btn-logout')?.addEventListener('click', function(e) {
        e.preventDefault();
        logoutAdmin();
    });
});

// Set up event listeners
function setupEventListeners() {
    // Filter buttons
    document.getElementById('filterBtn')?.addEventListener('click', () => {
        currentPage = 1;
        loadMembers();
    });

    document.getElementById('resetBtn')?.addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '0';
        currentPage = 1;
        currentSearch = '';
        currentStatus = 0;
        loadMembers();
    });

    // Status filter
    document.getElementById('statusFilter')?.addEventListener('change', (e) => {
        currentStatus = parseInt(e.target.value);
        currentPage = 1;
        loadMembers();
    });

    // Modal close button
    document.getElementById('modalClose')?.addEventListener('click', closeModal);

    // Modal background click
    document.getElementById('detailsModal')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            closeModal();
        }
    });
}

// Load members from API
function loadMembers() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        currentSearch = searchInput.value.trim();
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        currentStatus = parseInt(statusFilter.value);
    }

    // Build query parameters
    let url = `api/get_pending_members.php?status=${currentStatus}&page=${currentPage}`;
    if (currentSearch) {
        url += `&search=${encodeURIComponent(currentSearch)}`;
    }

    showLoading();

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateMembersTable(data.data);
                updateStats();
            } else {
                showAlert(data.message || 'Data loading failed', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading members: ' + error.message, 'danger');
            // Show empty state on error
            const tableBody = document.getElementById('membersTableBody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon">⚠️</div>
                                <p class="empty-text">डेटा लोड करने में त्रुटि हुई</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        })
        .finally(() => {
            hideLoading();
        });
}

// Populate members table
function populateMembersTable(data) {
    const tableBody = document.getElementById('membersTableBody');
    const paginationContainer = document.getElementById('pagination');
    
    if (!tableBody) return;

    // Clear existing rows
    tableBody.innerHTML = '';

    if (data.members.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p class="empty-text">कोई सदस्य नहीं मिला</p>
                    </div>
                </td>
            </tr>
        `;
        paginationContainer.innerHTML = '';
        return;
    }

    // Populate members
    data.members.forEach(member => {
        const statusBadge = getStatusBadge(member.status);
        const paymentBadge = getPaymentBadge(member.payment_verified);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${member.member_id}</td>
            <td>${member.full_name}</td>
            <td>${member.mobile_number}</td>
            <td>${member.email || '-'}</td>
            <td>${member.aadhar_masked}</td>
            <td>${paymentBadge}</td>
            <td>${member.created_at}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-view" onclick="viewMember('${member.member_id}')">
                        <i class="fas fa-eye"></i> विवरण
                    </button>
                    ${member.status === 0 ? `
                        <button class="btn-action btn-approve" onclick="approveMember('${member.member_id}')">
                            <i class="fas fa-check"></i> अनुमोदित
                        </button>
                        <button class="btn-action btn-reject" onclick="rejectMember('${member.member_id}')">
                            <i class="fas fa-times"></i> अस्वीकृत
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Populate pagination
    populatePagination(data);
}

// Get status badge HTML
function getStatusBadge(status) {
    const statusMap = {
        0: { text: 'लंबित', class: 'badge-pending' },
        1: { text: 'अनुमोदित', class: 'badge-approved' },
        2: { text: 'अस्वीकृत', class: 'badge-rejected' }
    };
    const item = statusMap[status] || statusMap[0];
    return `<span class="badge ${item.class}">${item.text}</span>`;
}

// Get payment badge HTML
function getPaymentBadge(verified) {
    if (verified == 1) {
        return '<span class="badge badge-verified">सत्यापित</span>';
    }
    return '<span class="badge badge-pending-payment">लंबित</span>';
}

// Populate pagination
function populatePagination(data) {
    const paginationContainer = document.getElementById('pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.disabled = data.page === 1;
    prevBtn.addEventListener('click', () => {
        if (data.page > 1) {
            currentPage = data.page - 1;
            loadMembers();
        }
    });
    paginationContainer.appendChild(prevBtn);

    // Page numbers
    for (let i = 1; i <= data.total_pages; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        if (i === data.page) {
            pageBtn.classList.add('active');
        }
        pageBtn.addEventListener('click', () => {
            currentPage = i;
            loadMembers();
        });
        paginationContainer.appendChild(pageBtn);
    }

    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = data.page === data.total_pages;
    nextBtn.addEventListener('click', () => {
        if (data.page < data.total_pages) {
            currentPage = data.page + 1;
            loadMembers();
        }
    });
    paginationContainer.appendChild(nextBtn);
}

// View member details
function viewMember(memberId) {
    fetch(`api/get_member_details.php?member_id=${memberId}`)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayMemberDetails(data.data);
                openModal();
            } else {
                showAlert(data.message || 'Failed to load member details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading member details: ' + error.message, 'danger');
        });
}

// Display member details in modal
function displayMemberDetails(member) {
    const modalBody = document.getElementById('modalBody');
    if (!modalBody) return;

    const statusText = ['लंबित', 'अनुमोदित', 'अस्वीकृत'][member.status] || 'अज्ञात';
    const membershipStatus = member.status === 1 ? 'badge-approved' : member.status === 2 ? 'badge-rejected' : 'badge-pending';

    modalBody.innerHTML = `
        <div class="detail-group">
            <div class="detail-label">सदस्य ID</div>
            <div class="detail-value">${member.member_id}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">नाम</div>
            <div class="detail-value">${member.full_name}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">आधार (मुखौटा)</div>
            <div class="detail-value">${member.aadhar_masked}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">मोबाइल</div>
            <div class="detail-value">${member.mobile_number}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">ईमेल</div>
            <div class="detail-value">${member.email || '-'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">जन्म तारीख</div>
            <div class="detail-value">${member.date_of_birth}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">लिंग</div>
            <div class="detail-value">${member.gender || '-'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">व्यवसाय</div>
            <div class="detail-value">${member.occupation || '-'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">राज्य</div>
            <div class="detail-value">${member.state}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">जिला</div>
            <div class="detail-value">${member.district}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">ब्लॉक</div>
            <div class="detail-value">${member.block}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">थायी पता</div>
            <div class="detail-value">${member.permanent_address || '-'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">UTR नंबर</div>
            <div class="detail-value">${member.utr_number}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">भुगतान</div>
            <div class="detail-value">${member.payment_verified == 1 ? '<span class="badge badge-verified">सत्यापित</span>' : '<span class="badge badge-pending-payment">लंबित</span>'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">स्थिति</div>
            <div class="detail-value"><span class="badge ${membershipStatus}">${statusText}</span></div>
        </div>
        <div class="detail-group">
            <div class="detail-label">रजिस्ट्रेशन तारीख</div>
            <div class="detail-value">${member.created_at}</div>
        </div>
        ${member.receipt_file ? `
        <div class="detail-group">
            <div class="detail-label">भुगतान रसीद</div>
            <div class="detail-value">
                <div class="receipt-section">
                    <div class="receipt-preview" id="receiptPreview" style="cursor: pointer;" onclick="viewReceipt('${member.member_id}', '${member.receipt_file}')">
                        <img src="../../uploads/payment_receipts/${member.receipt_file}" alt="भुगतान रसीद" class="receipt-image" style="cursor: pointer;">
                    </div>
                    <div class="receipt-actions">
                        <button class="btn btn-sm btn-info" onclick="viewReceipt('${member.member_id}', '${member.receipt_file}')" title="बड़ा करके देखें">
                            <i class="fas fa-expand"></i> बड़ा करके देखें
                        </button>
                        <a href="api/download_receipt.php?member_id=${member.member_id}" class="btn btn-sm btn-primary" title="डाउनलोड करें">
                            <i class="fas fa-download"></i> डाउनलोड करें
                        </a>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;

    // Update modal footer with action buttons
    const modalFooter = document.getElementById('modalFooter');
    if (modalFooter) {
        if (member.status === 0) {
            modalFooter.innerHTML = `
                <button class="btn btn-secondary" onclick="closeModal()">बंद करें</button>
                <button class="btn btn-success" onclick="approveMember('${member.member_id}')">
                    <i class="fas fa-check me-2"></i>अनुमोदित करें
                </button>
                <button class="btn btn-primary" onclick="rejectMember('${member.member_id}')">
                    <i class="fas fa-times me-2"></i>अस्वीकृत करें
                </button>
            `;
        } else {
            modalFooter.innerHTML = `
                <button class="btn btn-secondary" onclick="closeModal()">बंद करें</button>
            `;
        }
    }
}

// Approve member
function approveMember(memberId) {
    if (!confirm('क्या आप इस सदस्य को अनुमोदित करना चाहते हैं?')) {
        return;
    }

    const formData = new FormData();
    formData.append('member_id', memberId);
    formData.append('action', 'approve');

    fetch('api/approve_member.php', {
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
            closeModal();
            // Refresh both members list and stats
            setTimeout(() => {
                loadMembers();
                updateStats();
                // Update notification badge in navbar
                if (typeof updateNotificationCountGlobal === 'function') {
                    updateNotificationCountGlobal();
                }
            }, 1000);
        } else {
            showAlert(data.message || 'Approval failed', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error: ' + error.message, 'danger');
    });
}

// Reject member
function rejectMember(memberId) {
    if (!confirm('क्या आप इस सदस्य को अस्वीकृत करना चाहते हैं?')) {
        return;
    }

    const formData = new FormData();
    formData.append('member_id', memberId);
    formData.append('action', 'reject');

    fetch('api/approve_member.php', {
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
            closeModal();
            // Refresh both members list and stats
            setTimeout(() => {
                loadMembers();
                updateStats();
                // Update notification badge in navbar
                if (typeof updateNotificationCountGlobal === 'function') {
                    updateNotificationCountGlobal();
                }
            }, 1000);
        } else {
            showAlert(data.message || 'Rejection failed', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error: ' + error.message, 'danger');
    });
}

// Modal functions
function openModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Show loading
function showLoading() {
    const tableBody = document.getElementById('membersTableBody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="loading">
                    <div class="spinner"></div> लोड हो रहा है...
                </td>
            </tr>
        `;
    }
}

// Hide loading
function hideLoading() {
    // Loading will be replaced with actual data or empty state
}

// Logout admin
function logoutAdmin() {
    if (!confirm('क्या आप लॉगआउट करना चाहते हैं?')) {
        return;
    }

    fetch('api/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = '../index.php';
        });
}

// Update stats
function updateStats() {
    fetch('api/get_stats.php')
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const pending = data.data.pending || 0;
                const approved = data.data.approved || 0;
                const rejected = data.data.rejected || 0;
                const total = data.data.total || (pending + approved + rejected);
                
                document.getElementById('pendingCount').textContent = pending;
                document.getElementById('approvedCount').textContent = approved;
                document.getElementById('rejectedCount').textContent = rejected;
                document.getElementById('totalCount').textContent = total;
            } else {
                console.warn('Stats fetch warning:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            // Don't show alert for stats, just log it
            // Set default values
            document.getElementById('pendingCount').textContent = '0';
            document.getElementById('approvedCount').textContent = '0';
            document.getElementById('rejectedCount').textContent = '0';
            document.getElementById('totalCount').textContent = '0';
        });
}

// Show alert
function showAlert(message, type = 'info') {
    let alertContainer = document.getElementById('alertContainer');
    
    // Create alert container if it doesn't exist
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '80px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '999999';
        alertContainer.style.maxWidth = '400px';
        document.body.appendChild(alertContainer);
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} show`;
    alert.style.marginBottom = '10px';
    alert.innerHTML = message;

    alertContainer.appendChild(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// View payment receipt in full screen
function viewReceipt(memberId, fileName) {
    if (!fileName) {
        showAlert('भुगतान रसीद उपलब्ध नहीं है', 'info');
        return;
    }

    const modal = document.createElement('div');
    modal.className = 'modal show';
    modal.id = 'receiptModal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 90%; max-height: 90%;">
            <div class="modal-header">
                <h2>भुगतान रसीद</h2>
                <button type="button" class="modal-close" onclick="closeReceiptModal()">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: 70vh;">
                <img src="../../uploads/payment_receipts/${fileName}" alt="भुगतान रसीद" style="max-width: 100%; height: auto;">
            </div>
            <div class="modal-footer">
                <a href="api/download_receipt.php?member_id=${memberId}" class="btn btn-primary" download>
                    <i class="fas fa-download me-2"></i>डाउनलोड करें
                </a>
                <button class="btn btn-secondary" onclick="closeReceiptModal()">बंद करें</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeReceiptModal();
        }
    });
}

// Close receipt modal
function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    if (modal) {
        modal.remove();
    }
}
