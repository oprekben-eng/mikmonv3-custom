<?php
/**
 * Agent Voucher Generation Backend
 * Called via AJAX
 */
session_start();
error_reporting(0);

if (!isset($_SESSION['agent'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once('../database/database.php');
require_once('../lib/fonnte.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent = $_SESSION['agent'];
    $profile = trim($_POST['profile'] ?? '');
    $qty = intval($_POST['qty'] ?? 1);
    if ($qty < 1) $qty = 1;
    if ($qty > 100) $qty = 100; // Hard max limit to prevent timeouts
    $customer_phone = trim($_POST['customer_phone'] ?? '');

    if (empty($profile)) {
        echo json_encode(['success' => false, 'message' => 'Profil belum dipilih.']);
        exit;
    }

    try {
        $db = Database::getConnection();

        // Cek harga
        $stmt = $db->prepare("SELECT * FROM agent_prices WHERE agent_id = ? AND profile = ?");
        $stmt->execute([$agent['id'], $profile]);
        $priceData = $stmt->fetch();

        if (!$priceData) {
            echo json_encode(['success' => false, 'message' => 'Harga tidak ditemukan untuk profil ini.']);
            exit;
        }

        $buy_price = $priceData['buy_price'];
        
        // Cek saldo
        $total_cost = $buy_price * $qty;
        $stmt = $db->prepare("SELECT balance FROM agents WHERE id = ?");
        $stmt->execute([$agent['id']]);
        $current_balance = $stmt->fetch()['balance'];

        if ($current_balance < $total_cost) {
            echo json_encode(['success' => false, 'message' => 'Saldo tidak cukup untuk ' . $qty . ' voucher. Total Harga: Rp ' . number_format($total_cost, 0, ',', '.')]);
            exit;
        }

        // Cari session Mikrotik yang aktif
        include_once('../include/config.php');
        $mikhmon_session = '';
        foreach ($data as $key => $val) {
            if ($key !== 'mikhmon') {
                $mikhmon_session = $key;
                break;
            }
        }

        if (empty($mikhmon_session)) {
            echo json_encode(['success' => false, 'message' => 'Konfigurasi router tidak ditemukan.']);
            exit;
        }

        // Set session variable so readcfg.php can load it
        $session = $mikhmon_session;
        include_once('../include/readcfg.php');
        include_once('../lib/routeros_api.class.php');

        $API = new RouterosAPI();
        $API->debug = false;
        
        // Coba koneksi ke Mikrotik
        if (!$API->connect($iphost, $userhost, decrypt($passwdhost))) {
            echo json_encode(['success' => false, 'message' => 'Koneksi ke MikroTik gagal. Cek router.']);
            exit;
        }

        // Ambil format setting voucher
        $vc_mode = Database::getSetting('vc_mode', 'sama');
        $vc_char = Database::getSetting('vc_char', 'kombinasi');
        $vc_len = intval(Database::getSetting('vc_len', '8'));
        $vc_prefix = Database::getSetting('vc_prefix', '');
        $vc_ucase = Database::getSetting('vc_ucase', '1');

        // Buat charset
        $chars = '';
        if ($vc_char == 'huruf') {
            $chars = $vc_ucase == '1' ? 'abcdefghjkmnprstuvwxyzABCDEFGHJKMNPRSTUVWXYZ' : 'abcdefghjkmnprstuvwxyz';
        } elseif ($vc_char == 'angka') {
            $chars = '23456789';
        } else {
            $chars = $vc_ucase == '1' ? 'abcdefghjkmnprstuvwxyz23456789ABCDEFGHJKMNPRSTUVWXYZ' : 'abcdefghjkmnprstuvwxyz23456789';
        }

        // Start Generation Loop
        $generated_vouchers = [];
        $total_cost = 0;
        
        for ($q = 0; $q < $qty; $q++) {
            // Generate username/pass string
            $username = $vc_prefix;
            for ($i = 0; $i < $vc_len; $i++) {
                $username .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            
            if ($vc_mode == 'sama') {
                $password = $username;
            } else {
                $password = '';
                for ($i = 0; $i < $vc_len; $i++) {
                    $password .= $chars[mt_rand(0, strlen($chars) - 1)];
                }
            }

            // Tambah ke Mikrotik API
            // Format wajib Mikhmon: vc-{rand}-{m.d.y}-{komentar_tambahan} agar on-login merekam laporan penjualan
            $adcomment = "Agent_" . $agent['username'];
            $comment = "vc-" . rand(100, 999) . "-" . date("m.d.y") . "-" . $adcomment;
            
            $API->comm("/ip/hotspot/user/add", [
                "server"   => "all", 
                "name"     => $username,
                "password" => $password,
                "profile"  => $profile,
                "comment"  => $comment,
            ]);
            
            $generated_vouchers[] = [
                'username' => $username,
                'password' => $password
            ];
            $total_cost += $buy_price;
        }

        $API->disconnect();

        // ------------------
        // Transaction & Balance Handling
        // ------------------
        
        // Deduct total balance
        $stmt = $db->prepare("UPDATE agents SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$total_cost, $agent['id']]);
        $_SESSION['agent']['balance'] -= $total_cost;

        // Save transactions
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO voucher_transactions (agent_id, session_name, profile, username, password, customer_phone, price, wa_sent, wa_sent_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $wa_sent = 0;
        $wa_sent_at = null;
        $first_trx_id = 0;
        
        // For sending WhatsApp, usually we send it once if it's a batch, or we just send details of all.
        // If qty is 1, send normal text. If > 1, send batch text.
        if (!empty($customer_phone)) {
            $fonnte = new Fonnte();
            if ($fonnte->isConfigured()) {
                if ($qty == 1) {
                    $template = Database::getSetting('fonnte_template', "Username: {username}\nPassword: {password}\nPaket: {profile}\nHarga: {price}");
                    $msg_data = [
                        'username' => $generated_vouchers[0]['username'],
                        'password' => $generated_vouchers[0]['password'],
                        'profile'  => $profile,
                        'price'    => 'Rp ' . number_format($priceData['sell_price'], 0, ',', '.'),
                        'agent_name' => $agent['name'],
                        'customer_phone' => $customer_phone,
                        'session' => $mikhmon_session
                    ];
                    $message = Fonnte::formatTemplate($template, $msg_data);
                } else {
                    $message = "Halo, ini daftar " . $qty . " voucher paket " . $profile . " Anda:\n\n";
                    foreach($generated_vouchers as $v) {
                        $message .= "User: " . $v['username'] . " | Pass: " . $v['password'] . "\n";
                    }
                    $message .= "\nTerima kasih!\n- " . $agent['name'];
                }

                $res = $fonnte->sendMessage($customer_phone, $message);
                if ($res['success']) {
                    $wa_sent = 1;
                    $wa_sent_at = date('Y-m-d H:i:s');
                }
            }
        }

        foreach ($generated_vouchers as $v) {
            $stmt->execute([
                $agent['id'], 
                $mikhmon_session, 
                $profile, 
                $v['username'], 
                $v['password'], 
                $customer_phone, 
                $buy_price, 
                $wa_sent, 
                $wa_sent_at
            ]);
            
            if ($first_trx_id === 0) {
                $first_trx_id = $db->lastInsertId();
            }
        }
        
        $db->commit();

        echo json_encode([
            'success' => true, 
            'message' => $qty . ' Voucher berhasil digenerate.',
            'id' => $first_trx_id // Send back the first ID for history reference if needed
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }
}
