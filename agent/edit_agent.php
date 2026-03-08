<?php
/**
 * Edit Agent Page (Admin)
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
$stmt = $db->prepare("SELECT * FROM agents WHERE id = ?");
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
        <h3><i class="fa fa-edit"></i> Edit Agen: <?= htmlspecialchars($agent['username']) ?></h3>
    </div>
    <div class="card-body">
        <form method="post" action="./process/pagent.php" autocomplete="off">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $agent['id'] ?>">
            <input type="hidden" name="session" value="<?= $session; ?>">
            <table class="table">
                <tr>
                    <td class="align-middle">Nama Lengkap</td>
                    <td><input class="form-control" type="text" name="name" required value="<?= htmlspecialchars($agent['name']) ?>"></td>
                </tr>
                <tr>
                    <td class="align-middle">No. WhatsApp</td>
                    <td><input class="form-control" type="text" name="phone" required value="<?= htmlspecialchars($agent['phone']) ?>"></td>
                </tr>
                <tr>
                    <td class="align-middle">Password Baru</td>
                    <td>
                        <input class="form-control" type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                        <small class="text-muted">Isi hanya jika ingin ganti password.</small>
                    </td>
                </tr>
                <tr>
                    <td class="align-middle">Status Aktif</td>
                    <td>
                        <div class="checkbox">
                            <label><input type="checkbox" name="is_active" <?= $agent['is_active'] ? 'checked' : '' ?>> Agen bisa login & transaksi</label>
                        </div>
                    </td>
                </tr>
            </table>
            <div style="padding: 10px;">
                <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
                <a href="./?agent=manage&session=<?= $session; ?>" class="btn bg-warning"><i class="fa fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
