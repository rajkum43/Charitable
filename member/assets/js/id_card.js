let memberData = null;
let currentPhotoDataURL = null;
const defaultPhotoSrc = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="%23e2e8f0"><circle cx="50" cy="45" r="28" fill="%23b9d2f0"/><path d="M20 80 Q50 65 80 80" stroke="%2380b3ff" stroke-width="6" fill="none" stroke-linecap="round"/><circle cx="35" cy="40" r="4" fill="%23333333"/><circle cx="65" cy="40" r="4" fill="%23333333"/></svg>';

// Enhanced load data
async function loadMemberData() {
    const loadingDiv = document.getElementById('loading');
    const container = document.getElementById('idCardContainer');
    const downloadBtn = document.getElementById('downloadBtn');
    
    try {
        // Simulate API call (fetch actual endpoint)
        const response = await fetch('api/get_member_data.php');
        const data = await response.json();

        if (data.success && data.data) {
            memberData = data.data;
            fillCardData(memberData);
            
            const defaultPhoto = document.getElementById('memberPhoto');
            if (!defaultPhoto.src || defaultPhoto.src === window.location.href || defaultPhoto.src === "") {
                defaultPhoto.src = defaultPhotoSrc;
                defaultPhoto.style.display = 'block';
            }
            
            loadingDiv.style.display = 'none';
            container.style.display = 'block';
            downloadBtn.disabled = false;
        } else {
            throw new Error(data.message || 'डेटा नहीं मिला');
        }
    } catch (error) {
        console.error(error);
        loadingDiv.innerHTML = `<div class="alert alert-danger rounded-pill shadow-sm">⚠️ डेटा लोड विफल! कृपया बाद में प्रयास करें।<br><small>${error.message}</small></div>`;
        loadingDiv.classList.add('p-4');
    }
}

/* Fill All Information */
function fillCardData(data) {
    document.getElementById('memberId').innerHTML = data.member_id || 'N/A';
    document.getElementById('memberName').innerHTML = data.full_name || '----';
    document.getElementById('fatherHusband').innerHTML = data.father_husband_name || '----';
    document.getElementById('gender').innerHTML = formatGender(data.gender);
    document.getElementById('mobile').innerHTML = data.mobile_number || '----';
    document.getElementById('joiningDate').innerHTML = formatDate(data.created_at) || '--/--/----';
    document.getElementById('address').innerHTML = buildFullAddress(data);
}

function buildFullAddress(data) {
    const parts = [];
    if (data.permanent_address) parts.push(data.permanent_address.trim());
    if (data.block) parts.push(data.block.trim());
    if (data.district) parts.push(data.district.trim());
    return parts.length ? parts.join(', ') : 'पता उपलब्ध नहीं है';
}

function formatGender(gender) {
    if (!gender) return '----';
    if (gender.toLowerCase() === 'male') return 'पुरुष (Male)';
    if (gender.toLowerCase() === 'female') return 'महिला (Female)';
    if (gender.toLowerCase() === 'other') return 'अन्य (Other)';
    return gender;
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('hi-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch(e) {
        return dateStr;
    }
}

/* Upload Photo with live preview + quality enhancement */
document.getElementById('memberImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && (file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg')) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const imgElement = document.getElementById('memberPhoto');
            imgElement.src = ev.target.result;
            imgElement.style.display = 'block';
            currentPhotoDataURL = ev.target.result;
            // add subtle flash effect
            const photoBox = document.querySelector('.photo-frame');
            if (photoBox) {
                photoBox.style.transition = '0.2s';
                photoBox.style.filter = 'drop-shadow(0 0 3px #0d6efd)';
                setTimeout(() => photoBox.style.filter = '', 400);
            }
        };
        reader.readAsDataURL(file);
    } else if(file) {
        alert('कृपया केवल JPEG या PNG फोटो चुनें');
    }
});

/* Download ID Card with Enhanced Quality and edge smoothing */
document.getElementById('downloadBtn').addEventListener('click', function() {
    const cardElement = document.getElementById('idCard');
    const originalOverflow = cardElement.style.overflow;
    cardElement.style.overflow = 'visible';
    
    // Show loading indicator on button
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '⏳ कार्ड तैयार हो रहा है...';
    btn.disabled = true;
    
    // Force any lazy images to be ready (ensuring external)
    const images = cardElement.querySelectorAll('img');
    let loadedCount = 0;
    const totalImages = images.length;
    
    function tryCapture() {
        html2canvas(cardElement, {
            scale: 3.5,
            backgroundColor: '#ffffff',
            useCORS: true,
            logging: false,
            allowTaint: false,
            imageTimeout: 15000,
            width: cardElement.offsetWidth,
            height: cardElement.offsetHeight
        }).then(canvas => {
            // Convert to high quality jpeg
            const link = document.createElement('a');
            link.download = `member_id_${memberData?.member_id || 'card'}.jpg`;
            link.href = canvas.toDataURL('image/jpeg', 0.98);
            link.click();
            
            // Restore
            cardElement.style.overflow = originalOverflow;
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            // Sweet little notification
            const toastMsg = document.createElement('div');
            toastMsg.innerText = '✅ कार्ड डाउनलोड हो गया!';
            toastMsg.style.position = 'fixed';
            toastMsg.style.bottom = '20px';
            toastMsg.style.left = '50%';
            toastMsg.style.transform = 'translateX(-50%)';
            toastMsg.style.backgroundColor = '#0d6efd';
            toastMsg.style.color = 'white';
            toastMsg.style.padding = '10px 25px';
            toastMsg.style.borderRadius = '50px';
            toastMsg.style.fontWeight = 'bold';
            toastMsg.style.zIndex = '9999';
            toastMsg.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
            document.body.appendChild(toastMsg);
            setTimeout(() => toastMsg.remove(), 2500);
        }).catch(error => {
            console.error('html2canvas error', error);
            alert('कार्ड डाउनलोड में त्रुटि, पुन: प्रयास करें');
            cardElement.style.overflow = originalOverflow;
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
    
    // if there are images which might be loading cross-origin, wait for them
    if (totalImages === 0) {
        tryCapture();
    } else {
        let ready = 0;
        images.forEach(img => {
            if (img.complete) {
                ready++;
            } else {
                img.addEventListener('load', () => {
                    ready++;
                    if (ready === totalImages) tryCapture();
                });
                img.addEventListener('error', () => {
                    ready++;
                    if (ready === totalImages) tryCapture();
                });
            }
        });
        if (ready === totalImages) tryCapture();
    }
});

// manual gender & default style for empty photo
// Also handle if photo fails to load 
window.addEventListener('load', function() {
    loadMemberData();
    const img = document.getElementById('memberPhoto');
    if (img) {
        if (!img.src || img.src === window.location.href || img.src === '') {
            img.src = defaultPhotoSrc;
            img.style.display = 'block';
        }
        img.onerror = function() {
            this.src = defaultPhotoSrc;
            this.style.display = 'block';
        };
    }
});

// Add decorative background circles
const styleInject = document.createElement('style');
styleInject.textContent = `
    .id-card:after {
        content: '';
        position: absolute;
        bottom: -20px;
        right: -20px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(13,110,253,0.05), transparent);
        border-radius: 50%;
        z-index: 0;
        pointer-events: none;
    }
    .id-card .info-box .info-row:last-child {
        border-bottom: none;
    }
    .member-photo {
        transition: all 0.2s ease;
    }
`;
document.head.appendChild(styleInject);