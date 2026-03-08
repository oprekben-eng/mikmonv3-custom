<?php
/**
 * Manage Agents Page (Admin)
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM agents ORDER BY id DESC");
$agents = $stmt->fetchAll();
?>

<div class="row">
<div class="col-12">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-users"></i> Daftar Agen</h3>
    </div>
    <div class="card-body">
        <div class="pd-10">
            <a href="./?agent=add&session=<?= $session; ?>" class="btn bg-primary"><i class="fa fa-plus"></i> Tambah Agen</a>
        </div>
        
        <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
        <table class="table table-bordered table-hover" id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>No. HP</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agents as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><?= htmlspecialchars($a['username']) ?></td>
                    <td><?= htmlspecialchars($a['phone']) ?></td>
                    <td>Rp <?= number_format($a['balance'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($a['is_active']): ?>
                            <span class="text-success"><i class="fa fa-check-circle"></i> Aktif</span>
                        <?php else: ?>
                            <span class="text-danger"><i class="fa fa-times-circle"></i> Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="./?agent=topup&id=<?= $a['id'] ?>&session=<?= $session; ?>" class="btn btn-sm bg-success" title="Topup"><i class="fa fa-money"></i></a>
                        <a href="./?agent=edit&id=<?= $a['id'] ?>&session=<?= $session; ?>" class="btn btn-sm bg-info" title="Edit"><i class="fa fa-edit"></i></a>
                        <?php if ($a['is_active']): ?>
                            <a href="./process/pagent.php?action=toggle&val=0&id=<?= $a['id'] ?>&session=<?= $session; ?>" class="btn btn-sm bg-warning" title="Nonaktifkan"><i class="fa fa-ban"></i></a>
                        <?php else: ?>
                            <a href="./process/pagent.php?action=toggle&val=1&id=<?= $a['id'] ?>&session=<?= $session; ?>" class="btn btn-sm bg-success" title="Aktifkan"><i class="fa fa-check"></i></a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" onclick="if(confirm('Hapus agen <?= $a['username'] ?>?')) window.location='./process/pagent.php?action=delete&id=<?= $a['id'] ?>&session=<?= $session; ?>';" class="btn btn-sm bg-danger" title="Hapus"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($agents) == 0): ?>
                <tr><td colspan="7" class="text-center">Belum ada agen terdaftar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
</div>
</div>
