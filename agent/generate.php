<?php
/**
 * Agent Voucher Generation Content
 */
if (!isset($_SESSION['agent'])) exit;
$agent = $_SESSION['agent'];
$db = Database::getConnection();

// Get configured profiles for this agent
$stmt = $db->prepare("SELECT * FROM agent_prices WHERE agent_id = ? ORDER BY sell_price ASC");
$stmt->execute([$agent['id']]);
$profiles = $stmt->fetchAll();

?>
<style>
    .profile-card { border: 2px solid transparent; border-radius: 8px; cursor: pointer; transition: 0.2s; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 15px; text-align: center; margin-bottom: 20px; }
    .profile-card:hover { border-color: #6C63FF; transform: translateY(-3px); }
    .profile-card.selected { border-color: #6C63FF; background: #f0f4ff; box-shadow: 0 4px 10px rgba(108, 99, 255, 0.2); }
    .profile-name { font-size: 20px; font-weight: bold; color: #2c3e50; }
    .profile-price { font-size: 24px; color: #e74c3c; font-weight: bold; margin: 10px 0; }
    .profile-buy { color: #7f8c8d; font-size: 12px; }
    
    #generateFormArea { display: none; margin-top: 20px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
</style>

<div class="row">
    <div class="col-12">
        <h3 class="mb-20"><i class="fa fa-shopping-cart"></i> Pilih Paket / Profil</h3>
        <?php if (count($profiles) == 0): ?>
            <div class="bg-warning pd-10 radius-3"><i class="fa fa-info-circle"></i> Admin belum mengatur harga paket untuk Anda. Hubungi Administrator.</div>
        <?php endif; ?>
    </div>
</div>

<div class="row" style="margin-left:-10px; margin-right:-10px;">
    <?php foreach ($profiles as $p): ?>
    <div class="agent-col-3">
        <div class="profile-card" onclick="selectProfile('<?= htmlspecialchars($p['profile']) ?>', <?= $p['buy_price'] ?>, <?= $p['sell_price'] ?>, this)">
            <div class="profile-name"><?= htmlspecialchars($p['profile']) ?></div>
            <div class="profile-price">Rp <?= number_format($p['sell_price'], 0, ',', '.') ?></div>
            <div class="profile-buy">Harga Modal: Rp <?= number_format($p['buy_price'], 0, ',', '.') ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="generateFormArea">
    <h3 class="mb-10"><i class="fa fa-flash"></i> Proses Transaksi</h3>
    <div class="row">
        <div class="col-6 col-sm-12 pl-0">
            <table class="table table-bordered mb-20">
                <tr><td style="width: 40%">Profil Terpilih</td><td id="lbl_profile" class="text-bold text-primary">-</td></tr>
                <tr><td>Saldo Saat Ini</td><td class="text-bold text-success">Rp <?= number_format($agent['balance'], 0, ',', '.') ?></td></tr>
                <tr><td>Harga Total (HPP)</td><td id="lbl_total" class="text-bold text-danger">Rp 0</td></tr>
                <tr><td>Sisa Saldo</td><td id="lbl_sisa" class="text-bold">Rp 0</td></tr>
            </table>
        </div>
        
        <div class="col-6 col-sm-12 pr-0">
            <form id="generateForm" method="post" action="../process/generate_agent.php">
                <input type="hidden" name="profile" id="input_profile" value="">
                <input type="hidden" name="qty" value="1">
                
                <div class="group">
                    <label>Nomor WhatsApp Pelanggan (opsional)</label>
                    <input class="form-control" type="text" name="customer_phone" placeholder="08xxxxxxxxxx" autocomplete="off">
                    <small class="text-muted">Jika diisi, rincian voucher akan dikirim otomatis ke pelanggan (jika diaktifkan).</small>
                </div>
                
                <button type="button" class="btn bg-primary btn-block text-bold pt-10 pb-10 mt-10" id="btnProcess" style="font-size: 16px;">
                    <i class="fa fa-print"></i> Proses & Cetak Voucher
                </button>
            </form>
            <div id="genMsg" class="mt-10"></div>
        </div>
    </div>
</div>

<script>
var agentBalance = <?= $agent['balance'] ?>;
var selectedBuyPrice = 0;

function selectProfile(profile, buyPrice, sellPrice, el) {
    $('.profile-card').removeClass('selected');
    $(el).addClass('selected');
    
    selectedBuyPrice = buyPrice;
    $('#lbl_profile').text(profile);
    $('#input_profile').val(profile);
    
    updateTotals();
    $('#generateFormArea').slideDown();
    
    // Smooth scroll
    $('html, body').animate({
        scrollTop: $("#generateFormArea").offset().top - 50
    }, 500);
}

function updateTotals() {
    var total = selectedBuyPrice * 1; // qty is 1
    var sisa = agentBalance - total;
    
    $('#lbl_total').text('Rp ' + total.toLocaleString('id-ID'));
    var sisaEl = $('#lbl_sisa');
    sisaEl.text('Rp ' + sisa.toLocaleString('id-ID'));
    
    if (sisa < 0) {
        sisaEl.removeClass('text-success').addClass('text-danger');
        $('#btnProcess').prop('disabled', true).html('<i class="fa fa-ban"></i> Saldo Tidak Cukup');
    } else {
        sisaEl.removeClass('text-danger').addClass('text-success');
        $('#btnProcess').prop('disabled', false).html('<i class="fa fa-print"></i> Proses & Cetak Voucher');
    }
}

$(document).ready(function() {
    $('#btnProcess').click(function() {
        if (!confirm('Apakah pelanggan sudah membayar? Saldo Anda akan dipotong senilai HPP paket.')) return;
        
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-circle-o-notch fa-spin"></i> Memproses...');
        $('#genMsg').html('');
        
        $.ajax({
            type: "POST",
            url: "../process/generate_agent.php",
            data: $('#generateForm').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#genMsg').html('<div class="bg-success pd-10 radius-3 mb-10 text-white"><i class="fa fa-check"></i> ' + res.message + '</div>');
                    setTimeout(function() {
                        window.location = '?page=generate_history&id=' + res.id;
                    }, 500);
                } else {
                    $('#genMsg').html('<div class="bg-danger pd-10 radius-3 text-white"><i class="fa fa-ban"></i> ' + res.message + '</div>');
                    btn.prop('disabled', false).html('<i class="fa fa-print"></i> Proses Ulang');
                }
            },
            error: function() {
                $('#genMsg').html('<div class="bg-danger pd-10 radius-3 text-white"><i class="fa fa-ban"></i> Terjadi kesalahan jaringan / server.</div>');
                btn.prop('disabled', false).html('<i class="fa fa-print"></i> Proses Ulang');
            }
        });
    });
});
</script>
