<?php
/**
 * Fonnte WhatsApp Settings Page
 * Configure API token and message template
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

// Load current settings
$fonnte_token    = Database::getSetting('fonnte_token', '');
$fonnte_template = Database::getSetting('fonnte_template', "Halo! Berikut voucher Anda:\n\nUsername: {username}\nPassword: {password}\nPaket: {profile}\nHarga: {price}\n\nTerima kasih!\n- {agent_name}");
?>

<div class="row">
<div class="col-8">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-whatsapp"></i> Konfigurasi Fonnte WhatsApp</h3>
    </div>
    <div class="card-body">
        <div id="fonnteMsg"></div>
        <form id="fonnteForm" method="post" autocomplete="off">
        <table class="table">
            <tr>
                <td class="align-middle" style="width:30%;">API Token</td>
                <td>
                    <input class="form-control" type="text" name="fonnte_token" id="fonnte_token" 
                           value="<?= htmlspecialchars($fonnte_token) ?>" 
                           placeholder="Masukkan token dari fonnte.com">
                </td>
            </tr>
            <tr>
                <td class="align-middle">Template Pesan</td>
                <td>
                    <textarea class="form-control" name="fonnte_template" id="fonnte_template" 
                              rows="10" placeholder="Template pesan WhatsApp..."><?= htmlspecialchars($fonnte_template) ?></textarea>
                </td>
            </tr>
        </table>
        <div style="padding: 10px;">
            <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Simpan</button>
            <button type="button" class="btn bg-info" onclick="testWA()"><i class="fa fa-paper-plane"></i> Test Kirim</button>
        </div>
        </form>

        <div id="testWaForm" style="display:none; margin-top:15px; padding:15px; border:1px solid #ddd; border-radius:5px;">
            <h4><i class="fa fa-paper-plane"></i> Test Kirim Pesan WhatsApp</h4>
            <table class="table">
                <tr>
                    <td style="width:30%;">Nomor WhatsApp</td>
                    <td><input class="form-control" type="text" id="testPhone" placeholder="08xxxxxxxxxx"></td>
                </tr>
                <tr>
                    <td>Pesan</td>
                    <td><textarea class="form-control" id="testMessage" rows="4">Ini adalah pesan test dari Mikhmon.</textarea></td>
                </tr>
            </table>
            <button type="button" class="btn bg-success" onclick="sendTestWA()"><i class="fa fa-send"></i> Kirim Test</button>
            <button type="button" class="btn bg-warning" onclick="$('#testWaForm').hide()"><i class="fa fa-close"></i> Tutup</button>
            <div id="testResult" style="margin-top:10px;"></div>
        </div>
    </div>
</div>
</div>

<div class="col-4">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> Variabel Template</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><td><code>{username}</code></td><td>Username voucher</td></tr>
            <tr><td><code>{password}</code></td><td>Password voucher</td></tr>
            <tr><td><code>{profile}</code></td><td>Nama profil/paket</td></tr>
            <tr><td><code>{price}</code></td><td>Harga (Rupiah)</td></tr>
            <tr><td><code>{agent_name}</code></td><td>Nama agen</td></tr>
            <tr><td><code>{customer_phone}</code></td><td>Nomor pelanggan</td></tr>
            <tr><td><code>{session}</code></td><td>Nama session router</td></tr>
        </table>
    </div>
</div>

<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-question-circle"></i> Cara Setup</h3>
    </div>
    <div class="card-body">
        <ol>
            <li>Daftar di <a href="https://fonnte.com" target="_blank">fonnte.com</a></li>
            <li>Hubungkan nomor WhatsApp</li>
            <li>Dapatkan <b>API Token</b></li>
            <li>Masukkan token di form ini</li>
            <li>Klik <b>Test Kirim</b> untuk verifikasi</li>
        </ol>
    </div>
</div>
</div>
</div>

<script>
$(document).ready(function(){
    $('#fonnteForm').on('submit', function(e){
        e.preventDefault();
        $.post('./process/pformat.php?action=fonnte&session=<?= $session; ?>', $(this).serialize() + '&action=save_fonnte', function(data){
            $('#fonnteMsg').html(data);
            setTimeout(function(){ $('#fonnteMsg').html(''); }, 3000);
        });
    });
});

function testWA() {
    $('#testWaForm').toggle();
    // Pre-fill with template preview
    var template = $('#fonnte_template').val();
    template = template.replace('{username}', 'test123')
                       .replace('{password}', 'test123')
                       .replace('{profile}', '1d-3gb')
                       .replace('{price}', 'Rp 5.000')
                       .replace('{agent_name}', 'Admin')
                       .replace('{customer_phone}', '-')
                       .replace('{session}', '<?= $session; ?>');
    $('#testMessage').val(template);
}

function sendTestWA() {
    var phone = $('#testPhone').val();
    var message = $('#testMessage').val();
    if (!phone) {
        $('#testResult').html('<div class="bg-danger pd-5 radius-3" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> Masukkan nomor WhatsApp.</div>');
        return;
    }
    $('#testResult').html('<i class="fa fa-circle-o-notch fa-spin"></i> Mengirim...');
    $.post('./process/test_wa.php?session=<?= $session; ?>', {phone: phone, message: message}, function(data){
        $('#testResult').html(data);
    });
}
</script>
