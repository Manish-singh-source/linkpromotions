<?php
declare(strict_types=1);

$sessionPath = __DIR__ . '/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);
session_start();
require __DIR__ . '/config.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function countRows(mysqli $conn, string $table): int
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM {$table}");
    return (int)($result->fetch_assoc()['total'] ?? 0);
}

function redirectToPortfolio(): void
{
    header('Location: ?view=portfolios');
    exit;
}

function redirectToView(string $view): void
{
    header('Location: ?view=' . urlencode($view));
    exit;
}

function deleteRowsByIds(mysqli $conn, string $table, array $ids): int
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id): bool {
        return $id > 0;
    })));

    if (!$ids) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("DELETE FROM {$table} WHERE id IN ({$placeholders})");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    return max(0, $deleted);
}

function deletePortfolioItems(mysqli $conn, array $ids): int
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function (int $id): bool {
        return $id > 0;
    })));

    if (!$ids) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT image_path FROM portfolio_items WHERE id IN ({$placeholders})");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $deleted = deleteRowsByIds($conn, 'portfolio_items', $ids);

    foreach ($rows as $row) {
        $imagePath = (string)($row['image_path'] ?? '');
        if (strpos($imagePath, 'images/portfolio/') === 0) {
            $absolutePath = dirname(__DIR__) . '/' . $imagePath;
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    return $deleted;
}

function uploadPortfolioImage(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please choose a valid image.');
    }

    if ((int)$file['size'] > 6 * 1024 * 1024) {
        throw new RuntimeException('Image size must be under 6 MB.');
    }

    $tmpName = (string)$file['tmp_name'];
    $imageInfo = getimagesize($tmpName);
    if ($imageInfo === false) {
        throw new RuntimeException('Only image files are allowed.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mimeType = (string)($imageInfo['mime'] ?? '');
    if (!isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    $uploadDir = dirname(__DIR__) . '/images/portfolio';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileName = 'portfolio-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedMimeTypes[$mimeType];
    $targetPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    return 'images/portfolio/' . $fileName;
}

$mode = $_GET['action'] ?? 'login';
$mode = $mode === 'register' ? 'register' : 'login';
$view = $_GET['view'] ?? 'dashboard';
$view = in_array($view, ['dashboard', 'contacts', 'proposals', 'portfolios'], true) ? $view : 'dashboard';
$message = '';
$error = '';
$adminUser = $_SESSION['admin_user'] ?? null;

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? 'login';

    if ($adminUser && $postAction === 'portfolio_add') {
        try {
            $title = trim((string)($_POST['title'] ?? ''));
            $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));
            $imagePath = uploadPortfolioImage($_FILES['image'] ?? []);
            $stmt = $conn->prepare('INSERT INTO portfolio_items (title, image_path, display_order, is_active) VALUES (?, ?, ?, 1)');
            $dbTitle = $title !== '' ? $title : null;
            $stmt->bind_param('ssi', $dbTitle, $imagePath, $displayOrder);
            $stmt->execute();
            $stmt->close();
            $_SESSION['admin_flash'] = 'Portfolio image uploaded.';
        } catch (Throwable $e) {
            $_SESSION['admin_error'] = $e->getMessage();
        }
        redirectToPortfolio();
    } elseif ($adminUser && $postAction === 'portfolio_update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $displayOrder = max(0, (int)($_POST['display_order'] ?? 0));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0) {
            $dbTitle = $title !== '' ? $title : null;
            $stmt = $conn->prepare('UPDATE portfolio_items SET title = ?, display_order = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('siii', $dbTitle, $displayOrder, $isActive, $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['admin_flash'] = 'Portfolio item updated.';
        }
        redirectToPortfolio();
    } elseif ($adminUser && $postAction === 'portfolio_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            deletePortfolioItems($conn, [$id]);
            $_SESSION['admin_flash'] = 'Portfolio item deleted.';
        }
        redirectToPortfolio();
    } elseif ($adminUser && $postAction === 'portfolio_bulk_delete') {
        $deleted = deletePortfolioItems($conn, $_POST['ids'] ?? []);
        $_SESSION[$deleted > 0 ? 'admin_flash' : 'admin_error'] = $deleted > 0 ? "{$deleted} portfolio item(s) deleted." : 'Please select at least one portfolio item.';
        redirectToPortfolio();
    } elseif ($adminUser && $postAction === 'contact_delete') {
        $deleted = deleteRowsByIds($conn, 'contact_inquiries', [(int)($_POST['id'] ?? 0)]);
        $_SESSION[$deleted > 0 ? 'admin_flash' : 'admin_error'] = $deleted > 0 ? 'Contact inquiry deleted.' : 'Contact inquiry not found.';
        redirectToView('contacts');
    } elseif ($adminUser && $postAction === 'contact_bulk_delete') {
        $deleted = deleteRowsByIds($conn, 'contact_inquiries', $_POST['ids'] ?? []);
        $_SESSION[$deleted > 0 ? 'admin_flash' : 'admin_error'] = $deleted > 0 ? "{$deleted} contact inquiry item(s) deleted." : 'Please select at least one contact inquiry.';
        redirectToView('contacts');
    } elseif ($adminUser && $postAction === 'proposal_delete') {
        $deleted = deleteRowsByIds($conn, 'proposal_requests', [(int)($_POST['id'] ?? 0)]);
        $_SESSION[$deleted > 0 ? 'admin_flash' : 'admin_error'] = $deleted > 0 ? 'Proposal request deleted.' : 'Proposal request not found.';
        redirectToView('proposals');
    } elseif ($adminUser && $postAction === 'proposal_bulk_delete') {
        $deleted = deleteRowsByIds($conn, 'proposal_requests', $_POST['ids'] ?? []);
        $_SESSION[$deleted > 0 ? 'admin_flash' : 'admin_error'] = $deleted > 0 ? "{$deleted} proposal request item(s) deleted." : 'Please select at least one proposal request.';
        redirectToView('proposals');
    } elseif ($postAction === 'register') {
        $mode = 'register';
        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Please fill all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            $stmt = $conn->prepare('SELECT id FROM admin_users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $error = 'This email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $name, $email, $hash);
                $stmt->execute();
                $stmt->close();
                $message = 'Account created. Please sign in.';
                $mode = 'login';
            }
        }
    } else {
        $mode = 'login';
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Please enter email and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, email, password FROM admin_users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ];
                header('Location: ./');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
}

