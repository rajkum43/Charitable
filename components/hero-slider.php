<?php
// This file displays hero sliders from database
// Include in your main page where slider section is

require_once 'includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    return; // Silently fail if DB connection error
}

// Fetch active sliders
$sliders = [];
$result = $conn->query("SELECT * FROM sliders WHERE status = 1 ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ensure image path is correct
        if (!empty($row['image']) && !strpos($row['image'], '/')) {
            $row['image'] = 'assets/images/slider/' . $row['image'];
        }
        $sliders[] = $row;
    }
}

// If no sliders in database, use default sliders
if (empty($sliders)) {
    $sliders = [
        [
            'heading' => 'BRCT Bharat Trust',
            'description' => 'समाजिक सेवा के लिए एक संकल्प<br>समाज के हर वर्ग के उत्थान हेतु समर्पित',
            'image' => 'assets/images/slider/slide1.jpg',
            'button_text' => 'Join Now',
            'button_link' => 'pages/register.php'
        ],
        [
            'heading' => 'बेटी बचाओ, बेटी पढ़ाओ',
            'description' => 'हर बेटी के उज्ज्वल भविष्य के लिए हमारा संकल्प',
            'image' => 'assets/images/slider/slide2.jpg',
            'button_text' => 'जानें और जुड़ें',
            'button_link' => '#'
        ],
        [
            'heading' => 'सामाजिक सुरक्षा का वचन',
            'description' => 'जरूरतमंद परिवारों के साथ खड़े हैं हम',
            'image' => 'assets/images/slider/slide3.jpg',
            'button_text' => 'सहयोग करें',
            'button_link' => '#'
        ]
    ];
}
?>

<!-- Hero Slider Section - Bootstrap Carousel -->
<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php foreach ($sliders as $index => $slider): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" id="slider-<?php echo $index; ?>" style="background-image: url('<?php echo htmlspecialchars($slider['image']); ?>'); background-size: cover; background-position: center; background-attachment: scroll;">
                    <!-- Overlay -->
                    <div class="carousel-overlay"></div>
                    
                    <!-- Content -->
                    <div class="carousel-caption d-flex align-items-center justify-content-center h-100">
                        <div class="container">
                            <div class="row">
                                <div class="col-12 col-md-10 col-lg-8 mx-auto text-center text-white slider-content">
                                    <!-- Heading Section -->
                                    <div class="slider-heading-wrapper mb-3 mb-md-4">
                                        <h1 id="slider-heading-<?php echo $index; ?>" class="slider-heading display-4 fw-bold" style="text-shadow: 3px 3px 8px rgba(0,0,0,0.5);" data-aos="fade-up">
                                            <?php echo htmlspecialchars($slider['heading']); ?>
                                        </h1>
                                    </div>
                                    
                                    <!-- Description Section -->
                                    <div class="slider-description-wrapper mb-4 mb-md-5">
                                        <p id="slider-description-<?php echo $index; ?>" class="slider-description lead fs-5 fs-md-6 m-0" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.4);" data-aos="fade-up" data-aos-delay="100">
                                            <?php echo $slider['description']; ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Buttons Section -->
                                    <div id="slider-buttons-<?php echo $index; ?>" class="hero-buttons d-flex flex-column flex-sm-row gap-3 justify-content-center" data-aos="fade-up" data-aos-delay="200">
                                        <?php if (!empty($slider['button_link'])): ?>
                                            <a href="<?php echo htmlspecialchars($slider['button_link']); ?>" id="slider-btn-primary-<?php echo $index; ?>" class="btn btn-primary btn-lg px-5 py-3 fw-600">
                                                <?php echo htmlspecialchars($slider['button_text']); ?> <i class="fas fa-arrow-right ms-2"></i>
                                            </a>
                                            <a href="pages/about.php" id="slider-btn-secondary-<?php echo $index; ?>" class="btn btn-outline-light btn-lg px-5 py-3 fw-600">
                                                Learn More
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        
        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            <?php foreach ($sliders as $index => $slider): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                    <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                    aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
