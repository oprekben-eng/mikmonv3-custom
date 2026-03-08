<?php
/**
 * Topup Agent Page (Admin)
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo "<script>window.location='./?agent=manage&session=".$_GET['session']."';</script>";
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT id, username, name, balance FROM agents WHERE id = ?");
$stmt->execute([$id]);
$agent = $stmt->fetch();

if (!$agent) {
    echo "<div class='bg-danger pd-5 radius-3'>Agen tidak ditemukan.</div>";
    exit;
}
?>

<div class="row">
<div class="col-6">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-money"></i> Kelola Saldo Agen</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered mb-3">
            <tr><th style="width:30%">ID Agen</th><td><?= $agent['id'] ?></td></tr>
            <tr><th>Nama</th><td><?= htmlspecialchars($agent['name']) ?> (<?= htmlspecialchars($agent['username']) ?>)</td></tr>
            <tr><th>Saldo Saat Ini</th><td class="text-success text-bold" style="font-size: 18px;">Rp <?= number_format($agent['balance'], 0, ',', '.') ?></td></tr>
        </table>

        <form method="post" action="./process/pagent.php" autocomplete="off">
            <input type="hidden" name="action" value="topup">
            <input type="hidden" name="id" value="<?= $agent['id'] ?>">
            <input type="hidden" name="session" value="<?= $session; ?>">
            <table class="table">
                <tr>
                    <td class="align-middle" style="width:30%">Aksi Saldo</td>
                    <td>
                        <select name="type" class="form-control" required>
                            <option value="add">Tambah Saldo (+)</option>
                            <option value="deduct">Kurangi Saldo (-)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle">Jumlah (Rp)</td>
                    <td>
                        <input class="form-control" type="number" name="amount" required min="1" placeholder="Contoh: 150000">
                    </td>
                </tr>
            </table>
            <div style="padding: 10px;">
                <button type="submit" class="btn bg-success"><i class="fa fa-save"></i> Proses Saldo</button>
                <a href="./?agent=manage&session=<?= $session; ?>" class="btn bg-warning"><i class="fa fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