if (isset($_SESSION['admin_flash'])) {
    $message = (string)$_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}
if (isset($_SESSION['admin_error'])) {
    $error = (string)$_SESSION['admin_error'];
    unset($_SESSION['admin_error']);
}

$contactCount = 0;
$proposalCount = 0;
$portfolioCount = 0;
$contactRows = [];
$proposalRows = [];
$portfolioRows = [];

if ($adminUser) {
    $contactCount = countRows($conn, 'contact_inquiries');
    $proposalCount = countRows($conn, 'proposal_requests');
    $portfolioCount = countRows($conn, 'portfolio_items');

    if ($view === 'contacts') {
        $result = $conn->query('SELECT * FROM contact_inquiries ORDER BY created_at DESC, id DESC');
        $contactRows = $result->fetch_all(MYSQLI_ASSOC);
    }

    if ($view === 'proposals') {
        $result = $conn->query('SELECT * FROM proposal_requests ORDER BY created_at DESC, id DESC');
        $proposalRows = $result->fetch_all(MYSQLI_ASSOC);
    }

    if ($view === 'portfolios') {
        $result = $conn->query('SELECT * FROM portfolio_items ORDER BY display_order ASC, id ASC');
        $portfolioRows = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel | Link Promotions</title>
    <?php if ($adminUser): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <?php endif; ?>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #24262c;
            background: #edf0f3;
        }
        a { color: inherit; text-decoration: none; }
        .login-page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 24px;
        }
        .login-box {
            width: 100%;
            max-width: 430px;
            padding: 34px;
            border: 1px solid rgba(36, 38, 44, .12);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(36, 38, 44, .12);
        }
        .auth-logo {
            max-width: 170px;
            margin-bottom: 24px;
        }
        .brand span,
        .eyebrow {
            display: block;
            color: #f05a00;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }
        h1, h2, h3 { margin: 0; line-height: 1.15; }
        .login-box h1 { margin-top: 6px; font-size: 30px; }
        p {
            margin: 10px 0 24px;
            color: #5d626b;
            line-height: 1.55;
        }
        label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 700;
        }
        input,
        textarea {
            width: 100%;
            margin-bottom: 16px;
            padding: 0 14px;
            border: 1px solid #cfd3d8;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
        }
        input { height: 48px; }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
        }
        textarea {
            min-height: 86px;
            padding-top: 12px;
            resize: vertical;
        }
        input:focus,
        textarea:focus {
            border-color: #f05a00;
            box-shadow: 0 0 0 3px rgba(240, 90, 0, .12);
        }
        button,
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border: 0;
            border-radius: 6px;
            color: #fff;
            background: #f05a00;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }
        .login-box button { width: 100%; }
        button:hover,
        .btn:hover { background: #24262c; color: #fff; }
        .btn.danger { background: #b42318; }
        .btn.danger:hover { background: #24262c; }
        .btn.small {
            min-height: 38px;
            padding: 0 12px;
            font-size: 13px;
        }
        .switch-link {
            margin-top: 18px;
            color: #5d626b;
            text-align: center;
        }
        .switch-link a { color: #f05a00; font-weight: 700; }
        .alert {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.45;
        }
        .alert.error { color: #8a1f11; background: #ffe6df; }
        .alert.success { color: #1f6a38; background: #e2f5e8; }
        .admin-shell {
            display: grid;
            min-height: 100vh;
            grid-template-columns: 270px minmax(0, 1fr);
        }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 26px 20px;
            color: #fff;
            background: #24262c;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            min-height: 78px;
            margin-bottom: 26px;
            padding: 14px;
            border-radius: 8px;
            background: #fff;
        }
        .sidebar-logo img { width: 100%; height: auto; }
        .nav-label {
            margin: 0 0 12px 6px;
            color: rgba(255, 255, 255, .54);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .side-nav {
            display: grid;
            gap: 8px;
        }
        .side-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            border-radius: 8px;
            color: rgba(255, 255, 255, .78);
            font-weight: 700;
        }
        .side-nav a.active,
        .side-nav a:hover {
            color: #fff;
            background: #f05a00;
        }
        .side-nav i {
            display: grid;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: rgba(255, 255, 255, .12);
            place-items: center;
            font-style: normal;
        }
        .main { min-width: 0; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            min-height: 82px;
            padding: 0 34px;
            border-bottom: 1px solid #dfe3e8;
            background: #fff;
        }
        .topbar h1 { font-size: 24px; }
        .admin-user {
            display: flex;
            align-items: center;
            gap: 14px;
            color: #5d626b;
            font-weight: 700;
        }
        .admin-user span {
            display: grid;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: #fff;
            background: #24262c;
            place-items: center;
        }
        .content { padding: 34px; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 26px;
        }
        .stat-card,
        .panel {
            border: 1px solid #dfe3e8;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 30px rgba(36, 38, 44, .06);
        }
        .stat-card { padding: 24px; }
        .stat-card strong {
            display: block;
            margin-bottom: 12px;
            color: #6b7079;
            font-size: 14px;
        }
        .stat-card span {
            display: block;
            color: #24262c;
            font-size: 38px;
            font-weight: 800;
        }
        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid #edf0f3;
        }
        .panel-head h2 { font-size: 22px; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            padding: 24px;
        }
        .form-grid .wide { grid-column: span 2; }
        .form-actions {
            display: flex;
            align-items: flex-end;
        }
        .bulk-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 24px;
            border-bottom: 1px solid #edf0f3;
            background: #fbfcfd;
        }
        .bulk-actions span {
            color: #6b7079;
            font-size: 14px;
            font-weight: 700;
        }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px 18px;
            border-bottom: 1px solid #edf0f3;
            text-align: left;
            vertical-align: top;
            font-size: 14px;
            line-height: 1.45;
        }
        th {
            color: #6b7079;
            background: #f8f9fb;
            font-size: 12px;
            text-transform: uppercase;
        }
        td.message {
            min-width: 260px;
            color: #5d626b;
        }
        .select-col {
            width: 46px;
            min-width: 46px;
            text-align: center;
        }
        .action-col {
            width: 110px;
            min-width: 110px;
        }
        .thumb {
            width: 112px;
            height: 76px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dfe3e8;
            background: #f8f9fb;
        }
        .inline-form {
            display: grid;
            grid-template-columns: minmax(160px, 1fr) 92px 96px auto auto;
            align-items: center;
            gap: 10px;
            min-width: 580px;
        }
        .inline-form input { margin: 0; }
        .check-field {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: #5d626b;
            font-weight: 700;
            white-space: nowrap;
        }
        .dataTables_wrapper,
        .dt-container {
            padding: 18px 24px 24px;
        }
        .dt-container .dt-search input,
        .dt-container .dt-length select {
            margin: 0 0 0 8px;
            border: 1px solid #cfd3d8;
            border-radius: 6px;
            outline: none;
        }
        .dt-container .dt-search input {
            width: auto;
            height: 38px;
            padding: 0 12px;
        }
        .dt-container .dt-length select {
            height: 38px;
            padding: 0 8px;
        }
        .dt-container .dt-paging .dt-paging-button.current,
        .dt-container .dt-paging .dt-paging-button.current:hover {
            color: #fff !important;
            border-color: #f05a00 !important;
            background: #f05a00 !important;
        }
        .empty {
            padding: 42px 24px;
            color: #6b7079;
            text-align: center;
        }
        @media (max-width: 991px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar {
                position: relative;
                height: auto;
            }
            .sidebar-logo { max-width: 220px; }
            .stat-grid { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .wide { grid-column: auto; }
            .bulk-actions { align-items: flex-start; flex-direction: column; }
            .topbar,
            .content { padding-left: 20px; padding-right: 20px; }
        }
        @media (max-width: 575px) {
            .login-box { padding: 24px; }
            .topbar { align-items: flex-start; flex-direction: column; padding-top: 18px; padding-bottom: 18px; }
            .admin-user { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <?php if ($adminUser): ?>
        <div class="admin-shell">
            <aside class="sidebar">
                <a class="sidebar-logo" href="./">
                    <img src="../images/myimage/logo.png" alt="Link Promotions">
                </a>
                <div class="nav-label">Menu</div>
                <nav class="side-nav">
                    <a class="<?php echo $view === 'dashboard' ? 'active' : ''; ?>" href="./"><i>D</i>Dashboard</a>
                    <a class="<?php echo $view === 'contacts' ? 'active' : ''; ?>" href="?view=contacts"><i>C</i>Contact Inquiry</a>
                    <a class="<?php echo $view === 'proposals' ? 'active' : ''; ?>" href="?view=proposals"><i>P</i>Proposal</a>
                    <a class="<?php echo $view === 'portfolios' ? 'active' : ''; ?>" href="?view=portfolios"><i>I</i>Portfolio</a>
                </nav>
            </aside>
            <main class="main">
                <header class="topbar">
                    <div>
                        <span class="eyebrow">Admin Panel</span>
                        <h1><?php echo $view === 'contacts' ? 'Contact Inquiry' : ($view === 'proposals' ? 'Proposal Requests' : ($view === 'portfolios' ? 'Portfolio' : 'Dashboard')); ?></h1>
                    </div>
                    <div class="admin-user">
                        <span><?php echo h(strtoupper(substr((string)$adminUser['name'], 0, 1))); ?></span>
                        <?php echo h((string)$adminUser['name']); ?>
                        <a class="btn" href="?logout=1">Logout</a>
                    </div>
                </header>
                <section class="content">
                    <?php if ($error): ?>
                        <div class="alert error"><?php echo h($error); ?></div>
                    <?php endif; ?>
                    <?php if ($message): ?>
                        <div class="alert success"><?php echo h($message); ?></div>
                    <?php endif; ?>
                    <?php if ($view === 'dashboard'): ?>
                        <div class="stat-grid">
                            <a class="stat-card" href="?view=contacts">
                                <strong>Contact Inquiry</strong>
                                <span><?php echo $contactCount; ?></span>
                            </a>
                            <a class="stat-card" href="?view=proposals">
                                <strong>Proposal Requests</strong>
                                <span><?php echo $proposalCount; ?></span>
                            </a>
                            <a class="stat-card" href="?view=portfolios">
                                <strong>Portfolio Images</strong>
                                <span><?php echo $portfolioCount; ?></span>
                            </a>
                        </div>
                        <div class="panel">
                            <div class="panel-head"><h2>Welcome</h2></div>
                            <div class="empty">Use the sidebar to manage leads and portfolio images.</div>
                        </div>
                    <?php elseif ($view === 'contacts'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Contact Inquiry</h2>
                                <strong><?php echo $contactCount; ?> total</strong>
                            </div>
                            <?php if (!$contactRows): ?>
                                <div class="empty">No contact inquiries yet.</div>
                            <?php else: ?>
                                <form id="contacts-bulk-form" method="post" action="" onsubmit="return confirmBulkDelete(this, 'contact inquiries');">
                                    <input type="hidden" name="action" value="contact_bulk_delete">
                                </form>
                                <div class="bulk-actions">
                                    <span>Select rows and delete multiple contact inquiries.</span>
                                    <button class="btn danger small" type="submit" form="contacts-bulk-form">Delete Selected</button>
                                </div>
                                <div class="table-wrap">
                                    <table class="datatable">
                                        <thead>
                                            <tr>
                                                <th class="select-col"><input class="select-all" type="checkbox" data-target="contacts-bulk-form" aria-label="Select all contact inquiries"></th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Show Name</th>
                                                <th>Message</th>
                                                <th>Date</th>
                                                <th class="action-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contactRows as $row): ?>
                                                <tr>
                                                    <td class="select-col"><input class="row-select" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>" form="contacts-bulk-form" aria-label="Select contact inquiry <?php echo (int)$row['id']; ?>"></td>
                                                    <td><?php echo (int)$row['id']; ?></td>
                                                    <td><?php echo h($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                    <td><?php echo h($row['email']); ?></td>
                                                    <td><?php echo h($row['phone']); ?></td>
                                                    <td><?php echo h((string)$row['show_name']); ?></td>
                                                    <td class="message"><?php echo nl2br(h($row['message'])); ?></td>
                                                    <td><?php echo h($row['created_at']); ?></td>
                                                    <td class="action-col">
                                                        <form method="post" action="" onsubmit="return confirm('Delete this contact inquiry?');">
                                                            <input type="hidden" name="action" value="contact_delete">
                                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                            <button class="btn danger small" type="submit">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($view === 'proposals'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Proposal Requests</h2>
                                <strong><?php echo $proposalCount; ?> total</strong>
                            </div>
                            <?php if (!$proposalRows): ?>
                                <div class="empty">No proposal requests yet.</div>
                            <?php else: ?>
                                <form id="proposals-bulk-form" method="post" action="" onsubmit="return confirmBulkDelete(this, 'proposal requests');">
                                    <input type="hidden" name="action" value="proposal_bulk_delete">
                                </form>
                                <div class="bulk-actions">
                                    <span>Select rows and delete multiple proposal requests.</span>
                                    <button class="btn danger small" type="submit" form="proposals-bulk-form">Delete Selected</button>
                                </div>
                                <div class="table-wrap">
                                    <table class="datatable">
                                        <thead>
                                            <tr>
                                                <th class="select-col"><input class="select-all" type="checkbox" data-target="proposals-bulk-form" aria-label="Select all proposal requests"></th>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Company</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Show</th>
                                                <th>Location</th>
                                                <th>Stall Size</th>
                                                <th>Build Type</th>
                                                <th>Date/Budget</th>
                                                <th>Message</th>
                                                <th>Created</th>
                                                <th class="action-col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($proposalRows as $row): ?>
                                                <tr>
                                                    <td class="select-col"><input class="row-select" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>" form="proposals-bulk-form" aria-label="Select proposal request <?php echo (int)$row['id']; ?>"></td>
                                                    <td><?php echo (int)$row['id']; ?></td>
                                                    <td><?php echo h($row['name']); ?></td>
                                                    <td><?php echo h($row['company']); ?></td>
                                                    <td><?php echo h($row['email']); ?></td>
                                                    <td><?php echo h($row['phone']); ?></td>
                                                    <td><?php echo h($row['show_name']); ?></td>
                                                    <td><?php echo h($row['show_location']); ?></td>
                                                    <td><?php echo h($row['stall_size']); ?></td>
                                                    <td><?php echo h($row['build_type']); ?></td>
                                                    <td><?php echo h(trim((string)$row['show_date'] . ' ' . (string)$row['budget'])); ?></td>
                                                    <td class="message"><?php echo nl2br(h($row['message'])); ?></td>
                                                    <td><?php echo h($row['created_at']); ?></td>
                                                    <td class="action-col">
                                                        <form method="post" action="" onsubmit="return confirm('Delete this proposal request?');">
                                                            <input type="hidden" name="action" value="proposal_delete">
                                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                            <button class="btn danger small" type="submit">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Portfolio Images</h2>
                                <strong><?php echo $portfolioCount; ?> total</strong>
                            </div>
                            <form class="form-grid" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="portfolio_add">
                                <div class="wide">
                                    <label for="portfolio_title">Title</label>
                                    <input id="portfolio_title" name="title" type="text" placeholder="Portfolio title">
                                </div>
                                <div>
                                    <label for="portfolio_order">Order</label>
                                    <input id="portfolio_order" name="display_order" type="number" min="0" value="<?php echo $portfolioCount + 1; ?>">
                                </div>
                                <div>
                                    <label for="portfolio_image">Image</label>
                                    <input id="portfolio_image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit">Upload Image</button>
                                </div>
                            </form>
                            <?php if (!$portfolioRows): ?>
                                <div class="empty">No portfolio images yet.</div>
                            <?php else: ?>
                                <form id="portfolios-bulk-form" method="post" action="" onsubmit="return confirmBulkDelete(this, 'portfolio images');">
                                    <input type="hidden" name="action" value="portfolio_bulk_delete">
                                </form>
                                <div class="bulk-actions">
                                    <span>Select rows and delete multiple portfolio images.</span>
                                    <button class="btn danger small" type="submit" form="portfolios-bulk-form">Delete Selected</button>
                                </div>
                                <div class="table-wrap">
                                    <table class="datatable">
                                        <thead>
                                            <tr>
                                                <th class="select-col"><input class="select-all" type="checkbox" data-target="portfolios-bulk-form" aria-label="Select all portfolio images"></th>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Path</th>
                                                <th>Manage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($portfolioRows as $row): ?>
                                                <tr>
                                                    <td class="select-col"><input class="row-select" type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>" form="portfolios-bulk-form" aria-label="Select portfolio image <?php echo (int)$row['id']; ?>"></td>
                                                    <td><?php echo (int)$row['id']; ?></td>
                                                    <td><img class="thumb" src="../<?php echo h($row['image_path']); ?>" alt="<?php echo h((string)($row['title'] ?: 'Portfolio image')); ?>"></td>
                                                    <td>
                                                        <strong><?php echo h((string)($row['title'] ?: 'Untitled')); ?></strong><br>
                                                        <?php echo h($row['image_path']); ?>
                                                    </td>
                                                    <td>
                                                        <form class="inline-form" method="post" action="">
                                                            <input type="hidden" name="action" value="portfolio_update">
                                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                            <input name="title" type="text" value="<?php echo h((string)$row['title']); ?>" placeholder="Title">
                                                            <input name="display_order" type="number" min="0" value="<?php echo (int)$row['display_order']; ?>">
                                                            <label class="check-field">
                                                                <input name="is_active" type="checkbox" value="1" <?php echo (int)$row['is_active'] === 1 ? 'checked' : ''; ?>>
                                                                Active
                                                            </label>
                                                            <button class="btn small" type="submit">Save</button>
                                                        </form>
                                                        <form method="post" action="" onsubmit="return confirm('Delete this portfolio image?');" style="margin-top: 10px;">
                                                            <input type="hidden" name="action" value="portfolio_delete">
                                                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                            <button class="btn danger small" type="submit">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    <?php else: ?>
        <main class="login-page">
            <section class="login-box">
                <img class="auth-logo" src="../images/myimage/logo.png" alt="Link Promotions">
                <div class="brand">
                    <span>Link Promotions</span>
                    <h1><?php echo $mode === 'register' ? 'Create Admin Account' : 'Admin Sign In'; ?></h1>
                </div>
                <p><?php echo $mode === 'register' ? 'Register your admin access.' : 'Sign in to continue to the admin panel.'; ?></p>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo h($error); ?></div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="alert success"><?php echo h($message); ?></div>
                <?php endif; ?>

                <?php if ($mode === 'register'): ?>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="register">
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required>
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required>
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required>
                        <button type="submit">Sign Up</button>
                    </form>
                    <div class="switch-link">Already have an account? <a href="?action=login">Sign in</a></div>
                <?php else: ?>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="login">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" autocomplete="email" required>
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                        <button type="submit">Sign In</button>
                    </form>
                    <div class="switch-link">Need an account? <a href="?action=register">Create account</a></div>
                <?php endif; ?>
            </section>
        </main>
    <?php endif; ?>
    <?php if ($adminUser): ?>
        <script src="../js/jquery.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script>
            function confirmBulkDelete(form, label) {
                var selected = document.querySelectorAll('input[form="' + form.id + '"][name="ids[]"]:checked').length;
                if (selected === 0) {
                    alert('Please select at least one row.');
                    return false;
                }
                return confirm('Delete ' + selected + ' selected ' + label + '?');
            }

            $(function () {
                $('.datatable').each(function () {
                    var table = $(this).DataTable({
                        pageLength: 10,
                        order: [],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: [0, -1] }
                        ]
                    });

                    $(this).on('change', '.select-all', function () {
                        var checked = this.checked;
                        $(table.rows({ search: 'applied' }).nodes()).find('.row-select').prop('checked', checked);
                    });

                    $(this).on('change', '.row-select', function () {
                        var $table = $(this).closest('table');
                        var allRows = $table.find('.row-select').length;
                        var selectedRows = $table.find('.row-select:checked').length;
                        $table.find('.select-all').prop('checked', allRows > 0 && allRows === selectedRows);
                    });
                });
            });
        </script>
    <?php endif; ?>
</body>
</html>
