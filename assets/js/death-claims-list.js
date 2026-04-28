/**
 * Death Claims List Management
 * Handles data fetching, filtering, pagination, and display
 */

let currentPage = 1;
const API_URL = '../api/get_death_claims_applications.php';
const baseURL = window.location.origin + '/Charitable';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadApplications();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('filterBtn').addEventListener('click', function() {
        currentPage = 1;
        loadApplications();
    });

    document.getElementById('resetBtn').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('districtFilter').value = '';
        document.getElementById('blockFilter').value = '';
        document.getElementById('statusFilter').value = '';
        currentPage = 1;
        loadApplications();
    });

    document.getElementById('districtFilter').addEventListener('change', function() {
        if (this.value) {
            loadBlocks(this.value);
        }
    });

    document.getElementById('modalClose').addEventListener('click', closeModal);
    document.getElementById('imageViewerClose').addEventListener('click', closeImageViewer);
}

function getFilterParams() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value.trim();
    const district = document.getElementById('districtFilter').value;
    const block = document.getElementById('blockFilter').value;
    const status = document.getElementById('statusFilter').value;

    if (search) params.append('search', search);
    if (district) params.append('district', district);
    if (block) params.append('block', block);
    if (status) params.append('status', status);
    params.append('page', currentPage);

    return params.toString();
}

function loadApplications() {
    const filterParams = getFilterParams();
    const url = API_URL + '?' + filterParams;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplications(data.data.applications);
                updateStatistics(data.data.stats);
                populateDistricts(data.data.districts);
                displayPagination(data.data);
            } else {
                showError('डेटा लोड करने में त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('डेटा लोड करने में त्रुटि हुई');
        });
}

