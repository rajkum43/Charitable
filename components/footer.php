<footer class="footer bg-dark text-white pt-5">
    <?php 
    // Determine the base path dynamically based on server
    // Check if we're on localhost or production server
    $host = $_SERVER['HTTP_HOST'];
    
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        // Localhost: /Charitable/
        $root_path = '/Charitable/';
    } else {
        // Production server: /
        $root_path = '/';
    }
    ?>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h4 class="mb-4">BRCT Bharat Trust</h4>
                <p>समाज के उत्थान के लिए समर्पित एक गैर-लाभकारी संस्था। हम जरूरतमंदों को सहायता प्रदान करते हैं और एक मजबूत सामाजिक नेटवर्क का निर्माण करते हैं।</p>
                <div class="social-links mt-4">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in fa-lg"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h4 class="mb-4">Quick Links</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo $root_path; ?>index.php" class="text-white-50 text-decoration-none" style="cursor: pointer; pointer-events: auto; display: inline-block;">Home</a></li>
                    <li class="mb-2"><a href="<?php echo $root_path; ?>pages/about.php" class="text-white-50 text-decoration-none" style="cursor: pointer; pointer-events: auto; display: inline-block;">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo $root_path; ?>pages/niyamavali.php" class="text-white-50 text-decoration-none" style="cursor: pointer; pointer-events: auto; display: inline-block;">Niyamawali</a></li>
                    <li class="mb-2"><a href="<?php echo $root_path; ?>pages/members-directory.php" class="text-white-50 text-decoration-none" style="cursor: pointer; pointer-events: auto; display: inline-block;">Member List</a></li>
                    <li class="mb-2"><a href="<?php echo $root_path; ?>pages/contact.php" class="text-white-50 text-decoration-none" style="cursor: pointer; pointer-events: auto; display: inline-block;">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4">Our Services</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Beti Vivah Sahyog</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Death Sahyog</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Medical Assistance</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Education Support</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h4 class="mb-4">Contact Info</h4>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> Village Semarahana , Bhathiya Bazar,Post Uska Bazar,Tahasil Naugarh Siddharth Nagar Uttar Pradesh 272208</li>
                    <li class="mb-3"><i class="fas fa-phone me-2"></i> +91 99363 85189</li>
                    <li class="mb-3"><i class="fas fa-envelope me-2"></i>brctbharat@gmail.com</li>
                </ul>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <hr class="bg-secondary">
                <div class="text-center py-3">
                    <p class="mb-0">© 2026 BRCT Bharat Trust. All Rights Reserved. | <a href="https://ssvtechmitra.com/contact.php" target="_blank" class="text-white-50 text-decoration-none" style="cursor: pointer;">Developed by SSVTechMitra Private Limited</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>
  <p class="mb-0">© 2026 BRCT Bharat Trust. All Rights Reserved. | <a href="https://ssvtechmitra.com/contact.php" target="_blank" class="text-dark-50 text-decoration-none" style="cursor: pointer;">Developed by SSVTechMitra Private Limited</a></p>