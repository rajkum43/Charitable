/**
 * Death Sahyog Suchi JavaScript
 * Handles filters, search, and pagination for Death Claims
 */

let allData = [];
let filteredData = [];
let currentPage = 1;
let entriesPerPage = 10;

// DOM Elements
const tableSection = document.getElementById('tableSection');
const noDataMessage = document.getElementById('noDataMessage');
const loadingSpinner = document.getElementById('loadingSpinner');
const applicationsTableBody = document.getElementById('applicationsTableBody');
const districtFilter = document.getElementById('districtFilter');
const blockFilter = document.getElementById('blockFilter');
const searchInput = document.getElementById('searchInput');
const entriesSelect = document.getElementById('entriesSelect');
const pagination = document.getElementById('pagination');
const totalApplications = document.getElementById('totalApplications');
const totalAmount = document.getElementById('totalAmount');
const verifiedCount = document.getElementById('verifiedCount');
const pendingCount = document.getElementById('pendingCount');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    loadDistricts();
});

/**
 * Load data from API
 */
async function loadData() {
    loadingSpinner.style.display = 'block';
    tableSection.style.display = 'none';
    noDataMessage.style.display = 'none';

    try {
        const response = await fetch('../api/get_death_sahyog_suchi.php');
        const data = await response.json();

        if (data.success) {
            allData = data.data || [];
            filteredData = [...allData];
            applyFilters();
            updateStats();
        } else {
            console.error('Error:', data.message);
            noDataMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading data:', error);
        noDataMessage.style.display = 'block';
    } finally {
        loadingSpinner.style.display = 'none';
    }
}

/**
 * Load districts for filter
 */
async function loadDistricts() {
    try {
        const response = await fetch('../api/get_districts.php');
        const data = await response.json();

        if (data.success) {
            districtFilter.innerHTML = '<option value="">-- सभी जिले --</option>';
            data.districts.forEach(item => {
                const option = document.createElement('option');
                option.value = item.district;
                option.textContent = item.district;
                districtFilter.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading districts:', error);
    }
}

/**
 * Load blocks based on selected district
 */
async function loadBlocks(district) {
    if (!district) {
        blockFilter.innerHTML = '<option value="">-- सभी ब्लॉक --</option>';
        blockFilter.disabled = true;
        return;
    }

    try {
        const response = await fetch(`../api/get_blocks.php?district=${encodeURIComponent(district)}`);
        const data = await response.json();

        if (data.success) {
            blockFilter.innerHTML = '<option value="">-- सभी ब्लॉक --</option>';
            data.blocks.forEach(item => {
                const option = document.createElement('option');
                option.value = item.block;
                option.textContent = item.block;
                blockFilter.appendChild(option);
            });
            blockFilter.disabled = false;
        }
    } catch (error) {
        console.error('Error loading blocks:', error);
    }
}

/**
 * Apply filters and search
 */
function applyFilters() {
    const district = districtFilter.value;
    const block = blockFilter.value;
    const searchTerm = searchInput.value.toLowerCase();
    entriesPerPage = parseInt(entriesSelect.value) || 10;

    // Filter data
    filteredData = allData.filter(item => {
        const matchDistrict = !district || item.district === district;
        const matchBlock = !block || item.block === block;
        const matchSearch = !searchTerm || 
            item.full_name.toLowerCase().includes(searchTerm) ||
            item.claim_number.toLowerCase().includes(searchTerm) ||
            item.member_id.toLowerCase().includes(searchTerm) ||
            item.donation_to_member_id.toLowerCase().includes(searchTerm);

        return matchDistrict && matchBlock && matchSearch;
    });

    currentPage = 1;
    displayTable();
    updateStats();
}

/**
 * Display table with pagination
 */
function displayTable() {
    applicationsTableBody.innerHTML = '';
    
    if (filteredData.length === 0) {
        tableSection.style.display = 'none';
        noDataMessage.style.display = 'block';
        return;
    }

    tableSection.style.display = 'block';
    noDataMessage.style.display = 'none';

    // Calculate pagination
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    const startIndex = (currentPage - 1) * entriesPerPage;
    const endIndex = startIndex + entriesPerPage;
    const pageData = filteredData.slice(startIndex, endIndex);

    // Display rows
    pageData.forEach((item, index) => {
        const rowNumber = startIndex + index + 1;
        const row = document.createElement('tr');
        
        const createdDate = new Date(item.created_at);
        const formattedDate = createdDate.toLocaleDateString('hi-IN', {
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        });

        row.innerHTML = `
            <td>${rowNumber}</td>
            <td><strong>${item.full_name}</strong></td>
            <td><span class="unique-id">${item.claim_number}</span></td>
            <td><span class="amount-cell">₹ ${parseFloat(item.amount || 0).toFixed(2)}</span></td>
            <td>${item.recipient_name || '-'}</td>
            <td>${item.district || '-'}</td>
            <td>${item.block || '-'}</td>
            <td><span class="date-cell">${formattedDate}</span></td>
        `;
        
        applicationsTableBody.appendChild(row);
    });

    // Display pagination
    displayPagination(totalPages);
}

/**
 * Display pagination controls
 */
function displayPagination(totalPages) {
    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    const prevBtn = document.createElement('a');
    prevBtn.className = 'page-link';
    prevBtn.href = '#';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.onclick = (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            displayTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
    prevLi.appendChild(prevBtn);
    pagination.appendChild(prevLi);

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstPageLi = createPageItem(1, currentPage === 1);
        pagination.appendChild(firstPageLi);

        if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = '<span class="page-link">...</span>';
            pagination.appendChild(dotsLi);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageLi = createPageItem(i, currentPage === i);
        pagination.appendChild(pageLi);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = '<span class="page-link">...</span>';
            pagination.appendChild(dotsLi);
        }

        const lastPageLi = createPageItem(totalPages, currentPage === totalPages);
        pagination.appendChild(lastPageLi);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    const nextBtn = document.createElement('a');
    nextBtn.className = 'page-link';
    nextBtn.href = '#';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.onclick = (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            displayTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
    nextLi.appendChild(nextBtn);
    pagination.appendChild(nextLi);
}

/**
 * Create a page item for pagination
 */
function createPageItem(pageNum, isActive) {
    const li = document.createElement('li');
    li.className = `page-item ${isActive ? 'active' : ''}`;
    
    const a = document.createElement('a');
    a.className = 'page-link';
    a.href = '#';
    a.textContent = pageNum;
    a.onclick = (e) => {
        e.preventDefault();
        if (!isActive) {
            currentPage = pageNum;
            displayTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
    
    li.appendChild(a);
    return li;
}

/**
 * Update statistics
 */
function updateStats() {
    const total = allData.length;
    const totalAmt = allData.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    const verified = allData.filter(item => item.status === 'verified').length;
    const pending = allData.filter(item => item.status === 'pending').length;

    totalApplications.textContent = total;
    totalAmount.textContent = `₹ ${totalAmt.toLocaleString('en-IN', { maximumFractionDigits: 0 })}`;
    verifiedCount.textContent = verified;
    pendingCount.textContent = pending;
}

// Handle district change
districtFilter.addEventListener('change', function() {
    blockFilter.value = '';
    loadBlocks(this.value);
    applyFilters();
});

// Handle block change
blockFilter.addEventListener('change', function() {
    applyFilters();
});

// Handle search input
searchInput.addEventListener('keyup', function() {
    applyFilters();
});

// Handle entries per page change
entriesSelect.addEventListener('change', function() {
    applyFilters();
});

// Reset search
function resetSearch() {
    searchInput.value = '';
    districtFilter.value = '';
    blockFilter.value = '';
    blockFilter.disabled = true;
    entriesSelect.value = '10';
    currentPage = 1;
    filteredData = [...allData];
    displayTable();
    updateStats();
}
