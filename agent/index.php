<?php
/**
 * Agent Portal Entry Point / Routing Wrapper
 */
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Jakarta");
require_once('../database/database.php');

$page = $_GET['page'] ?? 'dashboard';

// Exclude login/logout from layout wrapper
if ($page === 'login') {
    include('login.php');
    exit;
}
if ($page === 'logout') {
    include('logout.php');
    exit;
}

// Session check
if (!isset($_SESSION['agent'])) {
    header('Location: ?page=login');
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare('SELECT * FROM agents WHERE id = ? AND is_active = 1');
$stmt->execute([$_SESSION['agent']['id']]);
$agent_data = $stmt->fetch();

if (!$agent_data) {
    header('Location: ?page=logout');
    exit;
}
$_SESSION['agent'] = $agent_data;
$agent = clone (object)$agent_data; // for easy access in views

// Load global Mikhmon theme
include('../include/theme.php');
if (empty($theme)) $theme = 'light';

?>
<!DOCTYPE html>
<html>
<head>
    <title>MIKHMON Agent Portal</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- Mikhmon Core CSS -->
    <link rel="stylesheet" href="../css/mikhmon-ui.<?= $theme ?>.min.css">
    <link rel="stylesheet" href="../css/font-awesome/css/font-awesome.min.css">
    <script src="../js/jquery.min.js"></script>
    <!-- PWA -->
    <link rel="manifest" href="./manifest.json">
    <link rel="apple-touch-icon" href="../img/icon-192.png">
    <link rel="icon" href="../img/favicon.png">
    <meta name="theme-color" content="<?= $themecolor ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; padding: 0;
            overflow-x: hidden;
        }
        
        /* Top Navigation Header */
        .agent-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: <?= $themecolor ?>;
            color: #fff;
            padding: 0 30px;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .topbar-brand i { margin-right: 8px; }
        .agent-username-badge {
            font-size: 12px;
            color: #888;
            margin-left: 10px;
            font-weight: normal;
        }

        /* Nav Links */
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-item {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            transition: 0.2s;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .nav-item i { margin-right: 6px; }
        .nav-item:hover { color: #fff; }
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: bold;
        }
        
        /* Main Container */
        #main-content {
            padding: 20px 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .agent-topbar { flex-direction: row; height: 60px; padding: 0 15px; justify-content: flex-start; }
            .topbar-brand { font-size: 16px; margin-right: auto; }
            .agent-username-badge { display: none; } /* Hide on small screens to save space */
            
            .topbar-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background: <?= $themecolor ?>;
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: space-around;
                align-items: center;
                margin: 0;
                padding: 10px 0;
                z-index: 1000;
                box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
            }
            .nav-item {
                flex-direction: column;
                font-size: 11px;
                padding: 5px;
                gap: 4px;
            }
            .nav-item i { margin-right: 0; font-size: 18px; }
            #main-content { padding: 15px; padding-bottom: 80px; } /* Space for bottom nav */
        }
    </style>
</head>
<body>

<!-- TOP HEADER NAVBAR -->
<div class="agent-topbar">
    <div class="topbar-brand">
        <i class="fa fa-users"></i> MIKHMON Agent 
        <span class="agent-username-badge"><?= htmlspecialchars($agent->name) ?> (<?= htmlspecialchars($agent->username) ?>)</span>
    </div>
    
    <div class="topbar-nav">
        <a href="?page=dashboard" class="nav-item <?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fa fa-dashboard"></i> Dashboard
        </a>
        <a href="?page=vouchers" class="nav-item <?= $page == 'vouchers' ? 'active' : '' ?>">
            <i class="fa fa-ticket"></i> Vouchers
        </a>
        <a href="?page=history" class="nav-item <?= $page == 'history' ? 'active' : '' ?>">
            <i class="fa fa-history"></i> Transaksi
        </a>
        <a href="?page=logout" class="nav-item">
            <i class="fa fa-sign-out"></i> Logout
        </a>
    </div>
</div>

<!-- MAIN CONTENT CONTAINER -->
<div id="main-content">
    <?php
    // Router to load content
    switch ($page) {
        case 'dashboard':
            include('dashboard.php');
            break;
        case 'generate':
            include('generate.php');
            break;
        case 'vouchers':
            include('vouchers.php');
            break;
        case 'generate_history':
            include('generate_history.php');
            break;
        case 'history':
            include('history.php');
            break;
        default:
            include('dashboard.php');
            break;
    }
    ?>
</div>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('./sw.js').catch(function(){});
}
</script>
</body>
</html>
