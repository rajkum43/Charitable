/**
 * Poll Expiry Manager - JavaScript
 * Handles search, update, and delete operations
 */

const BASE_URL = window.appConfig?.baseUrl || '/Charitable';
let allPolls = [];

/**
 * Initialize the page
 */
document.addEventListener('DOMContentLoaded', function() {
    loadInitialPolls();
    // Don't render table on load since it's already rendered by PHP
    // Just attach event listeners to existing elements
    attachEventListeners();
    setupSearchListeners();
});

/**
 * Load initial polls from data attribute
 */
function loadInitialPolls() {
    const pollsData = document.getElementById('polls-table-body')?.getAttribute('data-polls');
    if (pollsData) {
        try {
            allPolls = JSON.parse(pollsData);
        } catch (e) {
            console.error('Error parsing polls data:', e);
            allPolls = [];
        }
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'success') {
    const iconMap = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${iconMap[type] || 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.getElementById('alert-container').innerHTML = alertHtml;
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Render table rows dynamically
 */
function renderTable(polls) {
    const tableBody = document.getElementById('polls-table-body');
    const resultsInfo = document.getElementById('results-info');
    const resultCount = document.getElementById('result-count');

    if (polls.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                    कोई record नहीं मिला। कृपया दूसरा Claim Number दर्ज करें।
                </td>
            </tr>
        `;
        resultsInfo.style.display = 'none';
        return;
    }

    resultsInfo.style.display = 'block';
    resultCount.textContent = polls.length;

    let html = '';
    polls.forEach(poll => {
        const startDate = poll.start_poll_date && poll.start_poll_date !== '0000-00-00' ? poll.start_poll_date : '';
        const expireDate = poll.expire_poll_date;
        const appType = poll.application_type === 'Death_Claims' 
            ? '<span class="badge badge-death">मृत्यु सहयोग</span>' 
            : '<span class="badge badge-vivah">बेटी विवाह</span>';
        
        const createdDate = new Date(poll.created_at).toLocaleDateString('en-IN', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        html += `
            <tr class="poll-row" data-poll-id="${poll.id}">
                <td><small class="text-muted">#${poll.id}</small></td>
                <td>
                    <code class="bg-light p-1">${poll.claim_number}</code>
                </td>
                <td>
                    ${appType}
                </td>
                <td>
                    <span class="badge bg-info">${poll.poll}</span>
                </td>
                <td>
                    <span class="badge bg-warning">${poll.alert}</span>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control date-input start-date-input" 
                            data-poll-id="${poll.id}"
                            value="${startDate}">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control date-input expire-date-input" 
                            data-poll-id="${poll.id}"
                            value="${expireDate}">
                    </div>
                </td>
                <td>
                    <small class="text-muted">${createdDate}</small>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-success update-btn" 
                            data-poll-id="${poll.id}" type="button">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" 
                            data-poll-id="${poll.id}" type="button">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    tableBody.innerHTML = html;

    // Attach event listeners to newly rendered elements
    attachEventListeners();
}

/**
 * Attach event listeners to buttons
 */
function attachEventListeners() {
    document.querySelectorAll('.update-btn').forEach(btn => {
        btn.removeEventListener('click', updatePoll);
        btn.addEventListener('click', updatePoll);
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.removeEventListener('click', deletePoll);
        btn.addEventListener('click', deletePoll);
    });
}

/**
 * Update poll dates
 */
async function updatePoll() {
    const pollId = this.dataset.pollId;
    const row = document.querySelector(`tr[data-poll-id="${pollId}"]`);
    const startDate = row.querySelector('.start-date-input').value;
    const expireDate = row.querySelector('.expire-date-input').value;

    if (!startDate || !expireDate) {
        showAlert('कृपया दोनों dates (Start Date और Expiry Date) भरें।', 'warning');
        return;
    }

    // Show loading state
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', pollId);
        formData.append('start_poll_date', startDate);
        formData.append('expire_poll_date', expireDate);

        const response = await fetch(`${BASE_URL}/admin/api/poll-api.php`, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error. Response:', text);
            showAlert('Server error occurred. Please check console for details.', 'danger');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save"></i> Update';
            return;
        }

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'danger');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save"></i> Update';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('एक त्रुटि हुई। कृपया पुनः प्रयास करें।', 'danger');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-save"></i> Update';
    }
}

/**
 * Delete poll
 */
async function deletePoll() {
    if (!confirm('क्या आप इस poll को delete करना चाहते हैं? यह action पूर्ववत नहीं किया जा सकता।')) {
        return;
    }

    const pollId = this.dataset.pollId;

    // Show loading state
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', pollId);

        const response = await fetch(`${BASE_URL}/admin/api/poll-api.php`, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error. Response:', text);
            showAlert('Server error occurred. Please check console for details.', 'danger');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-trash"></i> Delete';
            return;
        }

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'danger');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('एक त्रुटि हुई। कृपया पुनः प्रयास करें।', 'danger');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
}

/**
 * Search polls by claim number
 */
document.getElementById('search-btn')?.addEventListener('click', async function() {
    const claimNumber = document.getElementById('search-claim-number').value.trim();
    
    if (!claimNumber) {
        showAlert('कृपया Claim Number दर्ज करें।', 'warning');
        return;
    }

    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';

    try {
        const formData = new FormData();
        formData.append('action', 'search');
        formData.append('claim_number', claimNumber);

        const response = await fetch(`${BASE_URL}/admin/api/poll-api.php`, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        let data;
        
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON Parse Error. Response:', text);
            showAlert('Server error occurred. Please check console for details.', 'danger');
            this.disabled = false;
            this.innerHTML = originalText;
            return;
        }

        if (data.success) {
            renderTable(data.polls);
            if (data.count === 0) {
                showAlert(`"${claimNumber}" के लिए कोई record नहीं मिला।`, 'info');
            } else {
                showAlert(`${data.count} record(s) मिले।`, 'success');
            }
        } else {
            showAlert(data.message, 'warning');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Search में त्रुटि हुई। कृपया पुनः प्रयास करें।', 'danger');
    } finally {
        this.disabled = false;
        this.innerHTML = originalText;
    }
});

/**
 * Show all records
 */
document.getElementById('show-all-btn')?.addEventListener('click', function() {
    document.getElementById('search-claim-number').value = '';
    renderTable(allPolls);
    showAlert(`कुल ${allPolls.length} poll record(s) दिखाई दे रहे हैं।`, 'info');
});

/**
 * Allow search on Enter key
 */
document.getElementById('search-claim-number')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('search-btn').click();
    }
});

/**
 * Setup search listeners
 */
function setupSearchListeners() {
    // Already handled above
}
