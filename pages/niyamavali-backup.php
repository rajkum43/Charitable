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
                        <span class="badge bg-primary-soft text-primary px-3 py-2 mb-3">संस्था के नियम</span>
                        <h2 class="display-5 fw-bold mb-4">BRCT Bharat Trust की नियमावली</h2>
                        <p class="lead text-secondary lh-lg">
                            BRCT Bharat Trust की नियमावली सदस्यों के अधिकारों, कर्तव्यों और संस्था के संचालन को 
                            नियंत्रित करने वाले नियमों को निर्धारित करती है। ये नियम सभी सदस्यों के लिए समान रूप से 
                            लागू होते हैं और सदस्यता के समय अनिवार्य है।
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
                                <strong>भारत रिलीफ चैरिटेबल टीम (BRCT) की स्थापना <u>23 नवम्बर 2025</u> को समाज के उच्च पदस्थ से लेकर अंतिम व्यक्ति तक आर्थिक / शैक्षिक / व्यावसायिक / सामाजिक राहत पहुँचाने के उद्देश्य से की गई है।</strong>
                            </p>
                            <p class="text-secondary lh-lg mb-3">
                                BRCT व्यवसाय / पेशा की भावना से ऊपर उठकर आर्थिक मदद करता है और सभी सदस्यों के साथ समान व्यवहार करता है।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eligibility Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-check-circle fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">सदस्यता के लिए पात्रता शर्तें</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3"><strong>निम्नलिखित व्यक्ति सदस्य बन सकते हैं:</strong></p>
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2"><strong>सरकारी कर्मचारी:</strong> शिक्षा, स्वास्थ्य, पंचायत, सिंचाई, जल निगम, राजस्व, ग्रामीण विकास, परिवहन, रेल, विद्यालय, बैंक, पुलिस आदि विभागों के कर्मचारी/अधिकारी</li>
                                <li class="mb-2"><strong>शिक्षा क्षेत्र:</strong> वित्तपोषित विद्यालय/कॉलेज/मदरसों के शिक्षक, शिक्षणेतर कर्मचारी एवं प्रबंधक</li>
                                <li class="mb-2"><strong>निजी क्षेत्र:</strong> सभी कर्मचारी, अधिकारी, एडवोकेट, मीडिया कर्मी, प्रबंधक, डॉक्टर, इंजीनियर, नर्स</li>
                                <li class="mb-2"><strong>अन्य पेशेवर:</strong> किसान, मजदूर, व्यापारी, कारीगर, गृहिणी, छात्र-छात्राएं</li>
                            </ul>
                            <p class="text-secondary lh-lg mt-3 mb-3"><strong>अनिवार्य शर्तें:</strong></p>
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2">उत्तर प्रदेश का स्थायी निवासी होना या उत्तर प्रदेश में सरकारी/प्राइवेट किसी पद पर कार्यरत होना</li>
                                <li class="mb-2"><strong>आयु सीमा:</strong> <u>18 वर्ष से 60 वर्ष</u> (दिनांक 13/01/2026 से आयु सीमा को <u>18 वर्ष से 60 वर्ष</u> तक निर्धारित की गई है)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Membership Terms Section -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">सदस्यता की अवधि</h4>
                        </div>
                        <ul class="text-secondary lh-lg">
                            <li class="mb-2"><strong>न्यूनतम आयु:</strong> 18 वर्ष</li>
                            <li class="mb-2"><strong>अधिकतम आयु (आवेदन):</strong> 60 वर्ष</li>
                            <li class="mb-2"><strong>सदस्यता विस्तार:</strong> 65 वर्ष तक सदस्यता बनाए रख सकते हैं</li>
                            <li class="mb-2"><strong>स्वतः समाप्ति:</strong> 65 वर्ष पूर्ण होने पर</li>
                            <li class="mb-2"><strong>वार्षिक नवीकरण:</strong> आवश्यक है</li>
                            <li class="mb-2"><strong>सदस्यता प्राप्ति:</strong> वेबसाइट के माध्यम से रजिस्ट्रेशन फॉर्म</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-money-bill-wave fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">वार्षिक दान योगदान</h4>
                        </div>
                        <ul class="text-secondary lh-lg">
                            <li class="mb-2"><strong>वार्षिक दान:</strong> ₹50 प्रत्येक वर्ष</li>
                            <li class="mb-2"><strong>दान की वैधानिकता अवधि:</strong> 45 दिन की अतिरिक्त समय सीमा</li>
                            <li class="mb-2"><strong>आवश्यक दस्तावेज:</strong> ट्रांजेक्शन स्क्रीनशॉट अपलोड करना</li>
                            <li class="mb-2"><strong>असंपत्ति:</strong> यदि दान समय पर न जमा हो तो सदस्यता स्वतः समाप्त</li>
                            <li class="mb-2"><strong>योजना अपात्रता:</strong> लाभ योजनाओं से अपात्र कर दिया जाएगा</li>
                            <li class="mb-2"><strong>दान का उपयोग:</strong> वेबसाइट, ऐप, कार्यालय, हेल्पलाइन, सत्यापन, प्रचार आदि में</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Member Rights Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-star fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">सदस्य के अधिकार</h4>
                        </div>
                        <div class="row ms-2">
                            <div class="col-md-6">
                                <ul class="text-secondary lh-lg">
                                    <li class="mb-2">बेटी विवाह शुभम योजना के तहत आर्थिक सहायता पाने का अधिकार</li>
                                    <li class="mb-2">आकस्मिक निधन पर आर्थिक सहयोग प्राप्त करने का अधिकार</li>
                                    <li class="mb-2">संस्था की बैठकों में भाग लेने का अधिकार</li>
                                    <li class="mb-2">संस्था के निर्णयों में मत देने का अधिकार</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="text-secondary lh-lg">
                                    <li class="mb-2">संस्था से जानकारी प्राप्त करने का अधिकार</li>
                                    <li class="mb-2">पारदर्शी लेखा-जोखा देखने का अधिकार</li>
                                    <li class="mb-2">किसी भी शिकायत दर्ज करने का अधिकार</li>
                                    <li class="mb-2">व्यक्तिगत जानकारी गोपनीय रखने का अधिकार</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Duties Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">सदस्य के अनिवार्य कर्तव्य</h4>
                        </div>
                        <div class="row ms-2">
                            <div class="col-md-6">
                                <ul class="text-secondary lh-lg">
                                    <li class="mb-2">प्रत्येक वर्ष ₹50 वार्षिक दान जमा करना अनिवार्य</li>
                                    <li class="mb-2">निर्धारित समय सीमा में दान जमा करना (45 दिन की छूट सहित)</li>
                                    <li class="mb-2">अपनी व्यक्तिगत जानकारी सही और अद्यतन रखना</li>
                                    <li class="mb-2">आधिकारिक व्हाट्सएप/टेलीग्राम ग्रुप में सक्रिय रहना</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="text-secondary lh-lg">
                                    <li class="mb-2">किसी अन्य सदस्य की दुर्घटना/निधन पर आवश्यकतानुसार सहयोग करना</li>
                                    <li class="mb-2">संस्था के नियमों का पालन करना</li>
                                    <li class="mb-2">संस्था के प्रति सकारात्मक रवैया बनाए रखना</li>
                                    <li class="mb-2">कोई भी जाली/गलत दस्तावेज प्रस्तुत न करना</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Benefit Schemes Section -->
            <div class="row mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-gift fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">बेटी विवाह शुभम योजना</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            <strong>शुरुआत:</strong> 25 जनवरी 2026 से
                        </p>
                        <ul class="text-secondary lh-lg">
                            <li class="mb-2"><strong>लॉक-इन अवधि:</strong> 12 महीने (23 नवम्बर 2025 के बाद रजिस्टर सदस्यों के लिए)</li>
                            <li class="mb-2"><strong>अवश्यक योगदान:</strong> कम से कम 90% अवसरों पर सहयोग करना अनिवार्य</li>
                            <li class="mb-2"><strong>अधिकतम सीमा:</strong> ₹11 प्रति सदस्य योगदान निर्धारित</li>
                            <li class="mb-2"><strong>पात्र बेटियाँ:</strong> अधिकतम 2 जैविक बेटियाँ योग्य</li>
                            <li class="mb-2"><strong>बेटी की शर्त:</strong> विवाह के लिए BRCT सदस्य होना आवश्यक</li>
                            <li class="mb-2"><strong>आवेदन:</strong> विवाह से 30 दिन पहले आवेदन अनिवार्य</li>
                            <li class="mb-2"><strong>लाभ धारक:</strong> सीधे बेटी के पिता के बैंक खाते में</li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-left">
                    <div class="about-card h-100">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-heart-pulse fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">आकस्मिक निधन योजना</h4>
                        </div>
                        <p class="text-secondary lh-lg mb-3">
                            <strong>1 वर्ष की सदस्यता के बाद पात्र</strong>
                        </p>
                        <ul class="text-secondary lh-lg">
                            <li class="mb-2"><strong>मृत्यु पश्चात सहायता:</strong> प्राकृतिक और आकस्मिक दोनों पर लागू</li>
                            <li class="mb-2"><strong>नॉमिनी योग्यता:</strong> सीधे नॉमिनी के खाते में धनराशि</li>
                            <li class="mb-2"><strong>हत्या की शर्त:</strong> नॉमिनी ने हत्या की तो अपात्र</li>
                            <li class="mb-2"><strong>आयु पूर्ण सदस्य:</strong> 65 वर्ष पश्चात ₹20,000 अंतिम संस्कार हेतु</li>
                            <li class="mb-2"><strong>न्यायिक चुनौती:</strong> कोई कानूनी सुधार का अधिकार नहीं</li>
                            <li class="mb-2"><strong>सत्यापन:</strong> दिवंगत सदस्य के घर स्थानीय सत्यापन</li>
                            <li class="mb-2"><strong>समय-सीमा:</strong> प्रक्रिया 15-30 दिन में</li>
                        </ul>
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
                            <p class="text-secondary lh-lg mb-3"><strong>नियम 14: दुष्प्रचार और अफवाह</strong></p>
                            <p class="text-secondary lh-lg mb-3">
                                यदि कोई सदस्य BRCT के खिलाफ दुष्प्रचार करता है, अफवाह फैलाता है, या बिना साक्ष्य आरोप लगाता है, तो टीम उसकी सदस्यता रद्द करने तथा आवश्यक विधिक कार्रवाई करने के लिए स्वतंत्र होगी।
                            </p>
                            <p class="text-secondary lh-lg mb-3"><strong>नियम 15: अभद्र व्यवहार</strong></p>
                            <p class="text-secondary lh-lg mb-3">
                                यदि कोई सदस्य BRCT के किसी पदाधिकारी के साथ अभद्र व्यवहार करता है या BRCT विरोधी गतिविधियों में लिप्त पाया जाता है, तो पर्याप्त साक्ष्य मिलने पर उसकी सदस्यता रद्द कर दी जाएगी।
                            </p>
                            <p class="text-secondary lh-lg"><strong>अन्य निलंबन कारण:</strong></p>
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2">निर्धारित समय सीमा के अंदर वार्षिक दान न जमा करना</li>
                                <li class="mb-2">गलत या जाली जानकारी प्रदान करना</li>
                                <li class="mb-2">किसी दूसरे सदस्य को धोखाधड़ी करना</li>
                                <li class="mb-2">संस्था के नियमों का उल्लंघन करना</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Complaint Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-comments fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">शिकायत और समाधान</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3">
                                <strong>किसी भी शिकायत को संस्था को सूचित किया जा सकता है:</strong>
                            </p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">लिखित रूप में</li>
                                <li class="mb-2">ईमेल के माध्यम से</li>
                                <li class="mb-2">वेबसाइट के माध्यम से</li>
                                <li class="mb-2">व्यक्तिगत रूप से</li>
                            </ul>
                            <p class="text-secondary lh-lg mb-3">
                                <strong>समाधान अवधि:</strong> संस्था 30 दिनों के अंदर शिकायत का समाधान करेगा
                            </p>
                            <p class="text-secondary lh-lg">
                                <strong>गोपनीयता:</strong> सभी शिकायतें पूरी तरह से गोपनीय रखी जाएंगी। किसी भी दुर्व्यवहार या धोखाधड़ी की स्थिति में कानूनी कार्रवाई की जा सकती है।
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Special Cases Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-info bg-opacity-10">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-star fa-3x text-info mb-3"></i>
                            <h4 class="fw-bold">बेटी विवाह योजना - विशेष परिस्थितियाँ</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3"><strong>माता का अधिकार (नियम 16):</strong></p>
                            <p class="text-secondary lh-lg mb-3">
                                यदि सदस्य (पिता) की मृत्यु बेटी की शादी से पहले हो जाती है, तो माता सदस्य बनकर योजना का लाभ ले सकती है।
                            </p>
                            <p class="text-secondary lh-lg mb-3"><strong>भाई का अधिकार (नियम 14-15):</strong></p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">यदि बेटी के माता-पिता दोनों जीवित नहीं हैं: जैविक भाई (न्यूनतम आयु 18 वर्ष) आवेदन कर सकता है</li>
                                <li class="mb-2">यदि माता-पिता दोनों 60 वर्ष से अधिक आयु के हैं: बेटी का जैविक भाई योजना का लाभ ले सकता है</li>
                            </ul>
                            <p class="text-secondary lh-lg"><strong>स्वयं के विवाह पर अयोग्य (नियम 7):</strong> सदस्य के स्वयं के विवाह पर यह योजना लागू नहीं होगी।</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Rules Section -->
            <div class="row mb-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card">
                        <div class="about-card-header mb-4">
                            <i class="fas fa-gavel fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold">महत्वपूर्ण नियम और शर्तें</h4>
                        </div>
                        <div class="ms-3">
                            <p class="text-secondary lh-lg mb-3"><strong>नियम 3: संचार माध्यम</strong></p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">सूचनाओं के आदान-प्रदान के लिए आधिकारिक व्हाट्सएप/टेलीग्राम ग्रुप में जुड़ना आवश्यक</li>
                                <li class="mb-2">सूचना के अभाव में सदस्य की वैधानिकता प्रभावित होने की जिम्मेदारी सदस्य की होगी</li>
                            </ul>

                            <p class="text-secondary lh-lg mb-3"><strong>नियम 12: नॉमिनी संबंधी</strong></p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">यदि सदस्य द्वारा बनाया गया नॉमिनी ही सदस्य की हत्या करता है तो उस नॉमिनी को आर्थिक मदद नहीं दी जाएगी</li>
                                <li class="mb-2">ऐसी स्थिति में संस्थापक मंडल आवश्यकतानुसार उचित नॉमिनी का चयन करेगा</li>
                            </ul>

                            <p class="text-secondary lh-lg mb-3"><strong>नियम 13: न्यायिक सीमा</strong></p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">सीधे दिवंगत सदस्य के नॉमिनी के खाते में आर्थिक सहयोग भेजा जाएगा</li>
                                <li class="mb-2">किसी भी प्रकार की न्यायिक चुनौती देने का अधिकार किसी व्यक्ति को नहीं होगा</li>
                            </ul>

                            <p class="text-secondary lh-lg mb-3"><strong>दान का उपयोग (नियम 16):</strong></p>
                            <ul class="text-secondary lh-lg mb-3">
                                <li class="mb-2">वेबसाइट निर्माण एवं संचालन</li>
                                <li class="mb-2">मोबाइल ऐप निर्माण एवं संचालन</li>
                                <li class="mb-2">जिला एवं प्रदेश कार्यालय खर्च</li>
                                <li class="mb-2">हेल्पलाइन स्टाफ का मानदेय</li>
                                <li class="mb-2">दिवंगत सदस्य के घर स्थानीय सत्यापन</li>
                                <li class="mb-2">प्रचार-प्रसार एवं सदस्यता अभियान</li>
                                <li class="mb-2">नई तकनीक का विकास</li>
                                <li class="mb-2">उत्तर प्रदेश के जरूरतमंद व्यक्तियों/छात्रों की सहायता</li>
                            </ul>

                            <p class="text-secondary lh-lg mb-3"><strong>भविष्य की योजनाएं (नोट):</strong></p>
                            <ul class="text-secondary lh-lg">
                                <li class="mb-2">सड़क दुर्घटना एवं गंभीर बीमारी इलाज योजना</li>
                                <li class="mb-2">छात्रों के लिए उच्च शिक्षा/तकनीकी शिक्षा/सिविल सेवा परीक्षा हेतु योजना</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goal Section -->
            <div class="row mt-5">
                <div class="col-lg-12" data-aos="fade-up">
                    <div class="about-card bg-primary-soft">
                        <div class="text-center">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h4 class="fw-bold mb-4">हमारा प्रतिबद्धता</h4>
                            <p class="text-secondary lh-lg lead">
                                BRCT Bharat Trust सभी सदस्यों के साथ पारदर्शिता, न्याय और सहयोग के साथ व्यवहार करने के लिए प्रतिबद्ध है।<br>
                                हम समाज में विश्वास और संवेदनशीलता को बढ़ावा देते हैं तथा कठिन समय में सभी को समान सहायता प्रदान करते हैं।
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
