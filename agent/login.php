<?php
/**
 * Agent Login Page - Theme Aware
 * Automatically follows Mikhmon active theme (dark/light/blue/green/pink)
 */
if (isset($_SESSION['agent'])) {
    header('Location: ?page=dashboard');
    exit;
}

// Load theme settings
@include_once(__DIR__ . '/../include/theme.php');
$theme = $theme ?? 'dark';
$themecolor = $themecolor ?? '#3a4149';

// Define theme-specific palettes
$palettes = [
    'dark'  => ['bg' => '#353b41', 'card' => '#2b3136', 'header' => '#252a2f', 'border' => '#1c2126', 'input' => '#22262a', 'text' => '#fff',    'subtext' => '#e0e0e0', 'placeholder' => '#aaa', 'accent' => '#17a2b8'],
    'light' => ['bg' => '#e9ecef', 'card' => '#ffffff', 'header' => '#f4f6f8', 'border' => '#d1d5db', 'input' => '#f0f2f5', 'text' => '#333',    'subtext' => '#555',    'placeholder' => '#999', 'accent' => '#008BC9'],
    'blue'  => ['bg' => '#e3f0fa', 'card' => '#ffffff', 'header' => '#eaf4fc', 'border' => '#b8d4e8', 'input' => '#f0f6fb', 'text' => '#1a3a5c', 'subtext' => '#335577', 'placeholder' => '#8ab', 'accent' => '#008BC9'],
    'green' => ['bg' => '#e8f5e9', 'card' => '#ffffff', 'header' => '#f1f9f1', 'border' => '#b8dbb8', 'input' => '#f0f8f0', 'text' => '#1b5e20', 'subtext' => '#2e7d32', 'placeholder' => '#8b8', 'accent' => '#4dbd74'],
    'pink'  => ['bg' => '#fce4ec', 'card' => '#ffffff', 'header' => '#fdf0f4', 'border' => '#f0b8c8', 'input' => '#fdf5f8', 'text' => '#880e4f', 'subtext' => '#ad1457', 'placeholder' => '#c88', 'accent' => '#e83e8c'],
];

$p = $palettes[$theme] ?? $palettes['dark'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM agents WHERE username = ?");
        $stmt->execute([$username]);
        $agent = $stmt->fetch();
        
        if ($agent && password_verify($password, $agent['password'])) {
            if ($agent['is_active']) {
                $_SESSION['agent'] = $agent;
                header('Location: ?page=dashboard');
                exit;
            } else {
                $error = 'Akun anda dinonaktifkan. Hubungi admin.';
            }
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Silakan isi username dan password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agent Login - Mikhmon</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../css/font-awesome/css/font-awesome.min.css">
    <!-- PWA -->
    <link rel="manifest" href="./manifest.json">
    <link rel="apple-touch-icon" href="../img/icon-192.png">
    <link rel="icon" href="../img/favicon.png">
    <meta name="theme-color" content="<?= $p['accent'] ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { 
            background-color: <?= $p['bg'] ?>;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            color: <?= $p['text'] ?>;
        }
        
        .login-card { 
            width: 320px; 
            background: <?= $p['card'] ?>;
            border: 1px solid <?= $p['border'] ?>;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .login-header {
            background: <?= $p['header'] ?>;
            padding: 8px 15px;
            font-size: 13px;
            font-weight: bold;
            color: <?= $p['subtext'] ?>;
            border-bottom: 1px solid <?= $p['border'] ?>;
        }
        .login-header i { margin-right: 5px; }
        
        .login-body {
            padding: 25px 30px;
            text-align: center;
        }
        
        .logo-container {
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
        }
        .logo-box {
            width: 80px;
            height: 80px;
            background: <?= $p['accent'] ?>;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 -3px 0 rgba(0,0,0,0.1);
        }
        .m-logo {
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 4px;
            position: relative;
            z-index: 2;
        }
        .m-logo::after {
            content: '';
            position: absolute;
            bottom: 0; left: 10px; right: 10px; height: 35px;
            background: <?= $p['accent'] ?>;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .m-logo::before {
            content: '';
            position: absolute;
            bottom: 0; left: 20px; right: 20px; height: 30px;
            background: #fff;
            z-index: 3;
        }
        .logo-shadow {
            position: absolute;
            background: rgba(0,0,0,0.15);
            width: 100px;
            height: 100px;
            transform: rotate(45deg);
            top: 15px;
            right: -55px;
            z-index: 1;
        }

        .app-title {
            font-size: 18px;
            margin-bottom: 25px;
            color: <?= $p['text'] ?>;
        }
        
        .alert {
            background: #c0392b;
            color: #fff;
            padding: 10px;
            font-size: 13px;
            border-radius: 3px;
            margin-bottom: 15px;
            text-align: left;
        }

        form { text-align: left; }
        .input-group {
            margin-bottom: 12px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            background: <?= $p['input'] ?>;
            border: 1px solid <?= $p['border'] ?>;
            border-radius: 3px;
            color: <?= $p['text'] ?>;
            font-size: 13px;
            outline: none;
            transition: 0.2s;
        }
        .form-control::placeholder { color: <?= $p['placeholder'] ?>; }
        .form-control:focus {
            border-color: <?= $p['accent'] ?>;
            box-shadow: 0 0 5px <?= $p['accent'] ?>44;
        }

        .btn-login {
            display: block;
            width: 100%;
            background: <?= $p['accent'] ?>;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.2s;
        }
        .btn-login:hover { opacity: 0.85; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="fa fa-users"></i> Agent Login
    </div>
    
    <div class="login-body">
        
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo-box">
                <div class="m-logo"></div>
                <div style="position:absolute; bottom:0; padding:10px; z-index:4; display:flex; gap:3px;">
                    <div style="width:8px; height:25px; background:<?= $p['accent'] ?>;"></div>
                    <div style="width:8px; height:25px; background:<?= $p['accent'] ?>;"></div>
                </div>
                <div class="logo-shadow"></div>
            </div>
        </div>
        
        <div class="app-title">MIKHMON Agent</div>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="?page=login">
            <div class="input-group">
                <input class="form-control" type="text" name="username" placeholder="Username" required autocomplete="off" autofocus>
            </div>
            <div class="input-group">
                <input class="form-control" type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>

    </div>
</div>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('./sw.js').catch(function(){});
}
</script>
</body>
</html>
