<?php
/**
 * Mikhmon Hosting Deployment Packager
 * Membuat file ZIP siap upload ke hosting
 * Akses: http://localhost/master/build_zip.php
 */

set_time_limit(120);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$projectDir = __DIR__;
$zipName = 'mikhmon-hosting-' . date('Ymd-His') . '.zip';
$zipPath = $projectDir . '/' . $zipName;

// File & folder yang TIDAK dimasukkan ke ZIP
$excludes = [
    // Development & config files
    '.git',
    '.gitignore',
    '.profile',
    '.antigravityignore',
    '.gemini',
    '.agents',
    '_config.yml',
    'docker-compose.yml',
    'nginx.conf',
    'temp.txt',
    'verson.txt',
    'README.md',
    'INSTALL.md',
    'LICENSE',

    // Development tools
    'build_zip.php',
    'test_ros_compat.php',
    'migrate.php',

    // Documentation files
    'dok_dashboard_ppp_bulanan.md',
    'dok_layout_agent.md',

    // Generated ZIP files
    '*.zip',
];

// ============================================

if (!class_exists('ZipArchive')) {
    die('<h2 style="color:red;">❌ Error: PHP ZipArchive extension tidak tersedia!</h2>
         <p>Aktifkan extension <code>zip</code> di php.ini</p>');
}

/**
 * Check if path should be excluded
 */
function shouldExclude($relativePath, $excludes) {
    $parts = explode('/', str_replace('\\', '/', $relativePath));
    foreach ($parts as $part) {
        foreach ($excludes as $pattern) {
            if (strpos($pattern, '*') !== false) {
                // Wildcard match
                if (fnmatch($pattern, $part)) return true;
            } else {
                if ($part === $pattern) return true;
            }
        }
    }
    return false;
}

/**
 * Recursively add files to ZIP
 */
function addToZip($zip, $dir, $baseDir, $excludes) {
    $count = 0;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;
        $relativePath = substr($fullPath, strlen($baseDir) + 1);

        if (shouldExclude($relativePath, $excludes)) continue;

        if (is_dir($fullPath)) {
            $zip->addEmptyDir($relativePath);
            $count += addToZip($zip, $fullPath, $baseDir, $excludes);
        } else {
            $zip->addFile($fullPath, $relativePath);
            $count++;
        }
    }
    return $count;
}

// ============================================
// BUILD ZIP
// ============================================

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('<h2 style="color:red;">❌ Gagal membuat file ZIP!</h2>');
}

$fileCount = addToZip($zip, $projectDir, $projectDir, $excludes);
$zip->close();

$fileSize = filesize($zipPath);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Mikhmon Packager</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/mikhmon-ui.light.min.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .pack-box { width: 600px; max-width: 100%; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .pack-logo { text-align: center; margin-bottom: 20px; }
        .file-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #4dbd74; }
        .exclude-list { background: #fff3cd; padding: 10px 15px; border-radius: 5px; margin: 15px 0; font-size: 13px; }
        .exclude-list code { background: #ffeaa7; padding: 1px 5px; border-radius: 3px; }
    </style>
</head>
<body>
<div class="pack-box">
    <div class="pack-logo">
        <i class="fa fa-archive text-success" style="font-size: 48px;"></i>
        <h2>Mikhmon Hosting Packager</h2>
    </div>

    <div class="bg-success pd-10 radius-3 text-white text-center" style="font-size: 18px;">
        <i class="fa fa-check-circle"></i> ZIP Berhasil Dibuat!
    </div>

    <div class="file-info">
        <table style="width:100%;">
            <tr><td><b><i class="fa fa-file-archive-o"></i> Nama File</b></td><td><?= htmlspecialchars($zipName) ?></td></tr>
            <tr><td><b><i class="fa fa-hdd-o"></i> Ukuran</b></td><td><?= $fileSizeMB ?> MB (<?= number_format($fileSize) ?> bytes)</td></tr>
            <tr><td><b><i class="fa fa-files-o"></i> Jumlah File</b></td><td><?= $fileCount ?> file</td></tr>
            <tr><td><b><i class="fa fa-clock-o"></i> Dibuat</b></td><td><?= date('d M Y H:i:s') ?></td></tr>
        </table>
    </div>

    <div class="text-center" style="margin: 20px 0;">
        <a href="<?= htmlspecialchars($zipName) ?>" class="btn bg-primary pd-10" style="font-size: 16px;" download>
            <i class="fa fa-download"></i> Download ZIP
        </a>
    </div>

    <div class="exclude-list">
        <b><i class="fa fa-ban"></i> File yang dikecualikan:</b><br>
        <?php foreach ($excludes as $e): ?>
            <code><?= htmlspecialchars($e) ?></code>
        <?php endforeach; ?>
    </div>

    <div class="bg-info pd-10 radius-3 text-white mt-10" style="font-size: 13px;">
        <i class="fa fa-info-circle"></i> <b>Cara pakai:</b> Download ZIP → Upload ke hosting via cPanel/FTP → Extract di <code>public_html</code> → Buka <code>install.php</code>
    </div>
</div>
</body>
</html>
