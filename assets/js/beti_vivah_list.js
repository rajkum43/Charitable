// Beti Vivah Applications List JavaScript

let currentPage = 1;
let currentDistrict = '';
let currentBlock = '';
let currentSearch = '';
let currentStatus = '';
let allDistricts = [];
let allBlocks = [];

document.addEventListener('DOMContentLoaded', function() {
    console.log('Beti Vivah Applications page loaded');
    
    // Load initial applications and filters
    loadApplications();
    loadFilterOptions();
    
    // Set up event listeners
    setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
    // Search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadApplications();
            }
        });
    }

    // District filter
    const districtFilter = document.getElementById('districtFilter');
    if (districtFilter) {
        districtFilter.addEventListener('change', (e) => {
            currentDistrict = e.target.value;
            currentBlock = '';
            const blockFilter = document.getElementById('blockFilter');
            if (blockFilter) blockFilter.value = '';
            currentPage = 1;
            loadApplications();
            loadFilterOptions();
        });
    }

    // Block filter
    const blockFilter = document.getElementById('blockFilter');
    if (blockFilter) {
        blockFilter.addEventListener('change', (e) => {
            currentBlock = e.target.value;
            currentPage = 1;
            loadApplications();
        });
    }

    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', (e) => {
            currentStatus = e.target.value;
            currentPage = 1;
            loadApplications();
        });
    }

    // Filter button
    const filterBtn = document.getElementById('filterBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            currentPage = 1;
            loadApplications();
        });
    }

    // Reset button
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', resetFilters);
    }

    // Modal close button
    const modalClose = document.getElementById('modalClose');
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    // Close modal on background click
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
}

