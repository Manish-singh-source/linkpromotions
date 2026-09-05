<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
require __DIR__ . '/admin/config.php';

$portfolio_items = [];
$result = $conn->query('SELECT title, image_path FROM portfolio_items WHERE is_active = 1 ORDER BY display_order ASC, id ASC');
$portfolio_items = $result->fetch_all(MYSQLI_ASSOC);
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
                <h2>More than 1800 Projects <br> Completed Worldwide</h2>
            </div>

            <div class="lp-portfolio-grid">
                <?php foreach ($portfolio_items as $index => $item) : ?>
                    <?php
                    $image = (string)$item['image_path'];
                    $title = trim((string)($item['title'] ?? '')) ?: 'Portfolio ' . ($index + 1);
                    ?>
                    <a class="lp-portfolio-item" href="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" data-fancybox="portfolio-gallery" data-caption="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="lp-portfolio-zoom"><i class="fa fa-search-plus"></i></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- End Portfolio Gallery Section -->

<?php include 'foter.php'; ?>
