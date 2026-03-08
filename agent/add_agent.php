<?php
/**
 * Add Agent Page (Admin)
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}
?>

<div class="row">
<div class="col-6">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-user-plus"></i> Tambah Agen</h3>
    </div>
    <div class="card-body">
        <form method="post" action="./process/pagent.php" autocomplete="off">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="session" value="<?= $session; ?>">
            <table class="table">
                <tr>
                    <td class="align-middle">Nama Lengkap</td>
                    <td><input class="form-control" type="text" name="name" required placeholder="Nama Lengkap Agen"></td>
                </tr>
                <tr>
                    <td class="align-middle">No. WhatsApp</td>
                    <td><input class="form-control" type="text" name="phone" required placeholder="08xxxxxxxxxx"></td>
                </tr>
                <tr>
                    <td class="align-middle">Username</td>
                    <td><input class="form-control" type="text" name="username" required placeholder="Username Login"></td>
                </tr>
                <tr>
                    <td class="align-middle">Password</td>
                    <td><input class="form-control" type="password" name="password" required placeholder="Password Login"></td>
                </tr>
                <tr>
                    <td class="align-middle">Saldo Awal</td>
                    <td><input class="form-control" type="number" name="balance" value="0" min="0"></td>
                </tr>
            </table>
            <div style="padding: 10px;">
                <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Simpan</button>
                <a href="./?agent=manage&session=<?= $session; ?>" class="btn bg-warning"><i class="fa fa-times"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
