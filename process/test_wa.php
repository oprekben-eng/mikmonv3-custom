<?php
/**
 * Test WhatsApp message sending
 * Called via AJAX from fonnte_settings.php
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    echo '<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> Unauthorized</div>';
    exit;
}

require_once('../lib/fonnte.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($phone) || empty($message)) {
        echo '<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> Nomor WA dan Pesan wajib diisi.</div>';
        exit;
    }

    $fonnte = new Fonnte();
    
    if (!$fonnte->isConfigured()) {
        echo '<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> Token Fonnte belum dikonfigurasi. Simpan pengaturan terlebih dahulu.</div>';
        exit;
    }

    $result = $fonnte->sendMessage($phone, $message);

    if ($result['success']) {
        echo '<div class="bg-success pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-check"></i> Pesan test berhasil dikirim ke ' . htmlspecialchars($phone) . '</div>';
    } else {
        echo '<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> Gagal mengirim pesan: ' . htmlspecialchars($result['detail']) . '</div>';
        echo '<div style="font-size:11px; margin-top:5px; padding:5px; background:#f5f5f5; border:1px solid #ddd; overflow-wrap:break-word;">Raw Response: ' . htmlspecialchars($result['response']) . '</div>';
    }
}
