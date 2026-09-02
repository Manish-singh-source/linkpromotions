<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
$portfolio_images = array_merge(
    glob('images/myimage/ev*.jpg') ?: [],
    glob('images/myimage/ev*.jpeg') ?: [],
    glob('images/myimage/ev*.png') ?: [],
    glob('images/myimage/ev*.webp') ?: []
);

usort($portfolio_images, function ($a, $b) {
    preg_match('/ev(\d+)/i', basename($a), $a_match);
    preg_match('/ev(\d+)/i', basename($b), $b_match);

    return ((int) ($a_match[1] ?? 0)) <=> ((int) ($b_match[1] ?? 0));
});
?>

    <!-- Page Title -->
    <section class="page-title lp-contact-title lp-portfolio-title">
        <div class="auto-container">
            <div class="inner-container">
                <h1 class="title">Portfolio</h1>
            </div>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Portfolio Gallery Section -->
    <section class="lp-portfolio-section">
        <div class="auto-container">
            <div class="sec-title text-center">
                <span class="sub-title orange">Project Glimpses</span>
                <h2 class="text-reveal-anim">More than 1800 Projects <br> Completed Worldwide</h2>
            </div>

            <div class="lp-portfolio-grid">
                <?php foreach ($portfolio_images as $index => $image) : ?>
                    <a class="lp-portfolio-item" href="<?php echo htmlspecialchars($image); ?>" data-fancybox="portfolio-gallery" data-caption="Portfolio <?php echo $index + 1; ?>">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Portfolio project <?php echo $index + 1; ?>">
                        <span class="lp-portfolio-zoom"><i class="fa fa-search-plus"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- End Portfolio Gallery Section -->

<?php include 'foter.php'; ?>
