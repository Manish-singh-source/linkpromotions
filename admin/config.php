<?php
declare(strict_types=1);

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'LP';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset('utf8mb4');

    $conn->query("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS contact_inquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(120) NOT NULL,
            last_name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(60) NOT NULL,
            show_name VARCHAR(190) NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS proposal_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(160) NOT NULL,
            company VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(60) NOT NULL,
            show_name VARCHAR(190) NOT NULL,
            show_location VARCHAR(190) NOT NULL,
            stall_size VARCHAR(120) NOT NULL,
            build_type VARCHAR(80) NOT NULL,
            show_date VARCHAR(120) NULL,
            budget VARCHAR(120) NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS portfolio_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(190) NULL,
            image_path VARCHAR(255) NOT NULL,
            display_order INT UNSIGNED NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $portfolioCount = (int)($conn->query('SELECT COUNT(*) AS total FROM portfolio_items')->fetch_assoc()['total'] ?? 0);
    if ($portfolioCount === 0) {
        $rootPath = dirname(__DIR__);
        $portfolioImages = array_merge(
            glob($rootPath . '/images/myimage/ev*.jpg') ?: [],
            glob($rootPath . '/images/myimage/ev*.jpeg') ?: [],
            glob($rootPath . '/images/myimage/ev*.png') ?: [],
            glob($rootPath . '/images/myimage/ev*.webp') ?: []
        );

        usort($portfolioImages, static function (string $a, string $b): int {
            preg_match('/ev(\d+)/i', basename($a), $aMatch);
            preg_match('/ev(\d+)/i', basename($b), $bMatch);

            return ((int)($aMatch[1] ?? 0)) <=> ((int)($bMatch[1] ?? 0));
        });

        $stmt = $conn->prepare('INSERT INTO portfolio_items (title, image_path, display_order) VALUES (?, ?, ?)');
        foreach ($portfolioImages as $index => $image) {
            $title = 'Portfolio ' . ($index + 1);
            $relativePath = str_replace('\\', '/', substr($image, strlen($rootPath) + 1));
            $displayOrder = $index + 1;
            $stmt->bind_param('ssi', $title, $relativePath, $displayOrder);
            $stmt->execute();
        }
        $stmt->close();
    }
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database connection error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
