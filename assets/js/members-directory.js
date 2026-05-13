// Members Directory JavaScript

let currentPage = 1;
let currentDistrict = '';
let currentBlock = '';
let currentSearch = '';
let allDistricts = [];
let allBlocks = [];

document.addEventListener('DOMContentLoaded', function() {
    // Load initial members and filters
    loadMembers();
    loadFilterOptions();
    
    // Set up event listeners
    setupEventListeners();
});

// Setup Event Listeners
function setupEventListeners() {
    // Search input - search on input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadMembers();
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
            loadMembers();
            loadFilterOptions();
        });
    }

    // Block filter
    const blockFilter = document.getElementById('blockFilter');
    if (blockFilter) {
        blockFilter.addEventListener('change', (e) => {
            currentBlock = e.target.value;
            currentPage = 1;
            loadMembers();
        });
    }

    // Filter button
    const filterBtn = document.getElementById('filterBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            currentPage = 1;
            loadMembers();
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

// Load filter options (districts and blocks)
function loadFilterOptions() {
    const apiUrl = '../api/get_approved_members.php';
    
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                allDistricts = data.data.districts || [];
                populateDistrictFilter();
            } else {
                showAlert('फिल्टर लोड करने में त्रुटि: ' + data.message, 'danger');
            }
        })
        .catch(error => {
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
    const url = `../api/get_approved_members.php?district=${encodeURIComponent(currentDistrict)}`;
    
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
        });
}

// Load members from API
function loadMembers() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        currentSearch = searchInput.value.trim();
    }

    // Build query parameters
    let url = `../api/get_approved_members.php?page=${currentPage}`;
    
    if (currentSearch) {
        url += `&search=${encodeURIComponent(currentSearch)}`;
    }
    
    if (currentDistrict) {
        url += `&district=${encodeURIComponent(currentDistrict)}`;
    }
    
    if (currentBlock) {
        url += `&block=${encodeURIComponent(currentBlock)}`;
    }

    showLoading();

    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateMembersGrid(data.data);
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
            showAlert('सदस्य डेटा लोड करने में त्रुटि: ' + error.message, 'danger');
            showEmptyState();
        })
        .finally(() => {
            hideLoading();
            // Load poll distribution after members are loaded
            loadPollDistribution();
        });
}

