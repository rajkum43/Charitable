<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
<nav class="navbar navbar-expand-lg shadow-sm <?php echo isset($navbar_sticky) && $navbar_sticky === false ? '' : 'static'; ?> ">
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
    <a class="navbar-brand" href="<?php echo $root_path; ?>index.php">
            <img src="<?php echo $root_path; ?>assets/images/logo/logo.png" class="d-inline-block logo align-text-top" alt="BRCT Bharat Trust Logo" height="210" style="object-fit: contain;">
        </a>
        <h1 class="navbar-title fw-bold text-wrap">BRCT Bharat Trust</h1>
    <div class="container container-bg">
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse"  id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Beti Vivah Sahyog Suchi
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_sahyog_suchi_all.php">All Sahyog Suchi</a></li>
                         <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/ac-holder-betvivah-suchi.php">Account Holder Wise</a></li>
                        <!-- <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_suchi_alert.php">Alert Wise</a></li> -->
                    </ul>
                </li>
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Death Sahyog Suchi
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/death_sahyog_suchi_all.php">All Sahyog Suchi</a></li>
                        <!-- <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_suchi_account.php">Account Holder Wise</a></li>
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_suchi_alert.php">Alert Wise</a></li> -->
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root_path; ?>pages/niyamavali-full.php">Niyamavali</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root_path; ?>pages/members-directory.php">Member List</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Sahyog
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_aavedan.php">Beti Vivah Sahyog</a></li>
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/death_aavedan.php">Death Sahyog</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Aavedan List
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_list.php">Beti Vivah List</a></li>
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/death_claims_list.php">Death Claims List</a></li>
                    </ul>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link active" href="<?php echo $root_path; ?>index.php">Home</a>
                </li> -->
                <!-- <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root_path; ?>pages/about.php">About Trust</a>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Sahyog Aavedan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/beti_vivah_aavedan.php">Beti Vivah Aavedan</a></li>
                        <li><a class="dropdown-item" href="<?php echo $root_path; ?>pages/death-claim.php">Death Aavedan</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root_path; ?>pages/register.php">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $root_path; ?>pages/login.php">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>