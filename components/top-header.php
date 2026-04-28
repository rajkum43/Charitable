<div class="top-header bg-primary text-white py-2">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-6 col-sm-12 text-center text-md-start mb-2 mb-md-0">
                <span class="fw-bold">BRCT Bharat Trust</span>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 text-center text-md-start mb-2 mb-md-0">
                <div class="contact-info d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                    <span><i class="fas fa-envelope me-2"></i>brctbharat@gmail.com</span>
                    <span><i class="fas fa-phone me-2"></i>+91 99363 85189</span>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 col-sm-12 text-center text-lg-end">
                <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-3">
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-youtube"></i></a>
                    </div>
                    <a href="<?php 
                        // Determine the base path dynamically
                        $host = $_SERVER['HTTP_HOST'];
                        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                            echo '/Charitable/';
                        } else {
                            echo '/';
                        }
                    ?>pages/contact.php" class="btn btn-light btn-sm fw-bold">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>