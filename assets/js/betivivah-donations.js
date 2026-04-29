/**
 * Beti Vivah Donations Page JavaScript
 */

const BASE_PATH = document.querySelector('body').getAttribute('data-base-path') || '/Charitable/';
const MEMBER_ID = document.querySelector('body').getAttribute('data-member-id');

let allData = [];
let currentPage = 1;
let entriesPerPage = 10;
let filteredData = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadDonations();
    
    // Event listeners
    document.getElementById('districtFilter').addEventListener('change', loadBlocksByDistrict);
    document.getElementById('blockFilter').addEventListener('change', applyFilters);
    document.getElementById('entriesSelect').addEventListener('change', function() {
        entriesPerPage = parseInt(this.value);
        currentPage = 1;
        displayTable();
    });
    document.getElementById('searchInput').addEventListener('keyup', applyFilters);
});

function loadDonations() {
    document.getElementById('loadingSpinner').style.display = 'block';
    document.getElementById('tableSection').style.display = 'none';
    document.getElementById('noDataMessage').style.display = 'none';

    fetch(`${BASE_PATH}api/get_betivivah_donations.php?member_id=${MEMBER_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allData = data.donations || [];
                filteredData = allData;
                populateDistrictFilter();
                updateTotalCollection();
                displayTable();
            } else {
                console.error('Error:', data.error);
            }
            document.getElementById('loadingSpinner').style.display = 'none';
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('loadingSpinner').style.display = 'none';
        });
}

function populateDistrictFilter() {
    const districts = [...new Set(allData
        .map(donation => donation.donor_district)
        .filter(d => d && d !== 'Unknown'))];
    
    const districtFilter = document.getElementById('districtFilter');
    districts.sort().forEach(district => {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        districtFilter.appendChild(option);
    });
}

function loadBlocksByDistrict() {
    const district = document.getElementById('districtFilter').value;
    const blockFilter = document.getElementById('blockFilter');
    
    blockFilter.innerHTML = '<option value="">सभी ब्लॉक</option>';
    blockFilter.disabled = true;

    if (district) {
        const blocks = [...new Set(allData
            .filter(donation => donation.donor_district === district)
            .map(donation => donation.donor_block)
            .filter(block => block && block !== 'Unknown'))];
        
        blocks.sort().forEach(block => {
            const option = document.createElement('option');
            option.value = block;
            option.textContent = block;
            blockFilter.appendChild(option);
        });
        
        blockFilter.disabled = blocks.length === 0;
    }

    currentPage = 1;
    applyFilters();
}

function applyFilters() {
    const district = document.getElementById('districtFilter').value;
    const block = document.getElementById('blockFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();

    filteredData = allData.filter(donation => {
        const matchDistrict = !district || donation.donor_district === district;
        const matchBlock = !block || donation.donor_block === block;
        const matchSearch = !search || 
            donation.donor_name.toLowerCase().includes(search) ||
            donation.donor_member_id.toLowerCase().includes(search);

        return matchDistrict && matchBlock && matchSearch;
    });

    currentPage = 1;
    displayTable();
}

function displayTable() {
    const tbody = document.getElementById('donationsTableBody');
    const tableSection = document.getElementById('tableSection');
    const noDataMessage = document.getElementById('noDataMessage');

    if (filteredData.length === 0) {
        tableSection.style.display = 'none';
        noDataMessage.style.display = 'block';
        return;
    }

    tableSection.style.display = 'block';
    noDataMessage.style.display = 'none';

    // Calculate pagination
    const totalPages = Math.ceil(filteredData.length / entriesPerPage);
    const startIdx = (currentPage - 1) * entriesPerPage;
    const endIdx = startIdx + entriesPerPage;
    const pageData = filteredData.slice(startIdx, endIdx);

    // Populate table
    tbody.innerHTML = pageData.map((donation, idx) => {
        const serialNo = startIdx + idx + 1;
        const donationDate = new Date(donation.created_at).toLocaleDateString('hi-IN');
        
        return `
            <tr>
                <td>${serialNo}</td>
                <td>${escapeHtml(donation.donor_name)}</td>
                <td>${escapeHtml(donation.donor_member_id)}</td>
                <td>${donation.amount}</td>
                <td>${escapeHtml(donation.recipient_name)}</td>
                <td>${escapeHtml(donation.donor_district)}</td>
                <td>${escapeHtml(donation.donor_block)}</td>
                <td>${donationDate}</td>
            </tr>
        `;
    }).join('');

    // Update pagination
    updatePagination(totalPages);
}

function updatePagination(totalPages) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous button
    if (currentPage > 1) {
        const prevBtn = document.createElement('a');
        prevBtn.href = '#';
        prevBtn.className = 'page-link';
        prevBtn.textContent = 'Previous';
        prevBtn.onclick = (e) => {
            e.preventDefault();
            currentPage--;
            displayTable();
            window.scrollTo(0, 0);
        };
        pagination.appendChild(prevBtn);
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            const activePage = document.createElement('span');
            activePage.className = 'page-link active';
            activePage.textContent = i;
            pagination.appendChild(activePage);
        } else {
            const pageBtn = document.createElement('a');
            pageBtn.href = '#';
            pageBtn.className = 'page-link';
            pageBtn.textContent = i;
            pageBtn.onclick = (e) => {
                e.preventDefault();
                currentPage = i;
                displayTable();
                window.scrollTo(0, 0);
            };
            pagination.appendChild(pageBtn);
        }
    }

    // Next button
    if (currentPage < totalPages) {
        const nextBtn = document.createElement('a');
        nextBtn.href = '#';
        nextBtn.className = 'page-link';
        nextBtn.textContent = 'Next';
        nextBtn.onclick = (e) => {
            e.preventDefault();
            currentPage++;
            displayTable();
            window.scrollTo(0, 0);
        };
        pagination.appendChild(nextBtn);
    }
}

function updateTotalCollection() {
    const total = allData.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
    document.getElementById('totalCollection').textContent = total.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
