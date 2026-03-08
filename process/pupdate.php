<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION["mikhmon"])){
    echo json_encode(['success' => false, 'error' => 'Akses ditolak. Anda harus login sebagai admin.']);
    exit;
}

require_once(__DIR__ . '/../database/database.php');

$logs = [];
function addLog($msg) {
    global $logs;
    $logs[] = $msg;
}

if (isset($_POST['action']) && $_POST['action'] === 'start_update') {
    $repo_url = Database::getSetting('github_repo_url');
    if (!$repo_url || strpos($repo_url, '.zip') === false) {
        echo json_encode(['success' => false, 'error' => 'URL repositori tidak valid atau belum diatur. Pastikan URL diakhiri dengan .zip']);
        exit;
    }

    $temp_zip = realpath(__DIR__ . '/../') . '/temp_update.zip';
    $temp_extract = realpath(__DIR__ . '/../') . '/temp_extract';

    // 1. Download ZIP
    addLog("Mendownload file core update dari GitHub...");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $repo_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mikhmon-Auto-Updater'); // Required by GitHub API
    // Nonaktifkan verifikasi SSL untuk kompatibilitas di local Windows (Laragon/XAMPP)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code !== 200 || $data === false) {
        $error_msg = $data === false ? $curl_error : "HTTP Code $http_code";
        echo json_encode(['success' => false, 'error' => "Gagal mengunduh file update. $error_msg", 'logs' => $logs]);
        exit;
    }

    file_put_contents($temp_zip, $data);
    addLog("Berhasil mendownload update sebesar " . round(filesize($temp_zip) / 1024, 2) . " KB.");

    // 2. Extract ZIP
    addLog("Mengekstrak file ZIP...");
    $zip = new ZipArchive;
    if ($zip->open($temp_zip) === TRUE) {
        // Create extract dir if not exists
        if (!is_dir($temp_extract)) {
            mkdir($temp_extract, 0755, true);
        }
        $zip->extractTo($temp_extract);
        $zip->close();
        addLog("Ekstraksi berhasil diselesaikan.");
    } else {
        echo json_encode(['success' => false, 'error' => "Gagal mengekstrak file ZIP.", 'logs' => $logs]);
        @unlink($temp_zip);
        exit;
    }

    // 3. Find extracted root directory
    // Typically GitHub zip extracts to a single parent directory (e.g. 'repo-name-main')
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

    addLog("Menyalin file update ke sistem root Mikhmon utama...");
    $target_dir = realpath(__DIR__ . '/../');

    // Helper functions
    function xcopy($src, $dest, &$logs, $root_src) {
        foreach (scandir($src) as $file) {
            if (!is_readable($src . '/' . $file)) continue;
            if ($file == '.' || $file == '..') continue;
            
            $relative_path = substr($src . '/' . $file, strlen($root_src) + 1);

            // LOGIKA EXCLUDE / PROTEKSI FILE:
            // Jangan overwrite config.php karena berisi password admin & data router!
            if ($relative_path === 'include/config.php') {
                $logs[] = ">> Melewati konfigurasi sensitif (include/config.php)...";
                continue;
            }
            // Jangan overwrite file database utama (Koneksi SQLite/Hosting pengguna bisa hilang jika ditimpa default GitHub)
            if ($relative_path === 'database/database.php' && file_exists($dest . '/database/database.php')) {
                $logs[] = ">> Melewati file Database koneksi user (database/database.php)...";
                continue;
            }
            // Jangan overwrite folder img/ agar logo user tidak hilang
            if ($file === 'img' && is_dir($src . '/' . $file) && $src == $root_src) {
                $logs[] = ">> Melewati folder gambar (img)...";
                continue;
            }

            if (is_dir($src . '/' . $file)) {
                if (!is_dir($dest . '/' . $file)) {
                    mkdir($dest . '/' . $file, 0755, true);
                }
                xcopy($src . '/' . $file, $dest . '/' . $file, $logs, $root_src);
            } else {
                copy($src . '/' . $file, $dest . '/' . $file);
            }
        }
    }
    
    function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    // Execute overwriting process
    xcopy($source_dir, $target_dir, $logs, $source_dir);
    addLog("Proses penimpaan file usai.");

    // 3.5 Update Database Schema
    addLog("Memeriksa pembaruan skema database...");
    $schemaFile = $target_dir . '/database/schema.sql';
    if (file_exists($schemaFile)) {
        try {
            $sql = file_get_contents($schemaFile);
            $db = Database::getConnection();
            if ($db) {
                $db->exec($sql);
                addLog("Skema database berhasil disinkronisasi (Tabel baru ditambahkan jika ada).");
            } else {
                addLog("Gagal menyinkronkan skema: Koneksi database bermasalah.");
            }
        } catch (Exception $e) {
            addLog("Peringatan sinkronisasi skema: " . $e->getMessage());
        }
    } else {
        addLog("File schema.sql tidak ditemukan, melewati pembaruan database.");
    }

    // 4. Cleanup
    addLog("Membersihkan file sisa installer temporary...");
    deleteDir($temp_extract);
    unlink($temp_zip);
    
    addLog("Update Aplikasi tuntas tanpa masalah.");

    echo json_encode(['success' => true, 'logs' => $logs]);
} else {
    echo json_encode(['success' => false, 'error' => 'Aksi tidak valid atau Anda mengakses format yang salah.']);
}