// Populate members table
function populateMembersGrid(data) {
    const tableBody = document.getElementById('membersTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (data.members.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9">
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p class="empty-title">कोई सदस्य नहीं मिला</p>
                        <p class="empty-text">कृपया अपनी खोज मानदंड बदलें और पुनः प्रयास करें।</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    // Calculate starting SN based on total records and current page
    const limit = data.limit || 12;
    const totalRecords = data.total || 0;
    const startingSN = totalRecords - ((data.page - 1) * limit);

    data.members.forEach((member, index) => {
        const sn = startingSN - index;
        const row = createMemberTableRow(member, sn);
        tableBody.appendChild(row);
    });
}

// Create member table row element
// Create member table row element
function createMemberTableRow(member, sn) {
    const row = document.createElement('tr');
    
    // Store member data in row attribute with SN
    const memberDataWithSN = { ...member, sn: sn };
    row.dataset.memberData = JSON.stringify(memberDataWithSN);

    // Format status
    const statusText = member.status == 1 ? 'अनुमोदित' : 'लंबित';
    const statusClass = member.status == 1 ? 'status-verified' : 'status-pending';

    // Format submission date
    const submissionDate = member.created_at ? new Date(member.created_at).toLocaleDateString('hi-IN') : 'N/A';
    
    // Format poll option with default 'A'
    const pollOption = member.poll_option && member.poll_option.trim() ? member.poll_option : 'A';

    row.innerHTML = `
        <td><strong>${escapeHtml(sn || member.id || 'N/A')}</strong></td>
        <td><span class="member-link">${escapeHtml(member.member_id)}</span></td>
        <td>${escapeHtml(member.full_name)}</td>
        <td>${escapeHtml(member.district || 'N/A')}</td>
        <td>${escapeHtml(member.block || 'N/A')}</td>
        <td>${escapeHtml(pollOption)}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td>${submissionDate}</td>
        <td>
            <button class="action-button">विवरण देखें</button>
        </td>
    `;

    // Add click listeners
    const memberLink = row.querySelector('.member-link');
    const actionBtn = row.querySelector('.action-button');
    
    const clickHandler = () => {
        const memberData = JSON.parse(row.dataset.memberData);
        openMemberDetails(memberData);
    };
    
    if (memberLink) memberLink.addEventListener('click', clickHandler);
    if (actionBtn) actionBtn.addEventListener('click', clickHandler);

    return row;
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Open member details modal
function openMemberDetails(member) {
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('modalBody');
    if (!modal || !modalBody) return;

    const submissionDate = member.created_at ? new Date(member.created_at).toLocaleDateString('hi-IN') : 'N/A';
    const statusText = member.status == 1 ? 'अनुमोदित' : 'लंबित';
    const pollOption = member.poll_option && member.poll_option.trim() ? member.poll_option : 'A';
    const sn = member.sn || member.id || 'N/A';
    
    // Build full address
    const addressParts = [];
    if (member.permanent_address && member.permanent_address.trim()) {
        addressParts.push(member.permanent_address.trim());
    }
    if (member.block && member.block.trim()) {
        addressParts.push(member.block.trim());
    }
    if (member.district && member.district.trim()) {
        addressParts.push(member.district.trim());
    }
    if (member.state && member.state.trim()) {
        addressParts.push(member.state.trim());
    }
    const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : 'N/A';

    const detailsHTML = `
        <div class="detail-group">
            <div class="detail-label">SN.</div>
            <div class="detail-value">${sn}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Unique ID</div>
            <div class="detail-value">${member.member_id}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">नाम</div>
            <div class="detail-value">${member.full_name}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">पिता/पति का नाम</div>
            <div class="detail-value">${member.father_husband_name || 'N/A'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Nominee का नाम</div>
            <div class="detail-value">${member.nominee_name || 'N/A'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Nominee का संबंध</div>
            <div class="detail-value">${member.nominee_relation || 'N/A'}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Full Address</div>
            <div class="detail-value">${fullAddress}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Poll Option</div>
            <div class="detail-value">${pollOption}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Status</div>
            <div class="detail-value">${statusText}</div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Submission Date</div>
            <div class="detail-value">${submissionDate}</div>
        </div>
    `;

    modalBody.innerHTML = detailsHTML;
    modal.classList.add('show');
}

// Close modal
function closeModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Reset all filters
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('districtFilter').value = '';
    document.getElementById('blockFilter').value = '';
    
    currentPage = 1;
    currentSearch = '';
    currentDistrict = '';
    currentBlock = '';
    
    loadMembers();
}

// Update statistics
function updateStats(stats) {
    if (!stats) return;

    const totalMembersEl = document.getElementById('totalMembers');
    const verifiedMembersEl = document.getElementById('verifiedMembers');
    const districtCountEl = document.getElementById('districtCount');
    const blockCountEl = document.getElementById('blockCount');

    if (totalMembersEl) {
        totalMembersEl.textContent = stats.total_members || 0;
    }

    if (verifiedMembersEl) {
        verifiedMembersEl.textContent = stats.verified_members || 0;
    }

    if (districtCountEl) {
        districtCountEl.textContent = stats.district_count || 0;
    }

    if (blockCountEl) {
        blockCountEl.textContent = stats.block_count || 0;
    }
}

// Update pagination
function updatePagination(data) {
    const paginationContainer = document.getElementById('pagination');
    if (!paginationContainer) return;

    paginationContainer.innerHTML = '';

    const totalPages = data.total_pages;
    const currentPageNum = data.page;

    // Previous button
    if (currentPageNum > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '← पिछला';
        prevBtn.onclick = () => {
            currentPage = currentPageNum - 1;
            loadMembers();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
        paginationContainer.appendChild(prevBtn);
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 ||
            i === totalPages ||
            (i >= currentPageNum - 1 && i <= currentPageNum + 1)
        ) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === currentPageNum ? 'active' : '';
            pageBtn.onclick = () => {
                currentPage = i;
                loadMembers();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };
            paginationContainer.appendChild(pageBtn);
        } else if (
            (i === currentPageNum - 2 && currentPageNum > 3) ||
            (i === currentPageNum + 2 && currentPageNum < totalPages - 2)
        ) {
            const dots = document.createElement('span');
            dots.textContent = '...';
            dots.style.padding = '8px 5px';
            paginationContainer.appendChild(dots);
        }
    }

    // Next button
    if (currentPageNum < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'अगला →';
        nextBtn.onclick = () => {
            currentPage = currentPageNum + 1;
            loadMembers();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
        paginationContainer.appendChild(nextBtn);
    }
}

// Show empty state
function showEmptyState() {
    const tableBody = document.getElementById('membersTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = `
        <tr>
            <td colspan="7">
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p class="empty-title">कोई सदस्य नहीं मिला</p>
                    <p class="empty-text">कृपया अपनी खोज मानदंड बदलें और पुनः प्रयास करें।</p>
                </div>
            </td>
        </tr>
    `;
}

// Show loading
function showLoading() {
    const tableBody = document.getElementById('membersTableBody');
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="loading-row">
                    <div class="spinner"></div>
                </td>
            </tr>
        `;
    }
}

// Hide loading
function hideLoading() {
    // Already hidden when content is loaded
}

// Show alert message
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;

    // Add to page
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Escape key to close modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});
