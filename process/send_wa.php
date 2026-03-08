<?php
/**
 * Resend WhatsApp message from Agent History
 * Called via AJAX
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"]) && !isset($_SESSION["agent"])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once('../database/database.php');
require_once('../lib/fonnte.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $phone = $_POST['phone'] ?? '';

    if ($id <= 0 || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    try {
        $db = Database::getConnection();
        
        // Cek transaksi
        $stmt = $db->prepare("
            SELECT t.*, a.name as agent_name 
            FROM voucher_transactions t 
            JOIN agents a ON t.agent_id = a.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $trx = $stmt->fetch();

        if (!$trx) {
            echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan.']);
            exit;
        }

        // Cek otorisasi agen
        if (isset($_SESSION['agent']) && $_SESSION['agent']['id'] != $trx['agent_id']) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access to transaction.']);
            exit;
        }

        // Siapkan pesan
        $template = Database::getSetting('fonnte_template', "Username: {username}\nPassword: {password}\nHarga: {price}");
        $data = [
            'username' => $trx['username'],
            'password' => $trx['password'],
            'profile' => $trx['profile'],
            'price' => 'Rp ' . number_format($trx['price'], 0, ',', '.'),
            'agent_name' => $trx['agent_name'],
            'customer_phone' => $phone,
            'session' => $trx['session_name']
        ];
        
        $message = Fonnte::formatTemplate($template, $data);

        // Kirim
        $fonnte = new Fonnte();
        $result = $fonnte->sendMessage($phone, $message);

        if ($result['success']) {
            // Update nomor HP dan status terkirim
            $upd = $db->prepare("UPDATE voucher_transactions SET customer_phone = ?, wa_sent = 1, wa_sent_at = NOW() WHERE id = ?");
            $upd->execute([$phone, $id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pesan berhasil dikirim ulang ke ' . htmlspecialchars($phone)
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Gagal mengirim: ' . $result['detail']
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
