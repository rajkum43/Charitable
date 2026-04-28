<?php
// Include configuration
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRCT Bharat Trust - समाजिक सेवा के लिए एक संकल्प</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/core-team.css">
</head>
<body>

    <!-- Top Header -->
    <?php include 'components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php include 'components/navbar.php'; ?>

    <!-- Dynamic Hero Slider (Database Content) -->
    <?php include 'components/hero-slider.php'; ?>

    <!-- Trust Introduction with Floating Cards -->
    <section class="introduction-section py-5 position-relative">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0 position-relative" data-aos="fade-right">
                    <div class="image-wrapper position-relative">
                        <img src="assets/images/about/trust-building.jpg" alt="About BRCT Bharat Trust" class="img-fluid rounded-4 shadow-lg main-image">
                        <div class="floating-card bg-primary text-white p-3 rounded-3 shadow">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <!-- <h5>10+ Years</h5> -->
                            <p class="mb-0 small">सेवा में समर्पित</p>
                        </div>
                        <div class="floating-card-2 bg-white p-3 rounded-3 shadow">
                            <i class="fas fa-smile text-primary fa-2x mb-2"></i>
                            <h5>1000+</h5>
                            <p class="mb-0 small">खुश परिवार</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">हमारा परिचय</span>
                    <h2 class="section-title text-start mb-4">समाज के उत्थान के लिए <span class="text-primary">समर्पित</span></h2>
                    <p class="lead text-secondary mb-4">BRCT Bharat Trust एक गैर-लाभकारी सामाजिक संस्था है, जिसकी स्थापना 23 नवम्बर 2025 को समाज के जरूरतमंद और कमजोर वर्गों की सहायता के उद्देश्य से की गई। यह संस्था विशेष रूप से बेटियों के विवाह सहयोग और आपातकालीन सहायता जैसे सामाजिक कार्यों में सक्रिय है।</p>
                    
                    <div class="features-list">
                        <div class="feature-item d-flex mb-4">
                            <div class="feature-icon me-3">
                                <i class="fas fa-bullseye fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4>हमारा मिशन</h4>
                                <p class="mb-0">समाज के हर व्यक्ति तक सहायता पहुंचाना और एक आत्मनिर्भर समाज का निर्माण करना।</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex mb-4">
                            <div class="feature-icon me-3">
                                <i class="fas fa-eye fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4>हमारा विजन</h4>
                                <p class="mb-0">एक ऐसा समाज जहां कोई भूखा न सोए, हर बच्चा शिक्षित हो और हर परिवार सुरक्षित हो।</p>
                            </div>
                        </div>
                        <div class="feature-item d-flex">
                            <div class="feature-icon me-3">
                                <i class="fas fa-hand-holding-heart fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h4>हमारे मूल्य</h4>
                                <p class="mb-0">ईमानदारी, पारदर्शिता और समर्पण के साथ समाज सेवा।</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="pages/about.php" class="btn btn-primary">और जानें <i class="fas fa-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcement Marquee -->
    <section class="announcement-marquee py-3 bg-gradient-primary">
        <div class="container-fluid">
            <div class="marquee-wrapper">
                <div class="marquee-content">
                    <i class="fas fa-bullhorn me-2"></i>
                    <span class="me-4">🎉 15 जनवरी से बेटी विवाह सहयोग योजना का शुभारंभ - BRCT BHARAT TRUST के परिवारों को लाभ मिलेगा</span>
                    <!-- <i class="fas fa-bullhorn me-2"></i>
                    <span class="me-4">📅 20 मार्च को ट्रस्ट की वार्षिक बैठक - सभी सदस्यों से उपस्थिति का अनुरोध</span>
                    <i class="fas fa-bullhorn me-2"></i> -->
    
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics with Gradient Cards -->
    <section class="statistics-section py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="stat-card gradient-card-1 text-center p-4 rounded-4">
                        <i class="fas fa-users fa-4x text-white mb-3"></i>
                        <h2 class="counter display-4 fw-bold text-white" data-target="1000">0</h2>
                        <p class="text-white-50 mb-0">कुल सदस्य</p>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span class="text-white-50">+12% इस महीने</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-card gradient-card-2 text-center p-4 rounded-4">
                        <i class="fas fa-female fa-4x text-white mb-3"></i>
                        <h2 class="counter display-4 fw-bold text-white" data-target="2">2</h2>
                        <p class="text-white-50 mb-0">बेटी विवाह सहयोग</p>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span class="text-white-50">+8% इस महीने</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-card gradient-card-3 text-center p-4 rounded-4">
                        <i class="fas fa-heartbeat fa-4x text-white mb-3"></i>
                        <h2 class="counter display-4 fw-bold text-white" data-target="1">1</h2>
                        <p class="text-white-50 mb-0">मृत्यु सहयोग</p>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span class="text-white-50">+5% इस महीने</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="stat-card gradient-card-4 text-center p-4 rounded-4">
                        <i class="fas fa-hand-holding-usd fa-4x text-white mb-3"></i>
                        <h2 class="counter display-4 fw-bold text-white" data-target="85">2</h2>
                        <p class="text-white-50 mb-0">लाख रुपये सहायता</p>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up text-success"></i>
                            <span class="text-white-50">+15% इस महीने</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Team Section -->
    <section class="core-team-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">हमारी टीम</span>
                <h2 class="section-title">प्रदेश कोर टीम</h2>
                <p class="lead text-secondary">Meet Our Experience & Expert Team</p>
                <p class="lead text-secondary">BRCT Bharat Trust का संचालन करने वाली कुशल और समर्पित टीम</p>
            </div>
            
            <div id="coreTeamContainer" class="row g-4 justify-content-center">
                <!-- Team members will be loaded here via API -->
                <div class="text-center" style="width: 100%; ">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sahyog Services with Image Cards -->
    <section class="sahyog-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">हमारी सेवाएं</span>
                <h2 class="section-title">हम क्या करते हैं?</h2>
                <p class="lead text-secondary">हमारी प्रमुख सहयोग योजनाएं जो समाज के हर वर्ग को लाभान्वित कर रही हैं</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-5 col-md-8" data-aos="flip-up" data-aos-delay="100">
                    <div class="service-card-modern rounded-4 overflow-hidden">
                        <div class="service-image position-relative">
                            <img src="assets/images/services/beti-vivah.jpg" alt="Beti Vivah Sahyog" class="img-fluid w-100">
                            <div class="service-overlay">
                                <h3 class="text-white mb-3">बेटी विवाह सहयोग</h3>
                                <p class="text-white-50 mb-4">गरीब और जरूरतमंद परिवारों की बेटियों के विवाह में आर्थिक सहायता</p>
                                <a href="#" class="btn btn-light">और जानें <i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                        <div class="service-content p-4">
                            <h3>बेटी विवाह सहयोग</h3>
                            <p>2+ परिवारों को मिला लाभ</p>
                            <div class="progress mb-3" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: 85%"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-rupee-sign text-primary"></i> 50,000 तक सहायता</span>
                                <span class="text-primary">85% लक्ष्य</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-5 col-md-8" data-aos="flip-up" data-aos-delay="200">
                    <div class="service-card-modern rounded-4 overflow-hidden">
                        <div class="service-image position-relative">
                            <img src="assets/images/services/death-sahyog.jpg" alt="Death Sahyog" class="img-fluid w-100">
                            <div class="service-overlay">
                                <h3 class="text-white mb-3">मृत्यु सहयोग</h3>
                                <p class="text-white-50 mb-4">सदस्य के निधन पर परिवार को त्वरित आर्थिक सहायता</p>
                                <a href="#" class="btn btn-light">और जानें <i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                        <div class="service-content p-4">
                            <h3>मृत्यु सहयोग</h3>
                            <p>1+ परिवारों को सहायता</p>
                            <div class="progress mb-3" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: 92%"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-rupee-sign text-primary"></i> 50,000 तक सहायता</span>
                                <span class="text-primary">92% लक्ष्य</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Member Benefits with Hover Effects -->
    <section class="benefits-section py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">सदस्यता लाभ</span>
                <h2 class="section-title">सदस्य बनने के फायदे</h2>
                <p class="lead text-secondary">BRCT Bharat Trust के सदस्य के रूप में आपको मिलते हैं ये विशेष लाभ</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-5 col-md-8" data-aos="fade-up" data-aos-delay="100">
                    <div class="benefit-card-modern text-center p-4">
                        <div class="benefit-icon-wrapper mx-auto mb-4">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <h4>सामाजिक सुरक्षा</h4>
                        <p class="text-secondary mb-3">संकट के समय सामाजिक और आर्थिक सहयोग</p>
                        <div class="benefit-stats">
                            <span class="badge bg-success">100% कवरेज</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-5 col-md-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="benefit-card-modern text-center p-4">
                        <div class="benefit-icon-wrapper mx-auto mb-4">
                            <i class="fas fa-hand-holding-heart fa-3x text-primary"></i>
                        </div>
                        <h4>आपातकालीन सहायता</h4>
                        <p class="text-secondary mb-3">अप्रत्याशित स्थिति में त्वरित मदद</p>
                        <div class="benefit-stats">
                            <span class="badge bg-success">24x7 हेल्पलाइन</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dynamic Testimonials (Database Content) -->
    <?php include 'components/testimonials.php'; ?>

    <!-- Partners/Sponsors Section -->
    <section class="partners-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title">हमारे साझेदार</h2>
                <p class="lead text-secondary">हमारे विश्वसनीय साझेदार और प्रायोजक</p>
            </div>   
         <div class="swiper partnerSwiper" data-aos="fade-up">
    <div class="swiper-wrapper">

        <div class="swiper-slide">
            <div class="partner-logo bg-white p-4 rounded-3 text-center">
                <img src="assets/images/partners/p1.png" alt="Partner" class="img-fluid" style="max-height: 120px; margin-bottom: 15px;">
                <h5 class="fw-bold mt-3 mb-1">सत्यनारायन सम्राट</h5>
                <p class="text-muted small mb-2">मो.: 9936385189</p>
                <span class="badge bg-primary">संस्थापक</span>
            </div>
        </div>

        <div class="swiper-slide">
            <div class="partner-logo bg-white p-4 rounded-3 text-center">
                <img src="assets/images/partners/p2.png" alt="Partner" class="img-fluid" style="max-height: 120px; margin-bottom: 15px;">
                <h5 class="fw-bold mt-3 mb-1">कांति देवी</h5>
                <p class="text-muted small mb-2">मो.: 9336472638</p>
                <span class="badge bg-success">कोषाध्यक्ष</span>
            </div>
        </div>

        <div class="swiper-slide">
            <div class="partner-logo bg-white p-4 rounded-3 text-center">
                <img src="assets/images/partners/p3.png" alt="Partner" class="img-fluid" style="max-height: 120px; margin-bottom: 15px;">
                <h5 class="fw-bold mt-3 mb-1">राजेश</h5>
                <p class="text-muted small mb-2">मो.: 7380820551</p>
                <span class="badge bg-info">सचिव</span>
            </div>
        </div>

        <div class="swiper-slide">
            <div class="partner-logo bg-white p-4 rounded-3 text-center">
                <img src="assets/images/partners/p5.png" alt="Partner" class="img-fluid" style="max-height: 120px; margin-bottom: 15px;">
                <h5 class="fw-bold mt-3 mb-1">डॉ. दुर्गेश चंद्रा</h5>
                <p class="text-muted small mb-2">मो.: 8081907027</p>
                <span class="badge bg-danger">प्रदेश अध्यक्ष</span>
            </div>
        </div>

        <div class="swiper-slide">
            <div class="partner-logo bg-white p-4 rounded-3 text-center">
                <img src="assets/images/partners/p.png" alt="Partner" class="img-fluid" style="max-height: 120px; margin-bottom: 15px;">
                <h5 class="fw-bold mt-3 mb-1">श्याम सुंदर राव </h5>
                <p class="text-muted small mb-2">मो.: 9651121493</p>
                <span class="badge bg-warning">प्रदेश कोषाध्यक्ष</span>
            </div>
        </div>

    </div>
