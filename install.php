<?php
/**
 * Mikhmon Custom Features Installer
 * Run this script to setup the database and configuration.
 */
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Check Requirements Helper
function checkReq($condition, $text) {
    if ($condition) {
        return "<tr><td>$text</td><td class='text-success'><i class='fa fa-check'></i> OK</td></tr>";
    } else {
        return "<tr><td>$text</td><td class='text-danger'><i class='fa fa-times'></i> FAILED</td></tr>";
    }
}

// Installation Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 3) {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = trim($_POST['db_name'] ?? '');

    if (empty($db_host) || empty($db_user) || empty($db_name)) {
        $error = "Host, Username, dan Database Name harus diisi!";
        $step = 2; // back to form
    } else {
        try {
            // 1. Test Connection & Create Database
            $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Note: CREATE DATABASE IF NOT EXISTS might need privileges, handle gracefully
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $pdo->exec("USE `$db_name`");

            // 2. Import schema.sql
            $schema_file = __DIR__ . '/database/schema.sql';
            if (!file_exists($schema_file)) {
                throw new Exception("File schema.sql tidak ditemukan di folder database!");
            }
            $sql = file_get_contents($schema_file);
            $pdo->exec($sql);

            // 3. Update database/database.php
            $db_file = __DIR__ . '/database/database.php';
            if (!is_writable($db_file)) {
                throw new Exception("File database/database.php tidak writable. Berikan akses tulis (chmod 666 atau chown yang sesuai).");
            }
            $db_content = file_get_contents($db_file);
            
            // Specific patterns that match my implementation in Phase 1
            $db_content = preg_replace("/\\\$host\\s*=\\s*['\"].*?['\"];/", "\$host   = '$db_host';", $db_content);
            $db_content = preg_replace("/\\\$user\\s*=\\s*['\"].*?['\"];/", "\$user   = '$db_user';", $db_content);
            $db_content = preg_replace("/\\\$pass\\s*=\\s*['\"].*?['\"];/", "\$pass   = '" . addslashes($db_pass) . "';", $db_content);
            $db_content = preg_replace("/\\\$dbname\\s*=\\s*['\"].*?['\"];/", "\$dbname = '$db_name';", $db_content);
            
            file_put_contents($db_file, $db_content);

            $success = true;
            
            // Attempt to self-delete
            @unlink(__FILE__);

        } catch (PDOException $e) {
            $error = "Kesalahan Database: " . $e->getMessage();
            $step = 2; // back
        } catch (Exception $e) {
            $error = $e->getMessage();
            $step = 2; // back
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mikhmon Custom Installer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- Use standard Mikhmon UI CSS for setup -->
    <link rel="stylesheet" href="css/mikhmon-ui.light.min.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .install-box { width: 600px; max-width: 100%; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .install-logo { text-align: center; margin-bottom: 20px; font-size: 24px; font-weight: bold; color: #34495e; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .step-indicator::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: #eee; z-index: 1; }
        .step { width: 33.33%; text-align: center; z-index: 2; position: relative; font-weight: bold; color: #999; }
        .step span { display: inline-block; width: 30px; height: 30px; line-height: 30px; background: #eee; border-radius: 50%; border: 3px solid #fff; }
        .step.active span { background: #3498db; color: #fff; }
        .step.active { color: #3498db; }
        .step.done span { background: #2ecc71; color: #fff; }
        .step.done { color: #2ecc71; }
    </style>
</head>
<body>

<div class="install-box">
    <div class="install-logo">
        <i class="fa fa-cogs text-primary"></i> <br>Mikhmon Custom Installer
    </div>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step <?= $step == 1 ? 'active' : ($step > 1 ? 'done' : '') ?>"><span>1</span><br>Syarat</div>
        <div class="step <?= $step == 2 ? 'active' : ($step > 2 ? 'done' : '') ?>"><span>2</span><br>Database</div>
        <div class="step <?= $step == 3 ? 'active' : ($step > 3 ? 'done' : '') ?>"><span>3</span><br>Selesai</div>
    </div>

    <?php if ($error): ?>
        <div class="bg-danger pd-10 radius-3 mb-20"><i class="fa fa-ban"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- STEP 1: Requirements -->
    <?php if ($step == 1): 
        $reqs_passed = true;
        // Check PHP Version
        $php_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
        $pdo_ok = extension_loaded('pdo_mysql');
        $curl_ok = extension_loaded('curl');
        $mb_ok = extension_loaded('mbstring');
        $json_ok = extension_loaded('json');
        
        $db_file_ok = true;
        $db_file_path = __DIR__ . '/database/database.php';
        if (file_exists($db_file_path) && !is_writable($db_file_path)) {
            $db_file_ok = false;
        }

        if (!$php_ok || !$pdo_ok || !$curl_ok || !$mb_ok || !$json_ok || !$db_file_ok) {
            $reqs_passed = false;
        }
    ?>
        <h3 class="mb-20">Pengecekan Server</h3>
        <table class="table table-bordered table-striped text-left">
            <?= checkReq($php_ok, "PHP Version >= 7.4") ?>
            <?= checkReq($pdo_ok, "PDO MySQL Extension") ?>
            <?= checkReq($curl_ok, "cURL Extension (Fonnte)") ?>
            <?= checkReq($mb_ok, "Mbstring Extension") ?>
            <?= checkReq($json_ok, "JSON Extension") ?>
            <?= checkReq($db_file_ok, "Write Access ke database/database.php") ?>
        </table>
        
        <div class="mt-20 text-right">
            <?php if ($reqs_passed): ?>
                <a href="?step=2" class="btn bg-primary pd-10"><i class="fa fa-arrow-right"></i> Lanjutkan</a>
            <?php else: ?>
                <button class="btn bg-danger pd-10" disabled><i class="fa fa-ban"></i> Penuhi syarat di atas dulu</button>
                <a href="?step=1" class="btn bg-info pd-10"><i class="fa fa-refresh"></i> Cek Ulang</a>
            <?php endif; ?>
        </div>

    <!-- STEP 2: Database Input -->
    <?php elseif ($step == 2): ?>
        <h3 class="mb-20">Konfigurasi Database MySQL/MariaDB</h3>
        <form method="post" action="?step=3" id="dbForm">
            <div class="group">
                <label>Database Host</label>
                <input class="form-control" type="text" name="db_host" value="localhost" required>
            </div>
            <div class="group">
                <label>Database Username</label>
                <input class="form-control" type="text" name="db_user" value="root" required>
            </div>
            <div class="group">
                <label>Database Password</label>
                <input class="form-control" type="text" name="db_pass" placeholder="(Kosongkan jika tidak ada password)">
            </div>
            <div class="group">
                <label>Database Name</label>
                <input class="form-control" type="text" name="db_name" value="mikhmon_agent" required>
            </div>
            <div class="mt-20 text-right">
                <a href="?step=1" class="btn bg-warning pd-10 pull-left"><i class="fa fa-arrow-left"></i> Kembali</a>
                <button type="submit" class="btn bg-primary pd-10" id="btnInstall"><i class="fa fa-cogs"></i> Install Sekarang</button>
            </div>
        </form>
        <script>
            document.getElementById('dbForm').onsubmit = function() {
                var btn = document.getElementById('btnInstall');
                btn.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i> Proses Instalasi...';
                btn.disabled = true;
            };
        </script>

    <!-- STEP 3: Finish -->
    <?php elseif ($step == 3 && $success): ?>
        <div class="text-center mt-20 mb-20">
            <i class="fa fa-check-circle text-success" style="font-size: 60px;"></i>
            <h2 class="mt-10">Instalasi Berhasil!</h2>
            <p>Database table structure <b><?= htmlspecialchars($_POST['db_name']) ?></b> berhasil dibuat dan dikonfigurasi.</p>
            
            <?php if (file_exists(__FILE__)): ?>
                <div class="bg-warning pd-10 radius-3 mt-20 mb-20 text-left">
                    <i class="fa fa-exclamation-triangle"></i> <b>PERHATIAN:</b> Sistem gagal menghapus file <code>install.php</code> secara otomatis. Sangat disarankan untuk <b>menghapus file install.php secara manual</b> demi alasan keamanan.
                </div>
            <?php else: ?>
                <div class="bg-success pd-10 radius-3 mt-20 mb-20 text-left text-white">
                    <i class="fa fa-check"></i> File <code>install.php</code> telah dihapus secara otomatis demi keamanan.
                </div>
            <?php endif; ?>

            <a href="admin.php" class="btn bg-primary pd-10 text-bold" style="font-size: 16px;"><i class="fa fa-home"></i> Buka Aplikasi (Login Administrator)</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
