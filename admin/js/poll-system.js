// Poll System JavaScript

let pollData = [];
let selectedRecords = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPollData();
    setupEventListeners();
    checkPublishButtonState();
    
    // Check publish button state every minute
    setInterval(checkPublishButtonState, 60000);
});

/**
 * Setup event listeners for interactive elements
 */
function setupEventListeners() {
    // Filter buttons
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filterType = this.dataset.filter || 'all';
            filterData(filterType);
        });
    });

    // Publish button
    const publishBtn = document.getElementById('publishBtn');
    if (publishBtn) {
        publishBtn.addEventListener('click', handlePublish);
    }

    // Confirm publish button in modal
    const confirmPublishBtn = document.getElementById('confirmPublishBtn');
    if (confirmPublishBtn) {
        confirmPublishBtn.addEventListener('click', proceedWithPublish);
    }

    // Clear selection button
    const clearBtn = document.getElementById('clearSelectionBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearSelection);
    }

    // Export button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportData);
    }
}

/**
 * Load poll data from API
 */
async function loadPollData() {
    try {
        showLoadingState(true);

        const response = await fetch(window.API_URL + 'fetch_poll_data.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server did not return JSON. Response is not valid API response.');
        }

        const data = await response.json();

        if (data.success) {
            pollData = data.data || [];
            displayPollTable(pollData);
            updateTotalCount();
        } else {
            showStatusMessage(data.message || 'Failed to load poll data', 'error');
        }
    } catch (error) {
        console.error('Poll data loading error:', error);
        showStatusMessage('Error loading poll data: ' + error.message, 'error');
    } finally {
        showLoadingState(false);
    }
}

/**
 * Display poll data in table
 */
