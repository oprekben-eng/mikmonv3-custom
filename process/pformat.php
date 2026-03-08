<?php
/**
 * Process Format Voucher Settings
 * Save/load voucher format configuration to database
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Unauthorized</div>';
    exit;
}

require_once('../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_fonnte') {
        $keys = ['fonnte_token', 'fonnte_template'];
    } elseif ($action === 'billing_template') {
        $keys = ['fonnte_billing_template'];
    } else {
        $keys = ['vc_mode', 'vc_char', 'vc_len', 'vc_prefix', 'vc_ucase'];
    }
    
    $success = true;

    foreach ($keys as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            
            // Validation
            if ($key == 'vc_len') {
                $value = max(4, min(20, intval($value)));
            }
            if ($key == 'vc_prefix') {
                $value = substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', $value), 0, 10);
            }
            if ($key == 'vc_mode' && !in_array($value, ['sama', 'beda'])) {
                $value = 'sama';
            }
            if ($key == 'vc_char' && !in_array($value, ['huruf', 'angka', 'kombinasi'])) {
                $value = 'kombinasi';
            }
            if ($key == 'vc_ucase' && !in_array($value, ['0', '1'])) {
                $value = '1';
            }

            if (!Database::setSetting($key, $value)) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo '<div class="bg-success pd-5 radius-3" style="padding:8px;border-radius:5px;margin-bottom:10px;"><i class="fa fa-check"></i> Pengaturan berhasil disimpan.</div>';
    } else {
        echo '<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;margin-bottom:10px;"><i class="fa fa-ban"></i> Gagal menyimpan pengaturan. Periksa koneksi database.</div>';
    }
}
