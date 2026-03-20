<?php
/**
 * Standalone Mikhmon Custom Auto-Updater (Bootstrap)
 * Upload file ini ke root folder Mikhmon Anda di Hosting (misal: public_html/update.php)
 * Lalu akses mondar-mandir URL-nya melalui browser (misal: domainanda.com/update.php).
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$repo_url = "https://github.com/oprekben-eng/mikmonv3-custom/archive/refs/heads/main.zip";
$temp_zip = __DIR__ . '/temp_update.zip';
$temp_extract = __DIR__ . '/temp_extract';
$target_dir = __DIR__;

echo "<h3>Memulai Proses Penarikan Mikhmon dari GitHub...</h3>";

// 1. Download
echo "1. Mendownload arsip dari GitHub...<br>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $repo_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mikhmon-Bootstrap-Updater');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || $data === false) {
    die("<b style='color:red'>Gagal mendownload repositori. HTTP Code: $http_code</b>");
}

file_put_contents($temp_zip, $data);
echo "<span style='color:green'>&#10003; Download zip berhasil (" . round(filesize($temp_zip)/1024, 2) . " KB)</span><br><br>";

// 2. Extract
echo "2. Mengekstrak file ZIP...<br>";
$zip = new ZipArchive;
if ($zip->open($temp_zip) === TRUE) {
    if (!is_dir($temp_extract)) mkdir($temp_extract, 0755, true);
    $zip->extractTo($temp_extract);
    $zip->close();
    echo "<span style='color:green'>&#10003; Ekstraksi berhasil</span><br><br>";
} else {
    die("<b style='color:red'>Gagal mengekstrak file ZIP.</b>");
}

// 3. Find source dir inside extract
$extracted_items = scandir($temp_extract);
$source_dir = $temp_extract;
foreach ($extracted_items as $item) {
    if ($item != '.' && $item != '..') {
        if (is_dir($temp_extract . '/' . $item)) {
            $source_dir = $temp_extract . '/' . $item;
            break;
        }
    }
}

// Helper functions
function xcopy($src, $dest, $root_src) {
    foreach (scandir($src) as $file) {
        if (!is_readable($src . '/' . $file)) continue;
        if ($file == '.' || $file == '..') continue;
        
        $relative_path = substr($src . '/' . $file, strlen($root_src) + 1);

        // Jangan overwrite config/database
        if ($relative_path === 'include/config.php') continue;
        if ($relative_path === 'database/database.php' && file_exists($dest . '/database/database.php')) continue; // Cegah file koneksi DB pengguna tertimpa GitHub
        if (strpos($relative_path, 'database/mikhmon_') === 0) continue; // Skip sqlite database files if any
        if ($file === 'img' && is_dir($src . '/' . $file) && $src == $root_src) continue;
        
        if (is_dir($src . '/' . $file)) {
            if (!is_dir($dest . '/' . $file)) mkdir($dest . '/' . $file, 0755, true);
            xcopy($src . '/' . $file, $dest . '/' . $file, $root_src);
        } else {
            copy($src . '/' . $file, $dest . '/' . $file);
        }
    }
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') $dirPath .= '/';
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) deleteDir($file);
        else unlink($file);
    }
    rmdir($dirPath);
}

// 4. Overwrite
echo "3. Menerapkan pembaruan file...<br>";
xcopy($source_dir, $target_dir, $source_dir);

// Optional: Execute Schema
$schemaFile = $target_dir . '/database/schema.sql';
if (file_exists($target_dir . '/database/database.php')) {
    require_once($target_dir . '/database/database.php');
    if (file_exists($schemaFile) && class_exists('Database')) {
        try {
            $db = Database::getConnection();
            if ($db) {
                $db->exec(file_get_contents($schemaFile));
                echo "<span style='color:green'>&#10003; Struktur Database Tersinkronisasi</span><br>";
                
                // Assign new github url directly to ensure setting is there
                Database::setSetting('github_repo_url', 'https://github.com/oprekben-eng/mikmonv3-custom/archive/refs/heads/main.zip');
            }
        } catch (Exception $e) {
            echo "<span style='color:orange'>Info Schema DB: " . $e->getMessage() . "</span><br>";
        }
    }
}

echo "<br><span style='color:green'>&#10003; Pembaruan File sukses!</span><br><br>";

// 5. Cleanup
echo "4. Membersihkan file sementara...<br>";
deleteDir($temp_extract);
unlink($temp_zip);
echo "<span style='color:green'>&#10003; Pembersihan Selesai.</span><br><br>";

echo "<h2 style='color:blue'>Selesai! Mikhmon Hosting Anda telah berhasil Diperbarui.</h2>";
echo "Silakan menuju halaman admin: <a href='admin.php'>Login Mikhmon</a><br>";
echo "<br><small style='color:red'>*Saran: Hapus file update.php ini setelah digunakan untuk keamanan.</small>";
?>