function displayPollTable(data) {
    const tbody = document.querySelector('#pollTable tbody');
    
    if (!tbody) return;

    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="fas fa-folder-open" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mt-2">कोई डेटा उपलब्ध नहीं है</p>
                </td>
            </tr>
        `;
        return;
    }

    data.forEach((record, index) => {
        const row = document.createElement('tr');
        // Ensure record has 'type' property for consistency
        const recordType = record.application_type || record.type;
        const recordWithType = { ...record, type: recordType };
        
        const isSelected = selectedRecords.some(r => r.id === record.id && r.type === recordType);
        const pollLetter = isSelected ? getPollLetterForRecord(recordWithType) : '';

        const badgeClass = recordType === 'Death_Claims' ? 'badge-death' : 'badge-vivah';
        const badgeText = recordType === 'Death_Claims' ? 'मृत्यु लाभ आवेदन' : 'बेटी विवाह';

        row.className = isSelected ? 'selected' : '';
        row.innerHTML = `
            <td class="text-nowrap-custom">${index + 1}</td>
            <td class="text-nowrap-custom">${record.claim_number || record.id}</td>
            <td class="text-nowrap-custom">${record.user_name || 'N/A'}</td>
            <td>
                <span class="badge ${badgeClass}">${badgeText}</span>
            </td>
            <td class="text-center">
                <input type="checkbox" class="form-check-input record-checkbox" data-id="${record.id}" data-type="${recordType}" data-name="${record.user_name || ''}" ${isSelected ? 'checked' : ''}>
            </td>
            <td class="text-center">
                <div class="poll-letter ${pollLetter ? '' : 'empty'}">
                    ${pollLetter || '-'}
                </div>
            </td>
        `;

        const checkbox = row.querySelector('.record-checkbox');
        checkbox.addEventListener('change', function() {
            toggleCheckbox(this, record);
        });

        tbody.appendChild(row);
    });
}

/**
 * Toggle checkbox and manage poll assignments
 */
function toggleCheckbox(checkboxElement, record) {
    const recordType = record.application_type || record.type;
    
    if (checkboxElement.checked) {
        // Add record to selection
        if (!selectedRecords.some(r => r.id === record.id && r.type === recordType)) {
            selectedRecords.push({
                id: record.id,
                claim_number: record.claim_number,
                user_id: record.user_id,
                user_name: record.user_name,
                type: recordType,
                db_id: record.db_id
            });
        }
    } else {
        // Remove record from selection
        selectedRecords = selectedRecords.filter(r => !(r.id === record.id && r.type === recordType));
    }

    // Update display - create a record object with 'type' property for getPollLetterForRecord
    const recordForLetter = {
        ...record,
        type: recordType
    };
    
    const row = checkboxElement.closest('tr');
    const pollLetterDiv = row.querySelector('.poll-letter');
    const pollLetter = getPollLetterForRecord(recordForLetter);

    if (pollLetter) {
        pollLetterDiv.textContent = pollLetter;
        pollLetterDiv.classList.remove('empty');
    } else {
        pollLetterDiv.textContent = '-';
        pollLetterDiv.classList.add('empty');
    }

    if (checkboxElement.checked) {
        row.classList.add('selected');
    } else {
        row.classList.remove('selected');
    }

    updateSelectedCount();
}

/**
 * Get poll letter for record based on selection order
 */
function getPollLetterForRecord(record) {
    const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];
    const index = selectedRecords.findIndex(r => r.id === record.id && r.type === record.type);
    return index >= 0 && index < letters.length ? letters[index] : '';
}

/**
 * Filter poll data by type
 */
function filterData(filterType) {
    let filtered = pollData;

    if (filterType === 'death') {
        filtered = pollData.filter(r => r.application_type === 'Death_Claims');
    } else if (filterType === 'vivah') {
        filtered = pollData.filter(r => r.application_type === 'Beti_Vivah');
    }

    displayPollTable(filtered);
}

/**
 * Clear all selections
 */
function clearSelection() {
    selectedRecords = [];
    document.querySelectorAll('.record-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    displayPollTable(pollData);
    updateSelectedCount();
}

/**
 * Update total count display
 */
function updateTotalCount() {
    const totalCountEl = document.getElementById('totalCount');
    if (totalCountEl) {
        totalCountEl.textContent = pollData.length;
    }
}

/**
 * Update selected count display
 */
function updateSelectedCount() {
    const selectedCountEl = document.getElementById('selectedCount');
    if (selectedCountEl) {
        selectedCountEl.textContent = selectedRecords.length;
    }

    // Enable/disable publish button based on selection
    const publishBtn = document.getElementById('publishBtn');
    if (publishBtn) {
        publishBtn.disabled = selectedRecords.length === 0 || isPublishButtonDisabledDate();
    }
}

/**
 * Check if today is between 11-20 (publish button disabled dates)
 * DISABLED FOR TESTING - Remove this comment to enable date restriction
 */
function isPublishButtonDisabledDate() {
    const today = new Date().getDate();
    // Date restriction disabled for testing
    // return today >= 11 && today <= 20;
    return false; // Allow publishing on all days for now
}

/**
 * Check and update publish button state
 */
function checkPublishButtonState() {
    const publishBtn = document.getElementById('publishBtn');
    const publishWarning = document.getElementById('publishWarning');

    if (!publishBtn) return;

    const isDisabledDate = isPublishButtonDisabledDate();
    const hasSelection = selectedRecords.length > 0;

    publishBtn.disabled = !hasSelection; // Only disable if no selection, ignore date for testing
    
    // Hide warning message (date restriction disabled for testing)
    if (publishWarning) {
        publishWarning.style.display = 'none';
    }
}

/**
 * Handle publish action
 */
async function handlePublish() {
    if (selectedRecords.length === 0) {
        showStatusMessage('कृपया कम से कम एक रिकॉर्ड चुनें', 'warning');
        return;
    }

    // Date restriction disabled for testing
    // if (isPublishButtonDisabledDate()) {
    //     showStatusMessage('प्रकाशन महीने के 11वें से 20वें दिन के बीच अनुमति नहीं है', 'warning');
    //     return;
    // }

    // Show confirmation modal with distribution details
    showPublishConfirmationModal();
}

/**
 * Calculate and show member distribution in confirmation modal
 */
function showPublishConfirmationModal() {
    // Get poll letters for selected records
    const pollLetters = selectedRecords.map((record, index) => {
        const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];
        return letters[index] || '';
    }).filter(l => l);

    // Use TOTAL_MEMBERS from PHP, or use a safe fallback
    let totalMembers = window.TOTAL_MEMBERS;
    if (!totalMembers || totalMembers === undefined || totalMembers === null) {
        console.warn('TOTAL_MEMBERS not found, using default 955');
        totalMembers = 955;
    }
    
    const pollCount = pollLetters.length;

    // Debug logging
    console.log('=== Poll Distribution Calculation ===');
    console.log('Total Members (from window.TOTAL_MEMBERS):', window.TOTAL_MEMBERS);
    console.log('Total Members (to use):', totalMembers);
    console.log('Poll Count:', pollCount);
    console.log('Base Allocation:', Math.floor(totalMembers / pollCount));
    console.log('Extra Members:', totalMembers % pollCount);

    // Calculate distribution
    const baseAllocation = Math.floor(totalMembers / pollCount);
    const extraMembers = totalMembers % pollCount;

    // Create distribution mapping
    const distribution = [];
    for (let i = 0; i < pollLetters.length; i++) {
        let count = baseAllocation;
        if (i < extraMembers) {
            count += 1;  // Distribute extra members to first options
        }
        distribution.push({
            letter: pollLetters[i],
            count: count
        });
    }

    console.log('Final Distribution:', distribution);
    console.log('===================================');

    // Update modal with record count
    document.getElementById('recordCountText').textContent = selectedRecords.length;

    // Populate distribution table
    const tableBody = document.getElementById('distributionTableBody');
    tableBody.innerHTML = '';

    distribution.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">
                <span class="badge bg-primary" style="font-size: 1.2rem; padding: 0.5rem 0.75rem;">
                    ${item.letter}
                </span>
            </td>
            <td class="text-center">
                <strong style="font-size: 1.1rem;">${item.count}</strong>
            </td>
        `;
        tableBody.appendChild(row);
    });

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('publishConfirmModal'));
    modal.show();
}

