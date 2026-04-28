<?php
// Niyamavali Full (A4 Pages) - PHP Version
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>भारत रिलीफ चैरिटेबल टीम (BRCT) नियमावली (A4 स्वरूप)</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <link rel="stylesheet" href="../assets/css/niyamavali.css">
</head>
<body>
    <!-- Top Header -->
    <?php include '../components/top-header.php'; ?>
    
    <!-- Navbar -->
    <?php $navbar_sticky = false; include '../components/navbar.php'; ?>

    <!-- Main Niyamavali Content -->
    <div class="niyamavali-wrapper">
        <!-- यहाँ जावास्क्रिप्ट A4 पेज डालेगा -->
        <div id="pages-container"></div>
    </div>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/niyamavali.js"></script>

</body>
</html>