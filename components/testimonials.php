<?php
// This file displays member stories testimonials from database
// Include in your main page where testimonials section is

require_once 'includes/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    return; // Silently fail if DB connection error
}

// Fetch active member stories - fetch more to handle any filtering
$stories = [];
$result = $conn->query("SELECT * FROM member_stories WHERE status = 1 ORDER BY id DESC LIMIT 9");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Ensure image path is correct
        if (!empty($row['image']) && !strpos($row['image'], '/')) {
            $row['image'] = 'assets/images/stories/' . $row['image'];
        }
        $stories[] = $row;
    }
}

// If no stories in database, use default testimonials
if (empty($stories)) {
    $stories = [
        [
            'name' => 'रामप्रसाद गुप्ता',
            'address' => 'गोरखपुर',
            'story' => 'BRCT Bharat Trust ने मेरी बेटी के विवाह में बहुत मदद की। आज मैं बहुत खुश हूं कि मैं इस ट्रस्ट का सदस्य हूं।',
            'image' => 'assets/images/testimonials/user1.jpg',
            'rating' => 5
        ],
        [
            'name' => 'सुनील कुमार',
            'address' => 'लखनऊ',
            'story' => 'पिता के निधन के बाद ट्रस्ट ने हमें आर्थिक सहायता दी। आज मैं खुद इस ट्रस्ट का हिस्सा हूं और दूसरों की मदद कर रहा हूं।',
            'image' => 'assets/images/testimonials/user2.jpg',
            'rating' => 5
        ],
        [
            'name' => 'अंजू देवी',
            'address' => 'वाराणसी',
            'story' => 'बच्चों की पढ़ाई के लिए छात्रवृत्ति मिली। BRCT Bharat Trust वाकई में समाज के लिए वरदान है।',
            'image' => 'assets/images/testimonials/user3.jpg',
            'rating' => 5
        ],
        [
            'name' => 'प्रिया शर्मा',
            'address' => 'दिल्ली',
            'story' => 'परिवार के आर्थिक संकट में BRCT Bharat Trust ने हमारा साथ दिया। उनकी मदद से मेरे बच्चों की शिक्षा जारी रह सकी।',
            'image' => 'assets/images/testimonials/user1.jpg',
            'rating' => 5
        ],
        [
            'name' => 'राज कुमार',
            'address' => 'मुंबई',
            'story' => 'ट्रस्ट की स्वास्थ्य योजना से मुझे बेहतर इलाज मिल सका। यह संस्था सच में समाज के लिए एक वरदान है।',
            'image' => 'assets/images/testimonials/user2.jpg',
            'rating' => 5
        ],
        [
            'name' => 'मीना देवी',
            'address' => 'कानपुर',
            'story' => 'महिला सशक्तिकरण कार्यक्रम में हिस्सा लेकर मैं आत्मनिर्भर हो गई। BRCT Bharat Trust का धन्यवाद।',
            'image' => 'assets/images/testimonials/user3.jpg',
            'rating' => 5
        ]
    ];
}
?>

<!-- Testimonials Section with Swiper -->
<section class="testimonials-section py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">हमारे सदस्यों की राय</span>
            <h2 class="section-title">क्या कहते हैं हमारे सदस्य?</h2>
            <p class="lead text-secondary">हजारों खुश सदस्यों की कहानियां और अनुभव</p>
        </div>
        
        <div class="swiper testimonialSwiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <?php foreach ($stories as $testimonial): ?>
                <div class="swiper-slide">
                    <div class="testimonial-card bg-white p-5 rounded-4 shadow-sm">
                        <div class="testimonial-content mb-4">
                            <i class="fas fa-quote-left fa-3x text-primary-soft mb-3"></i>
                            <p class="lead">"<?php echo htmlspecialchars($testimonial['story']); ?>"</p>
                        </div>
                        <div class="testimonial-author d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($testimonial['image'] ?? 'assets/images/testimonials/default.jpg'); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>" class="rounded-circle me-3" width="60" height="60" style="object-fit: cover; object-position: center;">
                            <div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($testimonial['name']); ?></h5>
                                <p class="text-secondary mb-0">सदस्य, <?php echo htmlspecialchars($testimonial['address']); ?></p>
                                <div class="text-warning">
                                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for ($i = $testimonial['rating']; $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