/**
 * Confirm and proceed with publishing
 */
async function proceedWithPublish() {
    try {
        showLoadingState(true);
        showStatusMessage('पोल प्रकाशित किया जा रहा है...', 'info');

        const pollData = selectedRecords.map((record, index) => ({
            claim_number: record.claim_number,
            user_id: record.user_id,
            poll: getPollLetterForRecord(record),
            application_type: record.type,
            db_id: record.db_id
        }));


        
        const response = await fetch(window.API_URL + 'publish_poll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                polls: pollData
            })
        });

        // Check if response is OK
        if (!response.ok) {
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }

        // Check response MIME type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Invalid response from server (not JSON)');
        }

        const result = await response.json();

        if (result.success) {
            showStatusMessage(`✓ पोल सफलतापूर्वक प्रकाशित हो गया!\n${result.message}`, 'success');
            closePublishConfirmationModal();
            clearSelection();
            
            // Automatically distribute members across published polls
            await distributeMembersAcrossPolls();
            
            loadPollData();
        } else {
            showStatusMessage(result.message || 'पोल प्रकाशन विफल', 'error');
        }
    } catch (error) {
        showStatusMessage('Error: ' + error.message, 'error');
    } finally {
        showLoadingState(false);
    }
}

/**
 * Close confirmation modal
 */
function closePublishConfirmationModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('publishConfirmModal'));
    if (modal) {
        modal.hide();
    }
}

/**
 * Distribute members across published polls
 */
async function distributeMembersAcrossPolls() {
    try {
        
        showStatusMessage('सदस्यों को पोल विकल्पों में वितरित किया जा रहा है...', 'info');
        
        const response = await fetch(window.API_URL + 'distribute_poll_to_members.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        });
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Invalid response from server (not JSON)');
        }
        
        const result = await response.json();
        
        if (result.success) {
            const pollsUsed = result.summary.poll_options_used || 'A-E';
            const membersUpdated = result.summary.total_members_updated;
            const totalMembers = result.summary.total_members;
            
            showStatusMessage(
                `✓ सभी ${totalMembers} सदस्यों को ${pollsUsed} पोल विकल्पों में वितरित किया गया!\\n${membersUpdated} सदस्य अपडेट किए गए।`,
                'success'
            );
        } else {
            // Don't show error if no polls are found yet (it's expected on first publish)
            if (!result.message.includes('No published polls')) {
                showStatusMessage(result.message || 'सदस्य वितरण विफल', 'warning');
            }
        }
    } catch (error) {
        // Don't fail the entire publish process if distribution has issues
    }
}

/**
 * Export data to CSV
 */
function exportData() {
    if (selectedRecords.length === 0) {
        showStatusMessage('निर्यात करने के लिए कम से कम एक रिकॉर्ड चुनें', 'warning');
        return;
    }

    const csv = [['रिकॉर्ड क्रमांक', 'दावा संख्या', 'सदस्य नाम', 'प्रकार', 'पोल अक्षर']];

    selectedRecords.forEach((record, index) => {
        csv.push([
            index + 1,
            record.claim_number,
            record.user_name,
            record.type === 'Death_Claims' ? 'मृत्यु लाभ आवेदन' : 'बेटी विवाह',
            getPollLetterForRecord(record)
        ]);
    });

    downloadCSV(csv, 'poll_data.csv');
}

/**
 * Download CSV file
 */
function downloadCSV(data, filename) {
    const csvContent = data.map(row =>
        row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')
    ).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showStatusMessage('डेटा सफलतापूर्वक निर्यात किया गया', 'success');
}

/**
 * Show/hide loading state
 */
function showLoadingState(isLoading) {
    const publishBtn = document.getElementById('publishBtn');
    if (publishBtn) {
        if (isLoading) {
            publishBtn.disabled = true;
            publishBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
        } else {
            publishBtn.disabled = selectedRecords.length === 0 || isPublishButtonDisabledDate();
            publishBtn.textContent = 'प्रकाशित करें';
        }
    }
}

/**
 * Show status message
 */
function showStatusMessage(message, type = 'info') {
    let statusEl = document.getElementById('statusMessage');

    if (!statusEl) {
        statusEl = document.createElement('div');
        statusEl.id = 'statusMessage';
        statusEl.className = 'status-message';
        const container = document.querySelector('.poll-wrapper') || document.body;
        container.insertBefore(statusEl, container.firstChild);
    }

    statusEl.className = `status-message alert alert-${type} show`;
    statusEl.textContent = message;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        statusEl.classList.remove('show');
    }, 5000);
}

// Export functions for external use
window.pollSystem = {
    loadPollData,
    toggleCheckbox,
    filterData,
    clearSelection,
    handlePublish,
    exportData,
    distributeMembersAcrossPolls
};
