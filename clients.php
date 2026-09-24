<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
$clientLogoFiles = glob('images/myimage/client-logo/*.{jpg,jpeg,png,webp,gif,svg}', GLOB_BRACE);

sort($clientLogoFiles, SORT_NATURAL | SORT_FLAG_CASE);

$clientLogoFiles = array_values(array_filter($clientLogoFiles, function ($clientLogoFile) {
    return pathinfo($clientLogoFile, PATHINFO_FILENAME) !== '20_SANY';
}));

$featuredClientOrder = [
    '13_Adobe',
    '14_HP',
    '08_Swarovski_Gemstones',
    '06_Midea',
    'new-sany',
    '03_FORBES',
    '09_DYMO',
    '12_Titan_Laboratories',
    '05_BARNES_Molding_Solutions',
    '04_Bliss_GVS',
];

$featuredClientOrder = array_flip($featuredClientOrder);

usort($clientLogoFiles, function ($firstLogo, $secondLogo) use ($featuredClientOrder) {
    $firstName = pathinfo($firstLogo, PATHINFO_FILENAME);
    $secondName = pathinfo($secondLogo, PATHINFO_FILENAME);
    $firstRank = $featuredClientOrder[$firstName] ?? PHP_INT_MAX;
    $secondRank = $featuredClientOrder[$secondName] ?? PHP_INT_MAX;

    if ($firstRank === $secondRank) {
        return strnatcasecmp($firstLogo, $secondLogo);
    }

    return $firstRank <=> $secondRank;
});
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
