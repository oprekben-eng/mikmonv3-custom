<?php
/**
 * Standalone Billing Management Page
 */

if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$db = Database::getConnection();
if (!$db) {
    echo "<div class='alert bg-danger'>Gagal menghubungkan ke database mikhmon_agent. Silakan periksa pengaturan database.</div>";
    // Don't exit, let it render the rest of the page to avoid PACE hang
} else {
    try {
        $billing_list = $db->query("SELECT * FROM billing ORDER BY due_date ASC")->fetchAll();
    } catch (Exception $e) {
        echo "<div class='alert bg-danger text-wrap'>Database Error: " . $e->getMessage() . "</div>";
        $billing_list = [];
    }
}

$billing_template = Database::getSetting('fonnte_billing_template', '');
?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-money"></i> Manajemen Penagihan Standalone</h3>
            </div>
            <div class="card-body">
                <div class="pd-10">
                    <button class="btn bg-primary" onclick="$('#addBillingModal').show()"><i class="fa fa-plus"></i> Tambah Pelanggan Tagihan</button>
                    <button class="btn bg-info" onclick="$('#templateModal').show()"><i class="fa fa-whatsapp"></i> Edit Template WA</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>WA Contact</th>
                                <th>Nominal</th>
                                <th>Jatuh Tempo</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($billing_list)): ?>
                                <tr><td colspan="6" class="text-center">Belum ada data pelanggan tagihan.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($billing_list as $b): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($b['username']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($b['phone']) ?></td>
                                    <td class="align-middle">Rp <?= number_format($b['amount'], 0, ',', '.') ?></td>
                                    <td class="align-middle">Tgl <?= $b['due_date'] ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($b['description']) ?></td>
                                    <td class="align-middle text-center">
                                        <button onclick="sendBillingWA(<?= $b['id'] ?>)" class="btn bg-whatsapp btn-sm" title="Kirim WA Penagihan"><i class="fa fa-whatsapp"></i></button>
                                        <button onclick="editBilling(<?= htmlspecialchars(json_encode($b)) ?>)" class="btn bg-info btn-sm" title="Edit"><i class="fa fa-edit"></i></button>
                                        <a href="./process/pbilling.php?action=delete&id=<?= $b['id'] ?>&session=<?= $session ?>" onclick="return confirm('Hapus data penagihan ini?')" class="btn bg-danger btn-sm" title="Hapus"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="addBillingModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:#fff; margin:5% auto; padding:20px; border:1px solid #888; width:400px; border-radius:5px; color:#333;">
        <h4 id="modalTitle">Tambah Pelanggan Tagihan</h4>
        <div id="billingMsg"></div>
        <form id="billingForm">
            <input type="hidden" name="id" id="b_id">
            <div class="p-5">
                <label>Nama Pelanggan</label>
                <input type="text" name="username" id="b_username" class="form-control" placeholder="Contoh: Fauzan Shop" required>
            </div>
            <div class="p-5">
                <label>Nomor WA (Awalan 0 atau 62)</label>
                <input type="text" name="phone" id="b_phone" class="form-control" placeholder="Contoh: 08123456789" required>
            </div>
            <div class="p-5">
                <label>Nominal Tagihan</label>
                <input type="text" name="amount" id="b_amount" class="form-control" placeholder="Contoh: 150.000" required>
            </div>
            <div class="p-5">
                <label>Tanggal Jatuh Tempo (1-31)</label>
                <input type="number" name="due_date" id="b_due_date" min="1" max="31" class="form-control" placeholder="Contoh: 10" required>
            </div>
            <div class="p-5">
                <label>Keterangan / Paket</label>
                <input type="text" name="description" id="b_description" class="form-control" placeholder="Contoh: Paket 10Mbps">
            </div>
            <div class="pd-10 text-right">
                <button type="button" class="btn bg-secondary" onclick="$('#addBillingModal').hide();">Batal</button>
                <button type="submit" class="btn bg-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Template -->
<div id="templateModal" class="modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background:#fff; margin:5% auto; padding:20px; border:1px solid #888; width:500px; border-radius:5px; color:#333;">
        <h4>Pengaturan Template WA Penagihan</h4>
        <p class="text-xs text-muted">Variabel: {username}, {amount}, {due_date}, {description}</p>
        <div id="tplMsg"></div>
        <form id="tplForm">
            <textarea name="fonnte_billing_template" class="form-control" rows="10" required><?= htmlspecialchars($billing_template) ?></textarea>
            <div class="pd-10 text-right">
                <button type="button" class="btn bg-secondary" onclick="$('#templateModal').hide()">Tutup</button>
                <button type="submit" class="btn bg-primary">Simpan Template</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#billingForm').on('submit', function(e){
        e.preventDefault();
        var action = $('#b_id').val() ? 'edit' : 'add';
        $.post('./process/pbilling.php?action=' + action + '&session=<?= $session ?>', $(this).serialize(), function(data){
            $('#billingMsg').html(data);
            if(data.indexOf('success') !== -1) {
                setTimeout(function(){ location.reload(); }, 1000);
            }
        });
    });

    $('#tplForm').on('submit', function(e){
        e.preventDefault();
        $.post('./process/pformat.php?action=billing_template&session=<?= $session ?>', $(this).serialize(), function(data){
            $('#tplMsg').html(data);
        });
    });
});

function editBilling(data) {
    $('#modalTitle').text('Edit Pelanggan Tagihan');
    $('#b_id').val(data.id);
    $('#b_username').val(data.username);
    $('#b_phone').val(data.phone);
    $('#b_amount').val(data.amount);
    $('#b_due_date').val(data.due_date);
    $('#b_description').val(data.description);
    $('#addBillingModal').show();
}

function sendBillingWA(id) {
    if(!confirm('Kirim pesan penagihan via WhatsApp?')) return;
    $.get('./process/send_billing_wa.php?id=' + id + '&session=<?= $session ?>', function(data){
        alert(data);
    });
}
</script>

<style>
.bg-danger-light { background-color: #fff5f5 !important; }
.bg-whatsapp { background-color: #25d366 !important; color: #fff !important; }
.text-xs { font-size: 11px; }
.ml-5 { margin-left: 5px; }
.text-wrap { white-space: normal !important; }
</style>
