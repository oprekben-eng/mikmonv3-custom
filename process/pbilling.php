<?php
/**
 * Billing Process Handler
 */
session_start();
error_reporting(0);
require_once('../database/database.php');

$action = $_GET['action'];
$db = Database::getConnection();

if ($action === 'add') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $amount = (int)str_replace(['.', ','], '', $_POST['amount']);
    $due_date = (int)$_POST['due_date'];
    $description = $_POST['description'];

    $stmt = $db->prepare("INSERT INTO billing (username, phone, amount, due_date, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$username, $phone, $amount, $due_date, $description])) {
        echo '<div class="alert bg-success"><i class="fa fa-check"></i> Data berhasil ditambahkan.</div>';
    } else {
        echo '<div class="alert bg-danger"><i class="fa fa-times"></i> Gagal menambahkan data.</div>';
    }
}

elseif ($action === 'edit') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $amount = (int)str_replace(['.', ','], '', $_POST['amount']);
    $due_date = (int)$_POST['due_date'];
    $description = $_POST['description'];

    $stmt = $db->prepare("UPDATE billing SET username=?, phone=?, amount=?, due_date=?, description=? WHERE id=?");
    if ($stmt->execute([$username, $phone, $amount, $due_date, $description, $id])) {
        echo '<div class="alert bg-success"><i class="fa fa-check"></i> Data berhasil diperbarui.</div>';
    } else {
        echo '<div class="alert bg-danger"><i class="fa fa-times"></i> Gagal memperbarui data.</div>';
    }
}

elseif ($action === 'delete') {
    $id = $_GET['id'];
    $stmt = $db->prepare("DELETE FROM billing WHERE id=?");
    $stmt->execute([$id]);
    header("Location: ../?ppp=billing&session=" . $_GET['session']);
}

