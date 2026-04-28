// Admin Poll Applications JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    let currentApplicationId = null;

    // View application details
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentApplicationId = this.dataset.appId;
            fetchApplicationDetails(currentApplicationId);
        });
    });

    // Approve application
    approveBtn.addEventListener('click', function() {
        if (confirm('क्या आप इस आवेदन को अनुमोदित करना चाहते हैं?')) {
            approveApplication(currentApplicationId);
        }
    });

    // Reject application
    rejectBtn.addEventListener('click', function() {
        const reason = prompt('अस्वीकार करने का कारण दर्ज करें:');
        if (reason !== null) {
            rejectApplication(currentApplicationId, reason);
        }
    });

    // Fetch application details
    function fetchApplicationDetails(appId) {
        const formData = new FormData();
        formData.append('action', 'get_application');
        formData.append('app_id', appId);

        fetch('../api/admin_poll_approval.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplicationDetails(data.data);
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('आवेदन लोड करने में विफल');
        });
    }

    // Display application details in modal
    function displayApplicationDetails(app) {
        const detailsContainer = document.getElementById('applicationDetails');
        const typeLabel = app.type === 'vivah' ? 'विवाह सहायता' : 'मृत्यु लाभ';

        detailsContainer.innerHTML = `
            <div class="details-content">
                <div class="detail-row">
                    <div class="detail-label">आवेदन ID:</div>
                    <div class="detail-value">#${app.id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">सदस्य नाम:</div>
                    <div class="detail-value">${app.full_name}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">सदस्य ID:</div>
                    <div class="detail-value">${app.member_id}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">आधार नंबर:</div>
                    <div class="detail-value">${app.aadhar_number}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">मोबाइल नंबर:</div>
                    <div class="detail-value">${app.mobile_number}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">आवेदन प्रकार:</div>
                    <div class="detail-value"><span class="badge bg-primary">${typeLabel}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">आवेदन तिथि:</div>
                    <div class="detail-value">${formatDate(app.created_at)}</div>
                </div>
                ${app.application_details ? `
                <div class="detail-row">
                    <div class="detail-label">विवरण:</div>
                    <div class="detail-value">${app.application_details}</div>
                </div>
                ` : ''}
            </div>
        `;
    }

    // Approve application
    function approveApplication(appId) {
        const formData = new FormData();
        formData.append('action', 'approve_application');
        formData.append('app_id', appId);

        fetch('../api/admin_poll_approval.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ आवेदन अनुमोदित किया गया! पोल बनाया गया: ' + data.data.poll_id);
                location.reload();
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('अनुमोदन में विफल');
        });
    }

    // Reject application
    function rejectApplication(appId, reason) {
        const formData = new FormData();
        formData.append('action', 'reject_application');
        formData.append('app_id', appId);
        formData.append('reason', reason);

        fetch('../api/admin_poll_approval.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ आवेदन अस्वीकार किया गया');
                location.reload();
            } else {
                alert('त्रुटि: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('अस्वीकार करने में विफल');
        });
    }

    // Helper function to format date
    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('hi-IN', options);
    }
});
