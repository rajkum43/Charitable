/**
 * Alert Death Details - JavaScript
 * Handles filtering, search, and pagination for death sahyog donations
 */

const BASE_PATH = document.body.getAttribute('data-base-path');
// Get alert number from URL - must be called after DOM is ready
let ALERT_NUMBER = 0;

// Get alert from URL parameter
function getAlertNumberFromURL() {
    const params = new URLSearchParams(window.location.search);
    return parseInt(params.get('alert') || 0);
}

let allData = [];
let currentPage = 1;
let itemsPerPage = 25;
let filteredData = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    ALERT_NUMBER = getAlertNumberFromURL();
    if (ALERT_NUMBER <= 0) {
        window.location.href = BASE_PATH + 'pages/alert-death-suchi.php';
        return;
    }
    loadAlertData();
    setupEventListeners();
});

/**
 * Fetch alert donation data via AJAX
 */
function loadAlertData() {
    const loadingSpinner = document.getElementById('loadingSpinner');
    const tableSection = document.getElementById('tableSection');
    const noDataMessage = document.getElementById('noDataMessage');

    loadingSpinner.style.display = 'block';
    tableSection.style.display = 'none';
    noDataMessage.style.display = 'none';

    fetch(`${BASE_PATH}api/get_alert_death_donations.php?alert=${ALERT_NUMBER}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.donations.length > 0) {
                allData = data.donations;
                filteredData = [...allData];
                loadingSpinner.style.display = 'none';
                tableSection.style.display = 'block';
                displayTable();
            } else {
                loadingSpinner.style.display = 'none';
                noDataMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            loadingSpinner.style.display = 'none';
            noDataMessage.style.display = 'block';
        });
}

/**
 * Setup event listeners for filters and search
 */
function setupEventListeners() {
    const districtFilter = document.getElementById('districtFilter');
    const blockFilter = document.getElementById('blockFilter');
    const searchInput = document.getElementById('searchInput');

    // District filter change
    if (districtFilter) {
        districtFilter.addEventListener('change', function() {
            const selectedDistrict = this.value;

            if (selectedDistrict && blockFilter) {
                const blocks = [...new Set(
                    allData
                        .filter(item => item.donor_district === selectedDistrict)
                        .map(item => item.donor_block)
                )];

                blockFilter.disabled = false;
                blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
                blocks.forEach(block => {
                    blockFilter.innerHTML += `<option value="${block}">${block}</option>`;
                });
            } else if (blockFilter) {
                blockFilter.disabled = true;
                blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
            }

            applyFilters();
        });
    }

    // Block filter change
    if (blockFilter) {
        blockFilter.addEventListener('change', applyFilters);
    }

    // Search input
    if (searchInput) {
        searchInput.addEventListener('keyup', applyFilters);
    }
}

/**
 * Apply all filters (district, block, search)
 */
function applyFilters() {
    const districtFilter = document.getElementById('districtFilter');
    const blockFilter = document.getElementById('blockFilter');
    const searchInput = document.getElementById('searchInput');

    const selectedDistrict = districtFilter ? districtFilter.value : '';
    const selectedBlock = blockFilter ? blockFilter.value : '';
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

    filteredData = allData.filter(item => {
        const districtMatch = !selectedDistrict || item.donor_district === selectedDistrict;
        const blockMatch = !selectedBlock || item.donor_block === selectedBlock;
        const searchMatch = !searchTerm ||
            item.donor_name.toLowerCase().includes(searchTerm) ||
            item.donor_member_id.toLowerCase().includes(searchTerm) ||
            item.recipient_name.toLowerCase().includes(searchTerm);

        return districtMatch && blockMatch && searchMatch;
    });

    currentPage = 1;
    displayTable();
}

/**
 * Display table with current page data
 */
function displayTable() {
    const tableBody = document.getElementById('applicationsTableBody');
    const paginationDiv = document.getElementById('pagination');

    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    const pageData = filteredData.slice(startIdx, endIdx);

    if (pageData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4">कोई डेटा नहीं मिला</td></tr>';
        paginationDiv.innerHTML = '';
        return;
    }

    tableBody.innerHTML = pageData.map((item, idx) => `
        <tr>
            <td>${startIdx + idx + 1}</td>
            <td>${item.donor_name}</td>
            <td>${item.donor_member_id}</td>
            <td>₹${parseFloat(item.amount).toFixed(2)}</td>
            <td>${item.recipient_name}</td>
            <td>${item.donor_district}</td>
            <td>${item.donor_block}</td>
            <td>${new Date(item.created_at).toLocaleDateString('hi-IN')}</td>
        </tr>
    `).join('');

    displayPagination();
}

/**
 * Display pagination controls
 */
function displayPagination() {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const paginationDiv = document.getElementById('pagination');

    if (totalPages <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }

    let html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${currentPage - 1})">
                <i class="fas fa-chevron-left"></i> पिछला
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
                </li>
            `;
        } else if (i === 2 || i === totalPages - 1) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${currentPage + 1})">
                अगला <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul></nav>';
    paginationDiv.innerHTML = html;
}

/**
 * Go to specific page
 * @param {number} pageNum - Page number to navigate to
 */
function goToPage(pageNum) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    if (pageNum >= 1 && pageNum <= totalPages) {
        currentPage = pageNum;
        displayTable();
        window.scrollTo(0, 0);
    }
}
