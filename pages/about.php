<?php
// About Page
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>हमारे बारे में - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/about.css">
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar (Non-sticky for about page) -->
    <?php $navbar_sticky = false; include '../components/navbar.php'; ?>

    <!-- About BRCT Section -->
    <section class="about-brct py-5">
        <div class="container">
            <!-- Header Section -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="text-center mb-5" >
                        <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">हमारा परिचय</span>
                        <h2 class="display-5 fw-bold mb-4">BRCT Bharat Trust के बारे में</h2>
                        <p class="lead text-secondary lh-lg">
                            BRCT Bharat Trust एक सामाजिक सेवा आधारित संगठन है जिसका उद्देश्य समाज के अंतिम व्यक्ति तक
                            तेजी से आर्थिक, सामाजिक, शैक्षिक और न्यायिक सहायता पहुँचाना है।<br>
                            यह संस्था समाज में पारस्परिक सहयोग की भावना को बढ़ावा देती है ताकि जरूरत के समय
                            एक सदस्य दूसरे सदस्य की मदद कर सके।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Purpose and Eligibility Section -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-bullseye fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">संस्था का उद्देश्य</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            BRCT Bharat Trust की स्थापना समाज में सहयोग और सेवा की भावना को मजबूत करने के लिए की गई है।
                            इस संस्था का मुख्य उद्देश्य आकस्मिक घटनाओं, सामाजिक जरूरतों तथा पारिवारिक संकट की स्थिति में
                            सदस्यों को आर्थिक सहायता उपलब्ध कराना है।
                        </p>
                        <p class="text-secondary lh-lg">
                            संस्था विभिन्न सरकारी और निजी क्षेत्रों में कार्यरत लोगों को एक मंच प्रदान करती है
                            जहाँ सभी सदस्य मिलकर एक दूसरे के कठिन समय में सहयोग कर सकें।
                        </p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">कौन सदस्य बन सकता है</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            BRCT Bharat Trust की सदस्यता समाज के सभी वर्गों के लिए खुली है।
                            सरकारी कर्मचारी, प्राइवेट कर्मचारी, शिक्षक, डॉक्टर, इंजीनियर,
                            किसान, मजदूर, व्यापारी, छात्र-छात्राएं, गृहिणी तथा अन्य सभी
                            पेशा से जुड़े व्यक्ति इस संस्था की सदस्यता ले सकते हैं।
                        </p>
                        <p class="text-secondary lh-lg">
                            सदस्य बनने के लिए व्यक्ति की न्यूनतम आयु 18 वर्ष और अधिकतम आयु 60 वर्ष निर्धारित है।
                            एक बार सदस्य बनने के बाद 65 वर्ष की आयु तक सदस्यता बनाए रखने की अनुमति होती है।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Schemes Section -->
            <div class="row mb-5 mt-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-hand-holding-heart fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">आकस्मिक निधन पर आर्थिक सहयोग योजना</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            BRCT Bharat Trust की प्रमुख योजना "आकस्मिक निधन पर आर्थिक सहयोग योजना" है।
                            यदि संस्था का कोई वैधानिक सदस्य असामयिक रूप से निधन हो जाता है
                            तो संस्था से जुड़े सभी सदस्य निर्धारित न्यूनतम राशि के माध्यम से
                            दिवंगत सदस्य के परिवार को आर्थिक सहयोग प्रदान करते हैं।
                        </p>
                        <p class="text-secondary lh-lg">
                            इस सहयोग का उद्देश्य दिवंगत सदस्य के परिवार को कठिन समय में आर्थिक सहारा प्रदान करना है।
                            सहयोग की राशि सीधे दिवंगत सदस्य के नॉमिनी के बैंक खाते में भेजी जाती है।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Special Schemes Section -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-heart text-primary mb-3" style="font-size: 2.5rem;"></i>
                            <h4 class="fw-bold">बेटी विवाह शगुन योजना</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            BRCT Bharat Trust द्वारा "बेटी विवाह शगुन योजना" भी संचालित की जाती है।
                            इस योजना के अंतर्गत पात्र सदस्य की बेटी के विवाह के समय
                            सदस्यों के सहयोग से आर्थिक सहायता प्रदान की जाती है।
                        </p>
                        <p class="text-secondary lh-lg">
                            इस योजना का उद्देश्य समाज में बेटी के विवाह के समय आने वाली आर्थिक
                            चुनौतियों को कम करना तथा सामाजिक सहयोग की भावना को मजबूत करना है।
                        </p>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">पारदर्शिता और तकनीकी व्यवस्था</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            BRCT Bharat Trust अपनी सभी प्रक्रियाओं को पारदर्शी और सरल बनाने के लिए
                            डिजिटल तकनीक का उपयोग करता है। सदस्यता, सहयोग और अन्य प्रक्रियाएं
                            वेबसाइट के माध्यम से संचालित की जाती हैं।
                        </p>
                        <p class="text-secondary lh-lg">
                            संस्था का उद्देश्य तकनीकी माध्यम से पारदर्शिता बनाए रखते हुए
                            सदस्यों के बीच विश्वास और सहयोग को मजबूत करना है।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Goal Section -->
            <div class="row mt-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-primary-soft">
                        <div class="text-center">
                            <i class="fas fa-target fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-4">हमारा लक्ष्य</h4>
                            <p class="text-secondary lh-lg lead">
                                BRCT Bharat Trust का लक्ष्य समाज में सहयोग, संवेदनशीलता और पारस्परिक सहायता की भावना को बढ़ावा देना है।<br>
                                संस्था का प्रयास है कि किसी भी सदस्य या उसके परिवार को कठिन समय में अकेला न छोड़कर
                                सामूहिक सहयोग के माध्यम से उन्हें आर्थिक और सामाजिक सहारा प्रदान किया जाए।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="row mt-5">
                <div class="col-lg-12 text-center" data-aos="fade-up">
                    <h4 class="fw-bold mb-4">क्या आप हमारे परिवार का हिस्सा बनना चाहते हैं?</h4>
                    <a href="register.php" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="fas fa-user-plus me-2"></i> अभी सदस्य बनें
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary btn-lg px-5 py-3 ms-3">
                        <i class="fas fa-phone me-2"></i> हमसे संपर्क करें
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <!-- AOS Initialization -->
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100,
            easing: 'ease-in-out'
        });
    </script>
</body>
</html>