</div>
        </div>
    </section>

    <!-- Call to Action with Parallax -->
    <section class="cta-section py-5 position-relative" style="background-image: linear-gradient(rgba(13, 110, 253, 0.9), rgba(13, 110, 253, 0.9)), url('assets/images/cta-bg.jpg'); background-attachment: fixed;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center text-white" data-aos="zoom-in">
                    <h2 class="display-4 fw-bold mb-4">BRCT Bharat Trust में शामिल हों</h2>
                    <p class="lead mb-5">समाज के उत्थान में अपना योगदान दें और सदस्य बनें। आपका एक कदम किसी की जिंदगी बदल सकता है।</p>
                    <div class="cta-buttons">
                        <a href="pages/register.php" class="btn btn-light btn-lg me-3 px-5 py-3">रजिस्टर करें <i class="fas fa-user-plus ms-2"></i></a>
                        <a href="pages/login.php" class="btn btn-outline-light btn-lg px-5 py-3">लॉगिन <i class="fas fa-sign-in-alt ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'components/footer.php';?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Core Team Loader -->
    <script>
        // Fetch and render core team members
        async function loadCoreTeam() {
            try {
                const response = await fetch('api/get_core_team.php');
                const data = await response.text();
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(data);
                } catch (e) {
                    console.error('Invalid JSON response:', data);
                    throw new Error('Invalid server response');
                }
                
                const container = document.getElementById('coreTeamContainer');
                
                if (result.success && result.data && result.data.length > 0) {
                    let html = '';
                    result.data.forEach(member => {
                        html += `
                            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                                <div class="team-member-card">
                                    <div class="team-member-image">
                                        <img src="${member.photo_url}" alt="${member.full_name}" loading="lazy">
                                    </div>
                                    <div class="team-member-content">
                                        <h3 class="team-member-name">${member.full_name}</h3>
                                        <span class="team-member-post">${member.post_name}</span>
                                        <div class="team-member-phone">
                                            <i class="fas fa-phone"></i>
                                            <a href="tel:${member.mobile_number}" style="color: inherit; text-decoration: none;">
                                                ${member.mobile_number}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                    
                    // Reinitialize AOS for new elements
                    if (typeof AOS !== 'undefined') {
                        AOS.refresh();
                    }
                } else {
                    // No members found - show empty state silently
                    container.innerHTML = `
                        <div class="team-empty-state col-12">
                            <div class="team-empty-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="team-empty-text">कोर टीम जल्द ही जोड़ी जाएगी</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading core team:', error);
                // Show empty state on error too
                const container = document.getElementById('coreTeamContainer');
                container.innerHTML = `
                    <div class="team-empty-state col-12">
                        <div class="team-empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="team-empty-text">कोर टीम जल्द ही जोड़ी जाएगी</p>
                    </div>
                `;
            }
        }
        
        // Load team members when DOM is ready
        document.addEventListener('DOMContentLoaded', loadCoreTeam);
    </script>
</body>
</html>