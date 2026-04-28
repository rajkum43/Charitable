<?php
// Niyamavali (Rules & Regulations) Page
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>नियमावली - BRCT Bharat Trust</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/about.css">
</head>
<body>

    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php $navbar_sticky = false; include '../components/navbar.php'; ?>

    <!-- Niyamavali Section -->
    <section class="about-brct py-5">
        <div class="container">
            <!-- Header Section -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="text-center mb-5" data-aos="fade-up">
                        <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">भवतु सब्ब मंगलम्</span>
                        <h2 class="display-5 fw-bold mb-4">BRCT Bharat Trust की नियमावली</h2>
                        <p class="lead text-secondary lh-lg">
                            आकस्मिक निधन पर आर्थिक सहयोग योजना एवं बेटी विवाह शगुन योजना की नियमावली
                        </p>
                    </div>
                </div>
            </div>

            <!-- BRCT Introduction Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-light">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-info-circle fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">भारत रिलीफ चैरिटेबल टीम (BRCT) की स्थापना</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                भारत रिलीफ चैरिटेबल टीम (BRCT) की स्थापना 23 नवम्बर 2025 को समाज के उच्च पदस्थ से लेकर अंतिम व्यक्ति तक तीव्र आर्थिक/शैक्षिक/न्यायिक/सामाजिक राहत पहुंचाने के लिए हुई है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                शिक्षा विभाग, स्वास्थ्य विभाग, पंचायत विभाग, सिंचाई विभाग, जल निगम विभाग, राजस्व विभाग, ग्रामीण विकास अभिकरण विभाग, परिवहन विभाग, रेल विभाग, बाल विकास एवं पुष्टाहार विभाग, विद्युत विभाग, बैंक, पुलिस, होमगार्ड, कलेक्ट्रेट विभाग इत्यादि अन्य समस्त विभागों के सरकारी/संविदा/आउटसोर्सिंग कर्मचारी अधिकारी BRCT की सदस्यता ले सकते हैं।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                वित्तविहीन विद्यालय/कॉलेज/मदरसा के शिक्षक, शिक्षणेत्तर कर्मचारी एवं प्रबंधक BRCT की सदस्यता ले सकते हैं।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                प्राइवेट सेक्टर में कार्यरत समस्त कर्मचारी, अधिकारी, एडवोकेट, मीडियाकर्मी, प्रबंधक, डॉक्टर, इंजीनियर, नर्स BRCT की सदस्यता ले सकते हैं।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                किसान, मजदूर, व्यवसाई, कारीगर, गृहिणी, छात्र छात्राएं BRCT की सदस्यता ले सकते हैं।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                BRCT व्यवसाय-पेशा की भावना से ऊपर उठकर आर्थिक मदद कराता है। BRCT की सदस्यता हेतु प्रदेश का स्थाई निवासी होना या उत्तर प्रदेश में सरकारी/प्राइवेट किसी पद पर कार्यरत होना अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                समस्त सदस्यों को वार्षिक 50 रुपए "BRCT" को दान देना अनिवार्य है।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Age Limit Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">सदस्यता की आयु सीमा</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>न्यूनतम आयु:</strong> 18 वर्ष
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>अधिकतम आयु (आवेदन):</strong> 60 वर्ष (दिनांक 13/01/2026 से)
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>सदस्यता विस्तार:</strong> 65 वर्ष तक सदस्यता बनाए रख सकते हैं
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>स्वतः समाप्ति:</strong> 65 वर्ष पूर्ण होने पर
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>वार्षिक नवीकरण:</strong> आवश्यक है
                            </p>
                            <p class="text-secondary lh-lg">
                                <strong>सदस्यता प्राप्ति:</strong> वेबसाइट के माध्यम से रजिस्ट्रेशन फॉर्म
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Donation Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-money-bill-wave fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">वार्षिक दान योगदान</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 2:</strong> हर वर्ष BRCT को 50 रुपए वार्षिक दान देने के लिए 45 दिन की अतिरिक्त समय सीमा दी जाती है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                BRCT की वैधानिक सदस्यता बनाए रखने के लिए ट्रस्ट को वार्षिक दान दिए हुए 1 वर्ष पूरे होने के बाद 45 दिन के अंदर वार्षिक दान देकर अपने प्रोफाइल में ट्रांजेक्शन स्क्रीन शॉट अपलोड करना अनिवार्य होगा।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                निर्धारित समय सीमा में वार्षिक दान जमा नहीं करने वाले सदस्य की सदस्यता स्वतः समाप्त हो जाएगी एवं "आकस्मिक निधन पर आर्थिक सहयोग योजना" में अपात्र कर दिया जाएगा।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Communication Rule Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-comments fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">संचार माध्यम - नियम 3</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                सूचनाओं के आदान प्रदान हेतु आधिकारिक व्हाट्सएप ग्रुप/टेलीग्राम ग्रुप में जुड़कर सूचनाओं से अपडेट रहना होगा।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                सूचना के अभाव में यदि आपके वैधानिकता पर कोई प्रभाव पड़ा तो उसके जिम्मेदार आप स्वयं होंगे।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                सदस्यों की सुविधा हेतु हेल्पलाइन 9936385189 जारी किया गया है, जिस पर कॉल/व्हाट्सएप मैसेज के माध्यम से जानकारी का आदान प्रदान किया जा सकता है।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accidental Death Assistance Scheme -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-info bg-opacity-10">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-heart-pulse fa-3x text-info mb-3"></i>
                            <h4 class="fw-bold">आकस्मिक निधन योजना - विस्तृत नियम</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 4:</strong> यदि BRCT के किसी वैधानिक सदस्य की असामयिक दुखद निधन हो जाती है तो BRCT से जुड़े शेष अन्य सभी सदस्य संस्थापक मंडल द्वारा नियमानुसार आधिकारिक आह्वान पर निर्धारित न्यूनतम धनराशि सीधे दिवंगत सदस्य के नॉमिनी के बैंक खाते में आर्थिक सहयोग भेजेंगे।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                आर्थिक सहयोग भेजकर वेबसाइट पर ट्रांजेक्शन डिटेल्स भरते हुए ट्रांजेक्शन रसीद अपलोड करना भी अनिवार्य है। वर्तमान में आर्थिक सहयोग प्रति सदस्य न्यूनतम 50 रुपए निर्धारित किया जा रहा है। सदस्य संख्या के अनुसार प्रति सदस्य न्यूनतम धनराशि घटाने का अधिकार संस्थापक मंडल के पास सुरक्षित रहेगा।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 5:</strong> समस्त सदस्यों की लॉक इन पीरियड 8 माह रहेगा। लॉक इन पीरियड में जारी समस्त सहयोग करना अनिवार्य है। 30 मई 2026 से सदस्यता लेने वाले सदस्यों के लिए लॉक इन पीरियड 10 माह किया जा रहा है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 6:</strong> वैधानिक सदस्यता के लिए लॉक इन पीरियड तक के समस्त सहयोग करना अनिवार्य है एवं लॉक इन पीरियड के बाद कुल 90% सहयोग करना अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 10:</strong> यदि किसी सदस्य द्वारा सदस्य बनने के बाद सहयोग नहीं किया गया या कुछ महीने/वर्षों सहयोग करने के बाद 1 या 1 से अधिक सहयोग छोड़ दिया, ऐसे स्थिति में लगातार 8 माह सहयोग करके और 8 माह का समय पूरा करके पुनः वैधानिक सदस्यता प्राप्त कर सकता है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 11:</strong> सदस्य द्वारा आत्महत्या की स्थिति में कोई आर्थिक सहयोग हेतु अपील नहीं की जाएगी। आत्महत्या के अलावा सभी तरह के मृत्यु पर आर्थिक सहयोग की अपील की जाएगी।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 12:</strong> यदि सदस्य द्वारा बनाए गए नॉमिनी ने ही खुद सदस्य की हत्या की है तो ऐसे नॉमिनी को आर्थिक मदद नहीं किया जाएगा। संस्थापक मंडल दिवंगत सदस्य के यथोचित नॉमिनी का चयन करने के लिए स्वतंत्र होंगे।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 13:</strong> BRCT सीधे दिवंगत सदस्य के नॉमिनी के खाते में आर्थिक सहयोग करवाती है इसलिए किसी भी प्रकार की न्यायिक चुनौती देने का अधिकार किसी व्यक्ति या सदस्य के पास नहीं होगा।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 17:</strong> यदि BRCT का कोई वैधानिक सदस्य अपनी उम्र सीमा 65 वर्ष आयु सफलतापूर्वक पूरा कर लेता है तो उक्त सदस्य की 65 वर्ष उम्र के बाद जब भी दुखद निधन होता है तो ट्रस्ट के खाते से 20,000 रुपए उक्त के नॉमिनी को अंतिम संस्कार के लिए आर्थिक सहयोग दिया जाएगा।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suspension and Termination Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-danger bg-opacity-10 border-danger">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                            <h4 class="fw-bold">सदस्यता निलंबन और विच्छेद</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 14:</strong> कोई भी सदस्य BRCT के खिलाफ दुष्प्रचार या अफवाह फैलाता है, बिना साक्ष्य या आकड़े प्रस्तुत किए आरोप लगाता है तो टीम उसकी सदस्यता रद्द करने व विधिक कार्यवाही हेतु स्वतंत्र होगी।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 15:</strong> BRCT के किसी पदाधिकारी के साथ कोई सदस्य अभद्र व्यवहार करते हुए या BRCT विरोधी गतिविधि में लिप्त पाया गया तो पर्याप्त साक्ष्य मिलने पर उक्त सदस्य की सदस्यता रद्द कर दी जाएगी।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation Usage Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-piggy-bank fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">दान का उपयोग - नियम 16</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                ट्रस्ट को दिए गए वार्षिक 50 रुपए दान से निम्नलिखित कार्यों में खर्च किया जाता है:
                            </p>
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2">वेबसाइट के निर्माण और संचालन में</li>
                                <li class="mb-2">ऐप बनवाने और संचालन में</li>
                                <li class="mb-2">जिला और प्रदेश कार्यालय खर्च हेतु</li>
                                <li class="mb-2">जिला और प्रदेश हेल्पलाइन नंबर पर नियुक्त स्टाफ को मानदेय देने में</li>
                                <li class="mb-2">दिवंगत सदस्य के घर स्थलीय सत्यापन में</li>
                                <li class="mb-2">प्रचार प्रसार और सदस्यता अभियान में</li>
                                <li class="mb-2">समय समय पर नई तकनीकी लाने में ताकि प्रक्रिया पारदर्शी के साथ साथ आसान बन सके</li>
                                <li class="mb-2">उत्तर प्रदेश के जरूरतमंद व्यक्तियों/छात्रों के हित में विभिन्न प्रकार का सहयोग करने में</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Beti Vivah Scheme -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-success bg-opacity-10">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-gift fa-3x text-success mb-3"></i>
                            <h4 class="fw-bold">बेटी विवाह शगुन योजना</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>शुरुआत:</strong> 15 जनवरी 2026 से
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>लॉक-इन अवधि:</strong> 30 जनवरी 2026 या उसके बाद रजिस्टर्ड सदस्यों के लिए 12 माह। 30 मई 2026 से 2 वर्ष।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>अवश्यक योगदान:</strong> सदस्यता तिथि से विवाह तिथि तक 90% अवसरों पर सहयोग करना अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>अधिकतम सीमा:</strong> प्रति सदस्य अधिकतम 11 रुपए तक का आर्थिक सहयोग।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>पात्र बेटियाँ:</strong> अधिकतम 2 जैविक बेटियाँ योग्य हैं।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>बेटी की शर्त:</strong> विवाह के लिए BRCT सदस्य होना अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>आवेदन:</strong> विवाह से 30 दिन पहले आवेदन अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>लाभ धारक:</strong> सीधे बेटी के पिता के बैंक खाते में।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>विवाह के बाद दायित्व:</strong> लाभ के बाद न्यूनतम 10 वर्ष तक योजना में 90% अवसरों पर सहयोग करना अनिवार्य है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>माता का अधिकार:</strong> यदि सदस्य (पिता) की मृत्यु बेटी की शादी से पहले हो जाती है, तो माता सदस्य बनकर योजना का लाभ ले सकती है।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>भाई का अधिकार:</strong> यदि बेटी के माता-पिता दोनों जीवित नहीं हैं या 60 वर्ष से अधिक आयु के हैं तो भाई (18 वर्ष या अधिक) सदस्य बनकर आवेदन कर सकता है।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Important Rules -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">महत्वपूर्ण नियम और शर्तें</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 8:</strong> संस्थापक मंडल वैधानिकता या किसी भी प्रकार के मामलों में जहां उचित समझेंगे अपने स्तर से परीक्षण करने व निर्णय लेने के लिए स्वतंत्र होंगे। कोई भी सदस्य/नॉमिनी आर्थिक सहयोग प्राप्त करने हेतु कानूनी दावा नहीं कर सकेगा।
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>नियम 9:</strong> सहयोग के दौरान या उसके बाद यदि किसी सदस्य द्वारा गलती से अधिक राशि किसी सहयोग हो रहे/हो चुके नॉमिनी के खाते में भेज दिया तो उचित साक्ष्य प्रस्तुत करने पर नॉमिनी द्वारा वो धनराशि सीधे उस सदस्य के खाते में वापस करना पड़ेगा।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future Plans -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-warning bg-opacity-10">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-star fa-3x text-warning mb-3"></i>
                            <h4 class="fw-bold">भविष्य की योजनाएं</h4>
                        </div>
                        <div class="ms-3">
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2">सड़क दुर्घटना एवं गंभीर बीमारी इलाज योजना</li>
                                <li class="mb-2">छात्रों के लिए उच्च शिक्षा/तकनीकी शिक्षा/सिविल सेवा परीक्षा हेतु योजना</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Note -->
            <div class="row mt-5 mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-primary bg-opacity-10 border-primary">
                        <div class="text-center">
                            <i class="fas fa-info-circle fa-3x text-primary mb-3"></i>
                            <p class="text-secondary lh-lg">
                                <strong>"किसी भी निर्णय की स्थिति में वेबसाइट पर अपलोड नियमावली की प्रति ही मान्य होगी।"</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="row mt-5">
                <div class="col-lg-12 text-center" data-aos="fade-up">
                    <h4 class="fw-bold mb-4">क्या आपको अधिक जानकारी चाहिए?</h4>
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
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    
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