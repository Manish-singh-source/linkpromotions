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

$mode = $_GET['action'] ?? 'login';
$mode = $mode === 'register' ? 'register' : 'login';
$view = $_GET['view'] ?? 'dashboard';
$view = in_array($view, ['dashboard', 'contacts', 'proposals'], true) ? $view : 'dashboard';
$message = '';
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? 'login';

    if ($postAction === 'register') {
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

$adminUser = $_SESSION['admin_user'] ?? null;
$contactCount = 0;
$proposalCount = 0;
$contactRows = [];
$proposalRows = [];

if ($adminUser) {
    $contactCount = countRows($conn, 'contact_inquiries');
    $proposalCount = countRows($conn, 'proposal_requests');

    if ($view === 'contacts') {
        $result = $conn->query('SELECT * FROM contact_inquiries ORDER BY created_at DESC, id DESC');
        $contactRows = $result->fetch_all(MYSQLI_ASSOC);
    }

    if ($view === 'proposals') {
        $result = $conn->query('SELECT * FROM proposal_requests ORDER BY created_at DESC, id DESC');
        $proposalRows = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel | Link Promotions</title>
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
        input {
            width: 100%;
            height: 48px;
            margin-bottom: 16px;
            padding: 0 14px;
            border: 1px solid #cfd3d8;
            border-radius: 6px;
            font-size: 15px;
            outline: none;
        }
        input:focus {
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
                </nav>
            </aside>
            <main class="main">
                <header class="topbar">
                    <div>
                        <span class="eyebrow">Admin Panel</span>
                        <h1><?php echo $view === 'contacts' ? 'Contact Inquiry' : ($view === 'proposals' ? 'Proposal Requests' : 'Dashboard'); ?></h1>
                    </div>
                    <div class="admin-user">
                        <span><?php echo h(strtoupper(substr((string)$adminUser['name'], 0, 1))); ?></span>
                        <?php echo h((string)$adminUser['name']); ?>
                        <a class="btn" href="?logout=1">Logout</a>
                    </div>
                </header>
                <section class="content">
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
                            <div class="stat-card">
                                <strong>Total Leads</strong>
                                <span><?php echo $contactCount + $proposalCount; ?></span>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-head"><h2>Welcome</h2></div>
                            <div class="empty">Use the sidebar to view Contact Inquiry and Proposal data.</div>
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
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Show Name</th>
                                                <th>Message</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contactRows as $row): ?>
                                                <tr>
                                                    <td><?php echo (int)$row['id']; ?></td>
                                                    <td><?php echo h($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                    <td><?php echo h($row['email']); ?></td>
                                                    <td><?php echo h($row['phone']); ?></td>
                                                    <td><?php echo h((string)$row['show_name']); ?></td>
                                                    <td class="message"><?php echo nl2br(h($row['message'])); ?></td>
                                                    <td><?php echo h($row['created_at']); ?></td>
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
                                <h2>Proposal Requests</h2>
                                <strong><?php echo $proposalCount; ?> total</strong>
                            </div>
                            <?php if (!$proposalRows): ?>
                                <div class="empty">No proposal requests yet.</div>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($proposalRows as $row): ?>
                                                <tr>
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
</body>
</html>
