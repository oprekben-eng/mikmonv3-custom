<?php
/**
 * Agent Transaction History Content
 */
if (!isset($_SESSION['agent'])) exit;
$agent = $_SESSION['agent'];
$db = Database::getConnection();

$stmt = $db->prepare("SELECT * FROM voucher_transactions WHERE agent_id = ? ORDER BY id DESC");
$stmt->execute([$agent['id']]);
$transactions = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-history"></i> Semua Transaksi</h3>
            </div>
            <div class="card-body">
                <div id="waStatusMsg"></div>
                <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Waktu</th>
                            <th>Profil</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Status WA & Nomor</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td class="align-middle"><?= $t['id'] ?></td>
                            <td class="align-middle"><?= htmlspecialchars($t['created_at']) ?></td>
                            <td class="align-middle"><b><?= htmlspecialchars($t['profile']) ?></b></td>
                            <td class="align-middle"><span class="bg-primary text-white pd-5 radius-3"><?= htmlspecialchars($t['username']) ?></span></td>
                            <td class="align-middle"><span class="bg-success text-white pd-5 radius-3"><?= htmlspecialchars($t['password']) ?></span></td>
                            <td class="align-middle">
                                <?php if ($t['wa_sent']): ?>
                                    <div class="text-success"><i class="fa fa-check-circle"></i> Terkirim</div>
                                    <small><?= htmlspecialchars($t['customer_phone']) ?></small>
                                <?php else: ?>
                                    <div class="text-warning"><i class="fa fa-times-circle"></i> Tidak</div>
                                    <small><?= htmlspecialchars($t['customer_phone'] ?? '-') ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm bg-info text-white" onclick="resendWa(<?= $t['id'] ?>, '<?= $t['customer_phone'] ?>')" title="Kirim Ulang ke WA"><i class="fa fa-whatsapp"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($transactions) == 0): ?>
                        <tr><td colspan="7" class="text-center">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resendWa(id, phone) {
    window.parent.postMessage("resize_frame", "*"); // optional 
    var pr = prompt("Kirim rincian voucher ini ke nomor WhatsApp:", phone);
    if (pr != null && pr.trim() !== "") {
        $('#waStatusMsg').html('<div class="bg-warning pd-5 radius-3 mb-10"><i class="fa fa-circle-o-notch fa-spin"></i> Mengirim pesan ke ' + pr + '...</div>');
        $.ajax({
            url: '../process/send_wa.php',
            type: 'POST',
            data: { id: id, phone: pr },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#waStatusMsg').html('<div class="bg-success pd-5 radius-3 mb-10 text-white"><i class="fa fa-check"></i> ' + res.message + '</div>');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $('#waStatusMsg').html('<div class="bg-danger pd-5 radius-3 mb-10 text-white"><i class="fa fa-ban"></i> ' + res.message + '</div>');
                }
            },
            error: function() {
                $('#waStatusMsg').html('<div class="bg-danger pd-5 radius-3 mb-10 text-white"><i class="fa fa-ban"></i> Terjadi kesalahan jaringan.</div>');
            }
        });
    }
}
</script>
