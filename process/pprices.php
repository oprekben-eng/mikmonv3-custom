<?php
/**
 * Process Agent Prices Settings
 * Save/delete custom agent prices
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Unauthorized</div>';
    exit;
}

require_once('../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    try {
        $db = Database::getConnection();

        if ($action === 'save') {
            $agent_id = intval($_POST['agent_id']);
            $profile = trim($_POST['profile']);
            $buy_price = intval($_POST['buy_price']);
            $sell_price = intval($_POST['sell_price']);
            $data_limit = intval($_POST['data_limit'] ?? 0);

            if ($agent_id <= 0 || empty($profile)) {
                echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Data tidak valid.</div>';
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO agent_prices (agent_id, profile, buy_price, sell_price, data_limit) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE buy_price = ?, sell_price = ?, data_limit = ?
            ");
            
            if ($stmt->execute([$agent_id, $profile, $buy_price, $sell_price, $data_limit, $buy_price, $sell_price, $data_limit])) {
                echo '<div class="bg-success pd-5 radius-3"><i class="fa fa-check"></i> Harga berhasil disimpan.</div>';
            } else {
                echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Gagal menyimpan.</div>';
            }
        }
        
        elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $db->prepare("DELETE FROM agent_prices WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo '<div class="bg-success pd-5 radius-3"><i class="fa fa-check"></i> Harga dihapus.</div>';
            }
        }

    } catch (PDOException $e) {
        echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Error: ' . $e->getMessage() . '</div>';
    }
}
