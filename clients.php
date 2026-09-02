<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

    <!-- Page Title -->
    <section class="page-title lp-contact-title lp-clients-page-title">
        <div class="auto-container">
            <div class="inner-container">
                <h1 class="title">Our Clients</h1>
            </div>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Clients Section -->
    <section class="lp-clients-page-section">
        <div class="auto-container">
            <div class="lp-clients-intro">
                <span class="lp-section-tag">Trusted By Leading Brands</span>
                <h2>Brands that choose Link Promotions and Exhibits</h2>
                <p>From exhibitions and launches to branded commercial spaces, we work with clients across diverse industries and geographies.</p>
            </div>

            <div class="lp-clients-grid">
                <?php for ($client = 1; $client <= 20; $client++) : ?>
                    <article class="lp-client-logo-card">
                        <img src="images/myimage/c<?php echo $client; ?>.png" alt="Client logo <?php echo $client; ?>">
                    </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <!-- End Clients Section -->

   

<?php include 'foter.php'; ?>
