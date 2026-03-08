<?php
/**
 * Process Agent Management (Admin)
 * CRUD operations for agents
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    echo '<div class="bg-danger pd-5 radius-3"><i class="fa fa-ban"></i> Unauthorized</div>';
    exit;
}

require_once('../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    // Capture the session token passed from the form
    $session_token = $_POST['session'] ?? $_GET['session'] ?? '';
    
    try {
        $db = Database::getConnection();

        if ($action === 'add') {
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $balance = intval($_POST['balance'] ?? 0);

            // Check if username exists
            $stmt = $db->prepare("SELECT id FROM agents WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo '<div class="bg-danger pd-5 radius-3" style="padding:10px;"><i class="fa fa-ban"></i> Username agen sudah digunakan.</div>';
                exit;
            }

            $stmt = $db->prepare("INSERT INTO agents (username, password, name, phone, balance) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $password, $name, $phone, $balance])) {
                echo '<script>window.location="../?agent=manage&session='.$session_token.'";</script>';
            } else {
                echo '<div class="bg-danger pd-5 radius-3" style="padding:10px;"><i class="fa fa-ban"></i> Gagal menambah agen.</div>';
            }
        } 
        
        elseif ($action === 'edit') {
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE agents SET name = ?, phone = ?, password = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $password, $is_active, $id]);
            } else {
                $stmt = $db->prepare("UPDATE agents SET name = ?, phone = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $is_active, $id]);
            }
            
            echo '<script>window.location="../?agent=manage&session='.$session_token.'";</script>';
        }

        elseif ($action === 'topup') {
            $id = intval($_POST['id']);
            $amount = intval($_POST['amount']);
            $type = $_POST['type']; // add or deduct
            
            if ($type === 'deduct') {
                $amount = -$amount;
            }
            
            $stmt = $db->prepare("UPDATE agents SET balance = balance + ? WHERE id = ?");
            if ($stmt->execute([$amount, $id])) {
                echo '<script>window.location="../?agent=manage&session='.$session_token.'";</script>';
            }
        }

    } catch (PDOException $e) {
        echo '<div class="bg-danger pd-5 radius-3" style="padding:10px;"><i class="fa fa-ban"></i> Error: ' . $e->getMessage() . '</div>';
    }
}

// Handle GET actions (delete, toggle)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $session = $_GET['session'];
    
    try {
        $db = Database::getConnection();
        
        if ($action === 'delete') {
            $id = intval($_GET['id']);
            // Delete related rows first to avoid foreign key constraints errors
            $stmt = $db->prepare("DELETE FROM voucher_transactions WHERE agent_id = ?");
            $stmt->execute([$id]);
            $stmt = $db->prepare("DELETE FROM agent_prices WHERE agent_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $db->prepare("DELETE FROM agents WHERE id = ?");
            $stmt->execute([$id]);
            echo '<script>window.location="../?agent=manage&session='.$session.'";</script>';
        }
        
        elseif ($action === 'toggle') {
            $id = intval($_GET['id']);
            $val = intval($_GET['val']);
            $stmt = $db->prepare("UPDATE agents SET is_active = ? WHERE id = ?");
            $stmt->execute([$val, $id]);
            echo '<script>window.location="../?agent=manage&session='.$session.'";</script>';
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
