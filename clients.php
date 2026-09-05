<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
$clientLogoFiles = glob('images/myimage/client-logo/*.{jpg,jpeg,png,webp,gif,svg}', GLOB_BRACE);

sort($clientLogoFiles, SORT_NATURAL | SORT_FLAG_CASE);
?>

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
                <?php foreach ($clientLogoFiles as $clientLogoFile) : ?>
                    <?php
                    $clientLogoName = pathinfo($clientLogoFile, PATHINFO_FILENAME);
                    $clientLogoName = preg_replace('/^\d+_/', '', $clientLogoName);
                    $clientLogoName = str_replace('_', ' ', $clientLogoName);
                    ?>
                    <article class="lp-client-logo-card">
                        <img src="<?php echo htmlspecialchars($clientLogoFile); ?>" alt="<?php echo htmlspecialchars($clientLogoName); ?> logo">
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- End Clients Section -->

   

<?php include 'foter.php'; ?>