// Load filter options
function loadFilterOptions() {
    const apiUrl = '../api/get_beti_vivah_applications.php';
    
    fetch(apiUrl)
        .then(response => {
            console.log('Filter options response status:', response.status);
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Filter options data:', data);
            if (data.success) {
                allDistricts = data.data.districts || [];
                populateDistrictFilter();
            } else {
                console.error('API error:', data.message);
                showAlert('फिल्टर लोड करने में त्रुटि: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error loading filter options:', error);
            showAlert('फिल्टर लोड करने में विफल: ' + error.message, 'danger');
        });
}

// Populate district filter dropdown
function populateDistrictFilter() {
    const districtFilter = document.getElementById('districtFilter');
    if (!districtFilter) return;

    const currentValue = districtFilter.value;
    districtFilter.innerHTML = '<option value="">सभी जिले</option>';

    allDistricts.forEach(district => {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        districtFilter.appendChild(option);
    });

    districtFilter.value = currentValue;
}

// Populate block filter dropdown
function populateBlockFilter() {
    const blockFilter = document.getElementById('blockFilter');
    if (!blockFilter || !currentDistrict) {
        if (blockFilter) blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
        return;
    }

    // Fetch blocks for selected district
    const url = `../api/get_beti_vivah_applications.php?district=${encodeURIComponent(currentDistrict)}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const blocks = data.data.blocks || [];
                blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
                blocks.forEach(block => {
                    const option = document.createElement('option');
                    option.value = block;
                    option.textContent = block;
                    blockFilter.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading blocks:', error);
        });
}

// Load applications from API
function loadApplications() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        currentSearch = searchInput.value.trim();
    }

    // Build query parameters
    let url = `../api/get_beti_vivah_applications.php?page=${currentPage}`;
    
    if (currentSearch) {
        url += `&search=${encodeURIComponent(currentSearch)}`;
    }
    
    if (currentDistrict) {
        url += `&district=${encodeURIComponent(currentDistrict)}`;
    }
    
    if (currentBlock) {
        url += `&block=${encodeURIComponent(currentBlock)}`;
    }

    if (currentStatus) {
        url += `&status=${encodeURIComponent(currentStatus)}`;
    }

    console.log('Loading applications from:', url);
    showLoading();

    fetch(url)
        .then(response => {
            console.log('API response status:', response.status);
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            console.log('Applications data:', data);
            if (data.success) {
                populateApplicationsTable(data.data);
                updateStats(data.data.stats);
                updatePagination(data.data);
                
                // Update block filter if district changed
                if (currentDistrict) {
                    populateBlockFilter();
                }
            } else {
                showAlert(data.message || 'डेटा लोड करने में विफल', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('आवेदन डेटा लोड करने में त्रुटि: ' + error.message, 'danger');
            showEmptyState();
        })
        .finally(() => {
            hideLoading();
        });
}

// Populate applications table
function populateApplicationsTable(data) {
    const tableBody = document.getElementById('applicationsTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (data.applications.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11">
                    <div class="empty-state" style="padding: 40px; text-align: center;">
                        <div class="empty-icon" style="font-size: 48px; margin-bottom: 15px;">📭</div>
                        <p class="empty-title" style="font-size: 18px; font-weight: 600; margin-bottom: 5px;">कोई आवेदन नहीं मिला</p>
                        <p class="empty-text" style="color: #666;">कृपया अपनी खोज मानदंड बदलें और पुनः प्रयास करें।</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    // Calculate starting SN
    const limit = data.limit || 20;
    const totalRecords = data.total || 0;
    const startingSN = totalRecords - ((data.page - 1) * limit);

    data.applications.forEach((app, index) => {
        const sn = startingSN - index;
        const row = createApplicationTableRow(app, sn);
        tableBody.appendChild(row);
    });
}

// Create application table row element
function createApplicationTableRow(app, sn) {
    const row = document.createElement('tr');
    
    // Store application data in row attribute
    const appDataWithSN = { ...app, sn: sn };
    row.dataset.appData = JSON.stringify(appDataWithSN);

    // Format status
    const statusText = getStatusText(app.status);
    const statusClass = getStatusClass(app.status);

    // Format submission date
    const submissionDate = app.created_at ? new Date(app.created_at).toLocaleDateString('hi-IN') : 'N/A';
    
    // Format wedding date
    const weddingDate = app.wedding_date ? new Date(app.wedding_date).toLocaleDateString('hi-IN') : 'N/A';

    // Format marriage certificate image
    const certificateImage = app.marriage_certificate ? `
        <div class="certificate-preview-box">
            <img src="../${escapeHtml(app.marriage_certificate)}" alt="विवाह कार्ड" class="certificate-img" />
        </div>
    ` : `<span class="text-muted">-</span>`;

    row.innerHTML = `
        <td><strong>${escapeHtml(sn || app.id || 'N/A')}</strong></td>
        <td><span class="app-link">${escapeHtml(app.application_number || 'N/A')}</span></td>
        <td>${escapeHtml(app.member_name || 'N/A')}</td>
        <td>${escapeHtml(app.bride_name || 'N/A')}</td>
        <td>${weddingDate}</td>
        <td><small>${escapeHtml((app.address || 'N/A').substring(0, 30) + '...')}</small></td>
        <td>${escapeHtml(app.district || '-')}</td>
        <td>${escapeHtml(app.block || '-')}</td>
        <td>${certificateImage}</td>
        <td>${submissionDate}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td>
            <button class="action-button btn btn-sm btn-primary">विवरण देखें</button>
        </td>
    `;

    // Add click listeners
    const appLink = row.querySelector('.app-link');
    const actionBtn = row.querySelector('.action-button');
    const certImg = row.querySelector('.certificate-img');
    
    const clickHandler = () => {
        const appData = JSON.parse(row.dataset.appData);
        openApplicationDetails(appData);
    };
    
    if (appLink) appLink.addEventListener('click', clickHandler);
    if (actionBtn) actionBtn.addEventListener('click', clickHandler);
    
    // Add click handler for certificate image
    if (certImg) {
        certImg.addEventListener('click', (e) => {
            e.stopPropagation();
            showImagePreview(certImg.src, 'विवाह कार्ड');
        });
    }

    return row;
}

// Get status text
function getStatusText(status) {
    const statusMap = {
        'Pending': 'लंबित',
        'Under Review': 'समीक्षा में',
        'Approved': 'स्वीकृत',
        'Rejected': 'अस्वीकृत'
    };
    return statusMap[status] || status || 'N/A';
}

// Get status badge class
function getStatusClass(status) {
    const classMap = {
        'Pending': 'status-pending',
        'Under Review': 'status-under-review',
        'Approved': 'status-approved',
        'Rejected': 'status-rejected'
    };
    return classMap[status] || 'status-pending';
}

// Open application details modal
function openApplicationDetails(app) {
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalBody) return;

    const weddingDate = app.wedding_date ? new Date(app.wedding_date).toLocaleDateString('hi-IN') : 'N/A';
    const submissionDate = app.created_at ? new Date(app.created_at).toLocaleDateString('hi-IN') : 'N/A';
    const statusText = getStatusText(app.status);

    const detailsHTML = `
        <div class="application-details">
            <div class="details-section">
                <h4>आवेदन जानकारी</h4>
                <div class="detail-row">
                    <span class="detail-label">आवेदन संख्या:</span>
                    <span class="detail-value">${escapeHtml(app.application_number || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">स्थिति:</span>
                    <span class="detail-value"><span class="status-badge ${getStatusClass(app.status)}">${statusText}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">आवेदन तिथि:</span>
                    <span class="detail-value">${submissionDate}</span>
                </div>
            </div>

            <div class="details-section">
                <h4>सदस्य जानकारी</h4>
                <div class="detail-row">
                    <span class="detail-label">सदस्य आईडी:</span>
                    <span class="detail-value">${escapeHtml(app.member_id || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">सदस्य का नाम:</span>
                    <span class="detail-value">${escapeHtml(app.member_name || 'N/A')}</span>
                </div>
            </div>

            <div class="details-section">
                <h4>बेटी की जानकारी</h4>
                <div class="detail-row">
                    <span class="detail-label">बेटी का नाम:</span>
                    <span class="detail-value">${escapeHtml(app.bride_name || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">विवाह तिथि:</span>
                    <span class="detail-value">${weddingDate}</span>
                </div>
            </div>

            <div class="details-section">
                <h4>पता की जानकारी</h4>
                <div class="detail-row">
                    <span class="detail-label">स्थाई पता:</span>
                    <span class="detail-value">${escapeHtml(app.address || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">जिला:</span>
                    <span class="detail-value">${escapeHtml(app.district || 'N/A')}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ब्लॉक:</span>
                    <span class="detail-value">${escapeHtml(app.block || 'N/A')}</span>
                </div>
            </div>

            ${app.remarks ? `
            <div class="details-section">
                <h4>टिप्पणी</h4>
                <div class="detail-row">
                    <span class="detail-value">${escapeHtml(app.remarks)}</span>
                </div>
            </div>
            ` : ''}
        </div>

        <style>
            .application-details {
                font-size: 14px;
            }
            .details-section {
                margin-bottom: 25px;
                border-bottom: 1px solid #e0e0e0;
                padding-bottom: 15px;
            }
            .details-section:last-child {
                border-bottom: none;
            }
            .details-section h4 {
                color: #2c3e50;
                margin-bottom: 10px;
                font-weight: 600;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding: 5px 0;
            }
            .detail-label {
                font-weight: 600;
                color: #555;
                min-width: 150px;
            }
            .detail-value {
                color: #333;
                text-align: right;
                flex: 1;
            }
        </style>
    `;

    modalBody.innerHTML = detailsHTML;
    modal.style.display = 'block';
}

// Close modal
function closeModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Update statistics
function updateStats(stats) {
    const totalEl = document.getElementById('totalApplications');
    const pendingEl = document.getElementById('pendingApplications');
    const approvedEl = document.getElementById('approvedApplications');
    const rejectedEl = document.getElementById('rejectedApplications');

    if (totalEl) totalEl.textContent = stats.total || 0;
    if (pendingEl) pendingEl.textContent = stats.pending || 0;
    if (approvedEl) approvedEl.textContent = stats.approved || 0;
    if (rejectedEl) rejectedEl.textContent = stats.rejected || 0;
}

// Update pagination
function updatePagination(data) {
    const paginationContainer = document.getElementById('pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    const totalPages = data.totalPages || 1;
    const currentPageNum = data.page || 1;

    if (totalPages <= 1) return;

    // Previous button
    if (currentPageNum > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '← पिछला';
        prevBtn.className = 'pagination-button';
        prevBtn.addEventListener('click', () => {
            currentPage = currentPageNum - 1;
            loadApplications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginationContainer.appendChild(prevBtn);
    }

    // Page numbers
    const startPage = Math.max(1, currentPageNum - 2);
    const endPage = Math.min(totalPages, currentPageNum + 2);

    if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.className = 'pagination-button';
        firstBtn.addEventListener('click', () => {
            currentPage = 1;
            loadApplications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginationContainer.appendChild(firstBtn);

        if (startPage > 2) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationContainer.appendChild(dots);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = 'pagination-button' + (i === currentPageNum ? ' active' : '');
        btn.addEventListener('click', () => {
            currentPage = i;
            loadApplications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginationContainer.appendChild(btn);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.className = 'pagination-dots';
            paginationContainer.appendChild(dots);
        }

        const lastBtn = document.createElement('button');
        lastBtn.textContent = totalPages;
        lastBtn.className = 'pagination-button';
        lastBtn.addEventListener('click', () => {
            currentPage = totalPages;
            loadApplications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginationContainer.appendChild(lastBtn);
    }

    // Next button
    if (currentPageNum < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'अगला →';
        nextBtn.className = 'pagination-button';
        nextBtn.addEventListener('click', () => {
            currentPage = currentPageNum + 1;
            loadApplications();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        paginationContainer.appendChild(nextBtn);
    }
}

// Reset all filters
function resetFilters() {
    currentPage = 1;
    currentDistrict = '';
    currentBlock = '';
    currentSearch = '';
    currentStatus = '';

    const searchInput = document.getElementById('searchInput');
    const districtFilter = document.getElementById('districtFilter');
    const blockFilter = document.getElementById('blockFilter');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) searchInput.value = '';
    if (districtFilter) districtFilter.value = '';
    if (blockFilter) blockFilter.value = '';
    if (statusFilter) statusFilter.value = '';

    loadApplications();
}

// Show loading state
function showLoading() {
    const tableBody = document.getElementById('applicationsTableBody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Hide loading state
function hideLoading() {
    // Loading is removed when table is updated
}

// Show empty state
function showEmptyState() {
    const tableBody = document.getElementById('applicationsTableBody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" style="padding: 40px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 15px;">⚠️</div>
                    <p style="font-size: 18px; font-weight: 600; margin-bottom: 5px;">डेटा लोड करने में त्रुटि</p>
                    <p style="color: #666;">कृपया पेज को रीफ्रेश करें और पुनः प्रयास करें।</p>
                </td>
            </tr>
        `;
    }
}

// Show alert
function showAlert(message, type = 'info') {
    console.log(`[${type}] ${message}`);
    // You can customize this to show actual alerts
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// Add pagination button styles
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .pagination {
            margin-top: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .pagination-button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: #fff;
            color: #333;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .pagination-button:hover {
            background-color: #f0f0f0;
            border-color: #999;
        }
        .pagination-button.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination-dots {
            padding: 8px 5px;
            color: #999;
        }
    </style>
`);

// Show image preview modal
function showImagePreview(imageSrc, title = 'Image') {
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalBody) return;

    const previewHTML = `
        <div class="image-preview-modal" style="text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 20px;">${escapeHtml(title)}</h3>
            <img src="${imageSrc}" alt="${escapeHtml(title)}" style="max-width: 100%; max-height: 500px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);" />
        </div>
    `;

    // Update modal header
    const modalHeader = modal.querySelector('.modal-header h2');
    if (modalHeader) {
        modalHeader.textContent = title;
    }

    modalBody.innerHTML = previewHTML;
    modal.style.display = 'block';
}