function displayApplications(applications) {
    const tbody = document.getElementById('applicationsTableBody');
    tbody.innerHTML = '';

    if (applications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4">कोई आवेदन नहीं मिला</td></tr>';
        return;
    }

    applications.forEach((app, index) => {
        const sno = ((currentPage - 1) * 20) + (index + 1);
        const deathDate = formatDate(app.death_date);
        const createdDate = formatDate(app.created_at);
        const statusClass = 'status-' + app.status;
        const statusText = getStatusText(app.status);

        let documentsHtml = '';
        if (app.death_certificate) {
            documentsHtml += `
                <div class="certificate-preview-box">
                    <img src="../${escapeHtml(app.death_certificate)}" alt="मृत्यु पत्र" class="certificate-img" onclick="viewImage('${baseURL}/${app.death_certificate}')" title="मृत्यु पत्र" />
                </div>
            `;
        } else {
            documentsHtml = '-';
        }

        const row = `
            <tr>
                <td>${sno}</td>
                <td><strong>${escapeHtml(app.application_number)}</strong></td>
                <td>${escapeHtml(app.member_name)}</td>
                <td>${escapeHtml(app.deceased_name)}</td>
                <td>${deathDate}</td>
                <td>${escapeHtml(app.applicant_name)}</td>
                <td>${escapeHtml(app.district)}</td>
                <td>${escapeHtml(app.block)}</td>
                <td>${documentsHtml || '-'}</td>
                <td>${createdDate}</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="showDetails(${JSON.stringify(app).replace(/"/g, '&quot;')})">
                        <i class="fas fa-eye"></i> विवरण
                    </button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function updateStatistics(stats) {
    document.getElementById('totalApplications').textContent = stats.total || 0;
    document.getElementById('pendingApplications').textContent = stats.pending || 0;
    document.getElementById('underReviewApplications').textContent = stats.under_review || 0;
    document.getElementById('approvedApplications').textContent = stats.approved || 0;
    document.getElementById('rejectedApplications').textContent = stats.rejected || 0;
}

function populateDistricts(districts) {
    const districtSelect = document.getElementById('districtFilter');
    const currentValue = districtSelect.value;

    // Clear existing options except the first one
    while (districtSelect.options.length > 1) {
        districtSelect.remove(1);
    }

    districts.forEach(district => {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        districtSelect.appendChild(option);
    });

    districtSelect.value = currentValue;
}

function loadBlocks(district) {
    const blockSelect = document.getElementById('blockFilter');
    blockSelect.innerHTML = '<option value="">सभी ब्लॉक</option>';

    if (!district) return;

    const filterParams = new URLSearchParams();
    filterParams.append('district', district);

    fetch(API_URL + '?' + filterParams)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.blocks) {
                data.data.blocks.forEach(block => {
                    const option = document.createElement('option');
                    option.value = block;
                    option.textContent = block;
                    blockSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading blocks:', error));
}

function displayPagination(data) {
    const paginationContainer = document.getElementById('pagination');
    paginationContainer.innerHTML = '';

    if (data.totalPages <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
    prevLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage - 1})"><i class="fas fa-chevron-left"></i> पिछला</a>`;
    ul.appendChild(prevLi);

    // Page numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(data.totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="goToPage(1)">1</a>`;
        ul.appendChild(firstLi);

        if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            ul.appendChild(dotsLi);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = 'page-item ' + (i === currentPage ? 'active' : '');
        li.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a>`;
        ul.appendChild(li);
    }

    if (endPage < data.totalPages) {
        if (endPage < data.totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            ul.appendChild(dotsLi);
        }

        const lastLi = document.createElement('li');
        lastLi.className = 'page-item';
        lastLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="goToPage(${data.totalPages})">${data.totalPages}</a>`;
        ul.appendChild(lastLi);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = 'page-item ' + (currentPage === data.totalPages ? 'disabled' : '');
    nextLi.innerHTML = `<a class="page-link" href="javascript:void(0)" onclick="goToPage(${currentPage + 1})">अगला <i class="fas fa-chevron-right"></i></a>`;
    ul.appendChild(nextLi);

    paginationContainer.appendChild(ul);
}

function goToPage(page) {
    currentPage = page;
    loadApplications();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showDetails(app) {
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('modalBody');
    const deathDate = formatDate(app.death_date);
    const createdDate = formatDate(app.created_at);
    const statusText = getStatusText(app.status);

    let documentsHtml = '';
    if (app.death_certificate) {
        documentsHtml += `<a href="javascript:void(0)" onclick="viewImage('${baseURL}/${app.death_certificate}')" class="btn btn-sm btn-primary me-2"><i class="fas fa-file-pdf"></i> मृत्यु पत्र</a>`;
    }
    if (app.deceased_aadhar) {
        documentsHtml += `<a href="javascript:void(0)" onclick="viewImage('${baseURL}/${app.deceased_aadhar}')" class="btn btn-sm btn-primary"><i class="fas fa-id-card"></i> आधार</a>`;
    }

    modalBody.innerHTML = `
        <div class="details-content">
            <div class="details-grid">
                <div class="detail-item">
                    <label>आवेदन संख्या:</label>
                    <p>${escapeHtml(app.application_number)}</p>
                </div>
                <div class="detail-item">
                    <label>सदस्य ID:</label>
                    <p>${escapeHtml(app.member_id)}</p>
                </div>
                <div class="detail-item">
                    <label>सदस्य नाम:</label>
                    <p>${escapeHtml(app.member_name)}</p>
                </div>
                <div class="detail-item">
                    <label>दिवंगत नाम:</label>
                    <p>${escapeHtml(app.deceased_name)}</p>
                </div>
                <div class="detail-item">
                    <label>आवेदक नाम:</label>
                    <p>${escapeHtml(app.applicant_name)}</p>
                </div>
                <div class="detail-item">
                    <label>सम्बन्ध:</label>
                    <p>${escapeHtml(app.applicant_relation)}</p>
                </div>
                <div class="detail-item">
                    <label>मृत्यु तिथि:</label>
                    <p>${deathDate}</p>
                </div>
                <div class="detail-item">
                    <label>स्थिति:</label>
                    <p><span class="status-badge status-${app.status}">${statusText}</span></p>
                </div>
                <div class="detail-item">
                    <label>आवेदन तिथि:</label>
                    <p>${createdDate}</p>
                </div>
                <div class="detail-item">
                    <label>जिला:</label>
                    <p>${escapeHtml(app.district)}</p>
                </div>
                <div class="detail-item">
                    <label>ब्लॉक:</label>
                    <p>${escapeHtml(app.block)}</p>
                </div>
                <div class="detail-item">
                    <label>राज्य:</label>
                    <p>${escapeHtml(app.state)}</p>
                </div>
                <div class="detail-item">
                    <label>स्थाई पता:</label>
                    <p>${escapeHtml(app.permanent_address)}</p>
                </div>
                <div class="detail-item">
                    <label>आवेदन पता:</label>
                    <p>${escapeHtml(app.member_address)}</p>
                </div>
                <div class="detail-item">
                    <label>टिप्पणी:</label>
                    <p>${escapeHtml(app.remarks || '-')}</p>
                </div>
            </div>
            ${documentsHtml ? `
            <div class="mt-4 pt-4 border-top">
                <h5 class="mb-3">दस्तावेज़:</h5>
                <div class="document-buttons">
                    ${documentsHtml}
                </div>
            </div>
            ` : ''}
        </div>
    `;

    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function viewImage(url) {
    document.getElementById('imageViewerImg').src = url;
    document.getElementById('imageViewerModal').style.display = 'block';
}

function closeImageViewer() {
    document.getElementById('imageViewerModal').style.display = 'none';
}

function getStatusText(status) {
    const statusMap = {
        'submitted': 'लंबित',
        'under_review': 'समीक्षा में',
        'approved': 'स्वीकृत',
        'rejected': 'अस्वीकृत'
    };
    return statusMap[status] || status;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('hi-IN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showError(message) {
    alert(message);
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const detailsModal = document.getElementById('detailsModal');
    const imageViewerModal = document.getElementById('imageViewerModal');
    
    if (event.target === detailsModal) {
        detailsModal.style.display = 'none';
    }
    if (event.target === imageViewerModal) {
        imageViewerModal.style.display = 'none';
    }
});
