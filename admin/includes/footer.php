<?php
// Footer Component for Admin Panel
?>
<footer class="admin-footer bg-dark text-light py-4 border-top">
    <div class="container-fluid px-4">
        <div class="row">
            <!-- About -->
            <div class="col-12 col-md-4 mb-3 mb-md-0">
                <h6 class="fw-bold mb-2">
                    <i class="fas fa-dharmachakra text-primary"></i> BRCT Bharat Trust
                </h6>
                <p class="small text-secondary mb-0">
                    Admin Control Panel for managing charitable trust content and operations.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-12 col-md-4 mb-3 mb-md-0">
                <h6 class="fw-bold mb-2">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="index.php" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li><a href="slider-manager.php" class="text-secondary text-decoration-none">Slider Manager</a></li>
                    <li><a href="register.php" class="text-secondary text-decoration-none">Register Admin</a></li>
                </ul>
            </div>

            <!-- Info -->
            <div class="col-12 col-md-4">
                <h6 class="fw-bold mb-2">Information</h6>
                <p class="small text-secondary mb-1">
                    <i class="fas fa-envelope me-1"></i> support@brctbharat.org
                </p>
                <p class="small text-secondary mb-0">
                    <i class="fas fa-phone me-1"></i> +91-XXXX-XXXX-XX
                </p>
            </div>
        </div>

        <hr class="bg-secondary my-3">

        <!-- Bottom -->
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <p class="small text-secondary mb-0">
                    &copy; 2026 BRCT Bharat Trust. All rights reserved.
                </p>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                <small class="text-secondary">
                    Last Updated: <span id="current-time"></span>
                </small>
            </div>
        </div>
    </div>
</footer>

<script>
// Update current time in footer
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById('current-time').textContent = timeString;
}

// Update on page load and every minute
updateTime();
setInterval(updateTime, 60000);
</script>

<style>
.admin-footer {
    background: #1a202c !important;
    color: #e2e8f0;
    border-top: 1px solid #495057;
    margin-top: auto;
    flex-shrink: 0;
    position: static;
}

.admin-footer a {
    color: #0d6efd;
    text-decoration: none;
    transition: color 0.3s;
}

.admin-footer a:hover {
    color: #0d6efd !important;
    text-decoration: underline;
}

.admin-footer h6 {
    color: white;
    font-weight: 600;
}

.admin-footer .small {
    font-size: 0.875rem;
}

/* Desktop styles */
@media (min-width: 769px) {
    .admin-footer {
        margin-left: 250px;
        width: calc(100% - 250px);
        position: static;
    }
}

/* Mobile styles */
@media (max-width: 768px) {
    .admin-footer {
        margin-left: 0;
        width: 100%;
        position: static;
    }

    .admin-footer .row {
        text-align: center;
    }

    .admin-footer .col-12 {
        margin-bottom: 15px;
    }

    .admin-footer .col-12:last-child {
        margin-bottom: 0;
    }
}

/* Transition for responsive */
@media (max-width: 768px) {
    .admin-footer {
        transition: margin-left 0.3s, width 0.3s;
    }
}
</style>
