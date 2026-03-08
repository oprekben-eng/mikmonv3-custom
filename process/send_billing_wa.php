<?php
/**
 * Send Billing WA via Fonnte
 */
session_start();
error_reporting(0);
require_once('../database/database.php');
require_once('../lib/fonnte.php');

$id = $_GET['id'];
$db = Database::getConnection();

// Get billing data
$stmt = $db->prepare("SELECT * FROM billing WHERE id = ?");
$stmt->execute([$id]);
$billing = $stmt->fetch();

if (!$billing) {
    echo "Error: Data penagihan tidak ditemukan.";
    exit;
}

// Get template
$template = Database::getSetting('fonnte_billing_template', '');
if (empty($template)) {
    echo "Error: Template penagihan belum disetting.";
    exit;
}

// Format message
$message = str_replace(
    ['{username}', '{amount}', '{due_date}', '{description}'],
    [$billing['username'], number_format($billing['amount'], 0, ',', '.'), $billing['due_date'], $billing['description']],
    $template
);

// Send via Fonnte
$fonnte = new Fonnte();
$res = $fonnte->sendMessage($billing['phone'], $message);

if ($res['success']) {
    // Update last reminded time
    $stmt = $db->prepare("UPDATE billing SET last_reminded_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    echo "Sukses: Pesan penagihan terkirim ke " . $billing['phone'];
} else {
    echo "Gagal: " . $res['detail'];
}
