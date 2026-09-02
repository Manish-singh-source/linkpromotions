<?php
$lp_request_path = $_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_NAME'] ?? '';
$lp_current_page = basename(parse_url($lp_request_path, PHP_URL_PATH));
if ($lp_current_page === '' || $lp_current_page === 'index.html') {
    $lp_current_page = 'index.php';
}
?>
    <!-- Cursor Animation -->
    <div class="circle"></div>
    <div class="circle-follow"></div>

    <!-- Preloader -->
    <!-- <div class="preloader"></div> -->
    
    <!-- Main Header-->
    <header class="main-header header-style-one lp-header">
        <div class="lp-topbar">
            <div class="auto-container">
                <ul class="lp-topbar-info">
                    <li><i class="fa fa-map-marker-alt"></i> Mumbai, Maharashtra, India</li>
                    <li><i class="fa fa-phone"></i> <a href="tel:+919769140669">+91 9769140669</a></li>
                    <li><i class="fa fa-envelope"></i> <a href="mailto:amar@linkpromotions.co.in">amar@linkpromotions.co.in</a></li>
                </ul>
                <div class="lp-topbar-swastik" aria-hidden="true">
                    <img src="images/myimage/swastik.png" alt="">
                </div>
                <div class="lp-topbar-associates">
                    Associates: Europe | UK | USA | South East Asia
                </div>
            </div>
        </div>
        
        <!-- Header Main Box -->
         <div class="auto-container">
             <div class="main-box">
                 <div class="logo-box">
                     <div class="logo"><a href="index.php"><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits" title="Link Promotions and Exhibits"></a></div>
                 </div>
                 
                 <div class="header-navbar">
                     <!--Nav Box-->
                    <div class="nav-outer">
                        <nav class="nav main-menu">
                            <ul class="navigation">
                                <li class="<?php echo $lp_current_page === 'index.php' ? 'current' : ''; ?>"><a href="index.php">Home</a></li>
                                <li class="<?php echo $lp_current_page === 'about.php' ? 'current' : ''; ?>"><a href="about.php">About Us</a></li>
                                <li class="<?php echo $lp_current_page === 'exhibition.php' ? 'current' : ''; ?>"><a href="exhibition.php">Exhibitions Worldwide</a></li>
                                <li class="<?php echo $lp_current_page === 'event.php' ? 'current' : ''; ?>"><a href="event.php">Events</a></li>
                                <li class="<?php echo $lp_current_page === 'contact.php' ? 'current' : ''; ?>"><a href="contact.php">Contact Us</a></li>
                            </ul>
                        </nav>
                    </div>
                    <!-- Main Menu End-->
    
                    <!-- Outer Box -->
                    <div class="outer-box">
           
                        <!-- Btn Box -->
                        <div class="btn-box">
                            <a href="proposal.php" class="theme-btn btn-style-one bg-orange lp-proposal-btn"><span class="btn-title">Get Proposal <i class="fa fa-arrow-right"></i></span></a>
                        </div>
        
                        <!-- Mobile Nav toggler -->
                        <div class="mobile-nav-toggler"><i class="fa fa-bars"></i></div>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Mobile Menu  -->
        <div class="mobile-menu">
            <div class="menu-backdrop"></div>
        
            <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
            <nav class="menu-box">
                <div class="upper-box">
                    <div class="logo-box">
                        <div class="nav-logo light"><a href="index.php"><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                        <div class="nav-logo dark"><a href="index.php"><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                    </div>
                    <div class="close-btn"><i class="icon fa fa-times"></i></div>
                </div>
        
                <ul class="navigation clearfix">
                    <!--Keep This Empty / Menu will come through Javascript-->
                </ul>
                <ul class="contact-list-one">
                    <li>
                        <i class="icon lnr-icon-phone-handset"></i>
                        <span class="title">Call Now</span>
                        <div class="text"><a href="tel:+919769140669">+91 9769140669</a></div>
                    </li>
                    <li>
                        <i class="icon lnr-icon-envelope1"></i>
                        <span class="title">Send Email</span>
                        <div class="text"><a href="mailto:amar@linkpromotions.co.in">amar@linkpromotions.co.in</a></div>
                    </li>
                    <li>
                        <i class="icon lnr-icon-map-marker"></i>
                        <span class="title">Address</span>
                        <div class="text">Mumbai, Maharashtra, India</div>
                    </li>
                </ul>
        
        
                <ul class="social-links">
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-pinterest"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </nav>
        </div><!-- End Mobile Menu -->

        <!-- Sticky Header  -->
        <div class="sticky-header">
            <div class="auto-container">
                <div class="inner-container">
                    <!--Logo-->
                    <div class="logo-box">
                        <div class="logo light"><a href="index.php" title=""><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                        <div class="logo dark"><a href="index.php" title=""><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                    </div>
        
                    <!--Right Col-->
                    <div class="nav-outer">
                        <!-- Main Menu -->
                        <nav class="main-menu">
                            <div class="navbar-collapse show collapse clearfix">
                                <ul class="navigation clearfix">
                                    <!--Keep This Empty / Menu will come through Javascript-->
                                </ul>
                            </div>
                        </nav><!-- Main Menu End-->

                        <div class="btn-box lp-sticky-proposal">
                            <a href="proposal.php" class="theme-btn btn-style-one bg-orange lp-proposal-btn"><span class="btn-title">Get Proposal <i class="fa fa-arrow-right"></i></span></a>
                        </div>
        
                        <!--Mobile Navigation Toggler-->
                        <div class="mobile-nav-toggler"><span class="icon lnr-icon-bars"></span></div>
                    </div>
                </div>
            </div>
        </div><!-- End Sticky Menu -->
    </header>
    <!--End Main Header -->
    
    <!-- Hidden bar back drop -->
	<div class="hidden-bar-back-drop"></div>

	<!-- Hidden Bar -->
	<section class="hidden-bar">
		<div class="inner-box">
			<div class="upper-box">
                <div class="logo-box">
                    <div class="nav-logo light"><a href="index.php"><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                    <div class="nav-logo dark"><a href="index.php"><img src="images/myimage/logo.png" alt="Link Promotions and Exhibits"></a></div>
                </div>
				<div class="close-btn"><i class="icon fa fa-times"></i></div>
			</div>

			<div class="text-box">
				<h4 class="title">Custom exhibition stands, events and commercial spaces</h4>
				<div class="text">Link Promotions and Exhibits helps B2B and B2C brands plan, design, fabricate and execute impactful brand experiences across India and worldwide.</div>
			</div>

			<ul class="contact-list-one">
				<li>
					<i class="icon lnr-icon-phone-handset"></i>
					<span class="title">Call Now</span>
					<div class="text"><a href="tel:+919769140669">+91 9769140669</a></div>
				</li>
				<li>
					<i class="icon lnr-icon-envelope1"></i>
					<span class="title">Send Email</span>
					<div class="text"><a href="mailto:amar@linkpromotions.co.in">amar@linkpromotions.co.in</a></div>
				</li>
				<li>
					<i class="icon lnr-icon-map-marker"></i>
					<span class="title">Address</span>
					<div class="text">Ghanshyam Enclave, Kandivali West,<br>Mumbai - 400067, India.</div>
				</li>
			</ul>

			<ul class="social-links">
				<li><a href="#"><i class="fab fa-twitter"></i></a></li>
				<li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
				<li><a href="#"><i class="fab fa-pinterest"></i></a></li>
				<li><a href="#"><i class="fab fa-instagram"></i></a></li>
			</ul>
		</div>
	</section>
	<!--End Hidden Bar -->
