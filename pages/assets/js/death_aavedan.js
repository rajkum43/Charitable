// Death Aavedan Form Handler
$(document).ready(function() {
    // Get member ID from the page's data attribute or global variable
    // Since this is called from death_aavedan.php which is in pages folder
    const memberId = sessionStorage.getItem('member_id') || localStorage.getItem('member_id') || '';

    // If member ID is not available, check if it's in the hidden form field
    let memberIdInput = document.querySelector('input[name="member_id"]');
    if (memberIdInput && memberIdInput.value) {
        // Member ID is available
        // Auto-fill deceased member ID with the logged-in member ID
        const deceasedMemberId = document.getElementById('deceased_member_id');
        if (deceasedMemberId && !deceasedMemberId.value) {
            deceasedMemberId.value = memberIdInput.value;
        }
    } else if (!memberId) {
        alert('Error: Member ID not found. Please login again.');
        window.location.href = '/pages/login.php';
        return;
    }

    // Validation function to check all required fields
    function validateForm() {
        const requiredFields = [
            // Tab 1: User Details (auto-filled, generally safe)
            { id: 'member_name', name: 'सदस्य का नाम', tab: 'userdetails-tab' },
            
            // Tab 2: Applicant Details (New)
            { id: 'applicant_name', name: 'आवेदक का नाम', tab: 'applicant-tab' },
            { id: 'applicant_relation', name: 'संबंध (मृत व्यक्ति के साथ)', tab: 'applicant-tab', type: 'radio' },
            { id: 'applicant_parent_name', name: 'अभिभावक का नाम', tab: 'applicant-tab' },
            
            // Tab 3: Deceased Details
            { id: 'deceased_name', name: 'मृत व्यक्ति का नाम', tab: 'deceased-tab' },
            { id: 'deceased_member_id', name: 'मृत व्यक्ति का सदस्य ID', tab: 'deceased-tab' },
            { id: 'deceased_dob', name: 'मृत व्यक्ति की जन्म तिथि', tab: 'deceased-tab' },
            { id: 'deceased_age', name: 'मृत्यु के समय आयु', tab: 'deceased-tab' },
            { id: 'death_date', name: 'मृत्यु की तारीख', tab: 'deceased-tab' },
            { id: 'deceased_relationship', name: 'रिश्ता', tab: 'deceased-tab' },
            { id: 'cause_of_death', name: 'मृत्यु का कारण', tab: 'deceased-tab' },
            
            // Tab 4: Family Details
            { id: 'family_income', name: 'वार्षिक पारिवारिक आय', tab: 'family-tab' },
            { id: 'family_members', name: 'परिवार के सदस्यों की संख्या', tab: 'family-tab' },
            
            // Tab 5: Bank Details
            { id: 'ifsc_code', name: 'IFSC कोड', tab: 'bank-tab' },
            { id: 'bank_name', name: 'बैंक का नाम', tab: 'bank-tab' },
            { id: 'branch_name', name: 'शाखा का नाम', tab: 'bank-tab' },
            { id: 'account_number', name: 'खाता संख्या', tab: 'bank-tab' },
            { id: 'account_holder_name', name: 'खाता धारक का नाम', tab: 'bank-tab' },
            { id: 'confirm_details', name: 'पुष्टि चेकबॉक्स', tab: 'bank-tab', type: 'checkbox' }
        ];

        let firstErrorTab = null;
        let errorDetails = [];

        // Check each field
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            if (!element) return;

            let isValid = false;
            if (field.type === 'checkbox') {
                isValid = element.checked;
            } else if (field.type === 'select') {
                isValid = element.value && element.value.trim() !== '';
            } else if (field.type === 'radio') {
                const radioGroup = document.getElementsByName(field.id);
                isValid = Array.from(radioGroup).some(r => r.checked);
            } else {
                isValid = element.value && element.value.trim() !== '';
            }

            if (!isValid) {
                if (!firstErrorTab) {
                    firstErrorTab = field.tab;
                }
                errorDetails.push({ fieldId: field.id, fieldName: field.name, tab: field.tab });
                
                // Add error styling to the field
                element.classList.add('is-invalid');
                element.closest('.form-group')?.classList.add('has-error');
                if (field.type === 'radio') {
                    const radioGroup = document.getElementsByName(field.id);
                    radioGroup.forEach(r => {
                        r.classList.add('is-invalid');
                    });
                }
            } else {
                // Remove error styling
                element.classList.remove('is-invalid');
                element.closest('.form-group')?.classList.remove('has-error');
                if (field.type === 'radio') {
                    const radioGroup = document.getElementsByName(field.id);
                    radioGroup.forEach(r => {
                        r.classList.remove('is-invalid');
                    });
                }
            }
        });

        if (errorDetails.length > 0) {
            // Switch to tab with first error
            if (firstErrorTab) {
                const tabButton = document.getElementById(firstErrorTab);
                if (tabButton) {
                    const tab = new bootstrap.Tab(tabButton);
                    tab.show();
                    
                    // Scroll to the first error field
                    setTimeout(() => {
                        const firstErrorField = document.getElementById(errorDetails[0].fieldId);
                        if (firstErrorField) {
                            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstErrorField.focus();
                        }
                    }, 300);
                }
            }

            // Show error message
            let errorMsg = 'कृपया निम्नलिखित आवश्यक फील्ड भरें:\n\n';
            errorDetails.forEach(err => {
                errorMsg += '• ' + err.fieldName + '\n';
            });
            alert(errorMsg);
            return false;
        }

        return true;
    }

    $('#death-aavedan-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form before submission
        if (!validateForm()) {
            return;
        }
        
        const submitBtn = $('#submit-btn');
        const loader = $('#loader');
        const actualMemberId = memberIdInput ? memberIdInput.value : memberId;
        
        const formData = {
            action: 'submit_application',
            member_id: actualMemberId,
            type: 'death',
            applicant_name: $('#applicant_name').val(),
            applicant_dob: $('#applicant_dob').val(),
            applicant_relation: $('input[name="applicant_relation"]:checked').val(),
            applicant_parent_name: $('#applicant_parent_name').val(),
            deceased_name: $('#deceased_name').val(),
            deceased_member_id: $('#deceased_member_id').val(),
            deceased_dob: $('#deceased_dob').val(),
            deceased_age: $('#deceased_age').val(),
            death_date: $('#death_date').val(),
            deceased_relationship: $('#deceased_relationship').val(),
            cause_of_death: $('#cause_of_death').val(),
            family_income: $('#family_income').val(),
            family_members: $('#family_members').val(),
            bank_name: $('#bank_name').val(),
            branch_name: $('#branch_name').val(),
            account_number: $('#account_number').val(),
            ifsc_code: $('#ifsc_code').val().toUpperCase(),
            account_holder_name: $('#account_holder_name').val(),
            upi_id: $('#upi_id').val(),
            remarks: $('#remarks').val()
        };

        submitBtn.disabled = true;
        loader.show();

        $.ajax({
            url: '../api/death_aavedan.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('आपका आवेदन सफलतापूर्वक जमा हो गया!');
                    $('#death-aavedan-form')[0].reset();
                    setTimeout(() => {
                        window.location.href = '/pages/application_status.php';
                    }, 1500);
                } else {
                    alert('त्रुटि: ' + response.message);
                    submitBtn.disabled = false;
                    loader.hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                alert('त्रुटि: आवेदन जमा नहीं हो सका। कृपया पुनः प्रयास करें।');
                submitBtn.disabled = false;
                loader.hide();
            }
        });
    });

    // Date validation - don't allow future dates for death date
    const today = new Date().toISOString().split('T')[0];
    $('#death_date').attr('max', today);

    // Optional: Remove error styling when user starts typing
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.closest('.form-group')?.classList.remove('has-error');
        });
        field.addEventListener('change', function() {
            this.classList.remove('is-invalid');
            this.closest('.form-group')?.classList.remove('has-error');
        });
    });
});
