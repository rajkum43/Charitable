/**
 * Donation History Page JavaScript
 * Handles loading and displaying member's donation history
 */

let allDonations = [];
let filteredDonations = [];

document.addEventListener('DOMContentLoaded', function() {
    loadDonationHistory();
    setupFilterListeners();
});

/**
 * Load donation history from API
 */
function loadDonationHistory() {
    // Use dynamic base URL from config.js
    const baseUrl = window.BASE_URL || '/Charitable/';
    
    fetch(`${baseUrl}api/get_member_donation_history.php`, {
        method: 'GET',
        credentials: 'include',  // Include session cookies
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load donation history');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.donations) {
                allDonations = data.donations;
                filteredDonations = allDonations;
                displayDonations();
                updateStatistics();
            } else {
                showError('दान का इतिहास लोड करने में त्रुटि');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('दान का इतिहास लोड करने में विफल रहा');
        });
}

/**
 * Display donations in table
 */
function displayDonations() {
    const tbody = document.getElementById('donationsBody');
    const emptyState = document.getElementById('emptyState');
    
    tbody.innerHTML = '';
    
    if (filteredDonations.length === 0) {
        emptyState.style.display = 'block';
        return;
    }
    
    emptyState.style.display = 'none';
    
    filteredDonations.forEach((donation, index) => {
        const row = createDonationRow(donation);
        tbody.appendChild(row);
    });
}

/**
 * Create a table row for a donation
 */
function createDonationRow(donation) {
    const row = document.createElement('tr');
    
    // Show applicant name
    const applicantName = donation.applicant_name || 'N/A';
    
    // Format type
    const typeLabel = donation.application_type === 'Death_Claims' ? 'मृत्यु दान' : 'बेटी विवाह';
    const typeClass = donation.application_type === 'Death_Claims' ? 'type-death' : 'type-beti';
    
    // Format date
    const donationDate = new Date(donation.donation_date);
    const formattedDate = donationDate.toLocaleDateString('hi-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Format amount
    const amount = parseFloat(donation.amount || 0);
    const formattedAmount = amount.toLocaleString('hi-IN');
    
    row.innerHTML = `
        <td>
            <div class="recipient-info">
                <div class="recipient-name">${applicantName}</div>
            </div>
        </td>
        <td>
            <span class="type-badge ${typeClass}">${typeLabel}</span>
        </td>
        <td>
            <span class="amount-text">₹${formattedAmount}</span>
        </td>
        <td>${formattedDate}</td>
        <td>
            <button class="action-btn btn-receipt" onclick="viewReceipt('${donation.transaction_id}', event)" style="border: none; background-color: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; transition: all 0.3s ease;">
                <i class="fas fa-file-pdf"></i> रसीद देखें
            </button>
        </td>
    `;
    
    return row;
}

/**
 * Get status label in Hindi
 */
function getStatusLabel(status) {
    const statusMap = {
        'pending': 'लंबित',
        'verified': 'सत्यापित',
        'rejected': 'अस्वीकृत'
    };
    return statusMap[status] || status;
}

/**
 * Update statistics cards
 */
function updateStatistics() {
    const totalDonations = allDonations.length;
    const totalAmount = allDonations.reduce((sum, d) => sum + (parseFloat(d.amount) || 0), 0);
    const verifiedCount = allDonations.filter(d => d.status === 'verified').length;
    
    document.getElementById('totalDonations').textContent = totalDonations;
    document.getElementById('totalAmount').textContent = 
        '₹' + totalAmount.toLocaleString('hi-IN', { maximumFractionDigits: 0 });
    document.getElementById('verifiedDonations').textContent = verifiedCount;
}

/**
 * Setup filter event listeners
 */
function setupFilterListeners() {
    document.getElementById('filterType').addEventListener('change', applyFilters);
}

/**
 * Apply filters to donations list
 */
function applyFilters() {
    const typeFilter = document.getElementById('filterType').value;
    
    filteredDonations = allDonations.filter(donation => {
        const typeMatch = !typeFilter || donation.application_type === typeFilter;
        return typeMatch;
    });
    
    displayDonations();
}

/**
 * View receipt for a donation
 */
function viewReceipt(transactionId, event) {
    if (event) {
        event.preventDefault();
    }
    
    if (!transactionId) {
        alert('रसीद की जानकारी उपलब्ध नहीं है');
        return;
    }
    
    const baseUrl = window.BASE_URL || '/Charitable/';
    const receiptUrl = `${baseUrl}member/generate_donation_receipt.php?txn_id=${transactionId}`;
    
    // Open receipt in new window
    window.open(receiptUrl, '_blank', 'width=900,height=800,scrollbars=yes');
}

/**
 * Show error message
 */
function showError(message) {
    const alertContainer = document.getElementById('alertContainer');
    if (alertContainer) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertContainer.appendChild(alert);
    }
}
