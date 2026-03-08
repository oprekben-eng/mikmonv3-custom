<?php
/**
 * Format Voucher Settings Page
 * Accessible from admin Settings dropdown
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

// Load current settings
$vc_mode   = Database::getSetting('vc_mode', 'sama');
$vc_char   = Database::getSetting('vc_char', 'kombinasi');
$vc_len    = Database::getSetting('vc_len', '8');
$vc_prefix = Database::getSetting('vc_prefix', '');
$vc_ucase  = Database::getSetting('vc_ucase', '1');
?>
<style>
.panel-cfg {
    border: 1px solid rgba(128,128,128,0.3);
    margin-bottom: 20px;
    background: inherit;
}
.panel-cfg-header {
    background: rgba(128,128,128,0.1);
    border-bottom: 1px solid rgba(128,128,128,0.3);
    padding: 10px 15px;
    font-weight: bold;
    color: inherit;
}
.panel-cfg-body {
    padding: 15px;
    color: inherit;
}
.cfg-label {
    display: block;
    color: inherit;
    opacity: 0.75;
    margin-bottom: 8px;
    font-size: 13px;
}
.cfg-note {
    color: inherit;
    opacity: 0.55;
    font-size: 12px;
    display: block;
    margin-top: 5px;
}
.flex-between {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.preview-box {
    border: 1px dashed #6c5ce7;
    border-radius: 4px;
    background: inherit;
}
.ticket-preview {
    display: inline-block;
    border: 1px solid rgba(128,128,128,0.3);
    padding: 15px 30px;
    background: inherit;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    min-width: 250px;
}
</style>

<div class="row">
<div class="col-12">
<div class="card box-bordered">
    <div class="card-header">
        <h3><i class="fa fa-gear"></i> Pengaturan Format Voucher</h3>
    </div>
    <div class="card-body">
        <div id="formatMsg"></div>
        <form id="formatVoucherForm" method="post" autocomplete="off">
        
        <!-- Section 1 -->
        <div class="panel-cfg">
            <div class="panel-cfg-header"><i class="fa fa-key"></i> Konfigurasi Username & Password</div>
            <div class="panel-cfg-body">
                <label class="cfg-label">Username dan Password</label>
                <div>
                    <label class="mr-20" style="cursor:pointer;">
                        <input type="radio" name="vc_mode" value="sama" <?= $vc_mode == 'sama' ? 'checked' : '' ?>> Sama
                    </label>
                    <label style="cursor:pointer;">
                        <input type="radio" name="vc_mode" value="beda" <?= $vc_mode == 'beda' ? 'checked' : '' ?>> Berbeda
                    </label>
                </div>
                <span class="cfg-note">Jika "Sama", password akan sama dengan username</span>
            </div>
        </div>

        <!-- Section 2 -->
        <div class="panel-cfg">
            <div class="panel-cfg-header"><i class="fa fa-user"></i> Pengaturan Username</div>
            <div class="panel-cfg-body">
                <div class="row">
                    <div class="col-6">
                        <label class="cfg-label">Tipe Karakter Username</label>
                        <select class="form-control" name="vc_char" id="vc_char">
                            <option value="kombinasi" <?= $vc_char == 'kombinasi' ? 'selected' : '' ?>>Kombinasi Angka & Huruf</option>
                            <option value="huruf" <?= $vc_char == 'huruf' ? 'selected' : '' ?>>Huruf saja</option>
                            <option value="angka" <?= $vc_char == 'angka' ? 'selected' : '' ?>>Angka saja</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="cfg-label">Jumlah Digit Username</label>
                        <input class="form-control" type="number" name="vc_len" id="vc_len" min="4" max="20" value="<?= htmlspecialchars($vc_len) ?>" required>
                        <span class="cfg-note">Minimal 4, maksimal 20 karakter</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3 -->
        <div class="panel-cfg">
            <div class="panel-cfg-header"><i class="fa fa-wrench"></i> Pengaturan Tambahan</div>
            <div class="panel-cfg-body">
                <div class="flex-between mb-15">
                    <div>
                        <span class="cfg-note" style="margin:0;">Opsional: Jika Prefix diisi, huruf awal voucher akan selalu menggunakan kode ini.</span>
                    </div>
                    <div style="display:flex; align-items:center;">
                        <input type="text" class="form-control" style="width:100px; margin-right:10px;" name="vc_prefix" id="vc_prefix" maxlength="10" placeholder="Prefix" value="<?= htmlspecialchars($vc_prefix) ?>">
                        <label class="mb-0" style="font-size:13px; opacity:0.7;">Gunakan Prefix untuk Username</label>
                    </div>
                </div>
                <div class="flex-between">
                    <div>
                        <span class="cfg-note" style="margin:0;">Jika dicentang, semua huruf akan menjadi kapital</span>
                    </div>
                    <div style="display:flex; align-items:center;">
                        <input type="checkbox" name="vc_ucase" value="1" id="vc_ucase" style="margin-right:10px;" <?= $vc_ucase == '1' ? 'checked' : '' ?>>
                        <label for="vc_ucase" class="mb-0" style="font-size:13px; opacity:0.7; cursor:pointer;">Huruf Kapital (Uppercase)</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4 Preview -->
        <div class="panel-cfg preview-box mb-10">
            <div class="panel-cfg-body text-center">
                <div class="text-primary text-bold" style="font-size: 15px;"><i class="fa fa-eye"></i> Preview Voucher</div>
                <div class="cfg-note mb-15">Contoh voucher yang akan di-generate</div>

                <div class="ticket-preview">
                    <div style="opacity:0.6; font-size:11px; margin-bottom:5px;">Username</div>
                    <div id="previewUser" style="font-size:22px; font-weight:bold; letter-spacing:2px;">-</div>
                    
                    <div id="previewPassWrap" style="display:none; margin-top:15px; padding-top:15px; border-top:1px dashed rgba(128,128,128,0.3);">
                        <div style="opacity:0.6; font-size:11px; margin-bottom:5px;">Password</div>
                        <div id="previewPass" style="font-size:22px; font-weight:bold; letter-spacing:2px;">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding: 10px 0; display:flex; justify-content:flex-end;">
            <button type="button" class="btn bg-warning mr-5" onclick="resetDefaults()"><i class="fa fa-undo"></i> Reset Default</button>
            <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Simpan Pengaturan</button>
        </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
$(document).ready(function(){
    generatePreview();

    $('#formatVoucherForm').on('submit', function(e){
        e.preventDefault();
        
        // Handle unchecked checkbox values
        var formData = $(this).serializeArray();
        if (!$('#vc_ucase').is(':checked')) {
            formData.push({name: 'vc_ucase', value: '0'});
        }

        $.post('./process/pformat.php?session=<?= $session; ?>', $.param(formData), function(data){
            $('#formatMsg').html(data);
            generatePreview();
            setTimeout(function(){ $('#formatMsg').html(''); }, 3000);
        });
    });

    // Update preview on any change
    $('input[name="vc_mode"], #vc_char, #vc_len, #vc_prefix, #vc_ucase').on('change keyup', function(){
        generatePreview();
    });
});

function generatePreview() {
    var mode = $('input[name="vc_mode"]:checked').val();
    var charType = $('#vc_char').val();
    var len = parseInt($('#vc_len').val()) || 8;
    var prefix = $('#vc_prefix').val();
    var ucase = $('#vc_ucase').is(':checked') ? '1' : '0';
    
    var chars = '';
    if (charType == 'huruf') {
        chars = ucase == '1' ? 'abcdefghjkmnprstuvwxyzABCDEFGHJKMNPRSTUVWXYZ' : 'abcdefghjkmnprstuvwxyz';
    } else if (charType == 'angka') {
        chars = '23456789';
    } else {
        chars = ucase == '1' ? 'abcdefghjkmnprstuvwxyz23456789ABCDEFGHJKMNPRSTUVWXYZ' : 'abcdefghjkmnprstuvwxyz23456789';
    }
    
    var username = prefix;
    for (var i = 0; i < len; i++) {
        username += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    if (ucase == '1') {
        username = username.toUpperCase();
    } else {
        username = username.toLowerCase();
    }
    
    $('#previewUser').text(username);

    if (mode == 'sama') {
        $('#previewPassWrap').hide();
    } else {
        var password = '';
        for (var j = 0; j < len; j++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        if (ucase == '1') { password = password.toUpperCase(); } 
        else { password = password.toLowerCase(); }
        
        $('#previewPass').text(password);
        $('#previewPassWrap').show();
    }
}

function resetDefaults() {
    $('input[name="vc_mode"][value="sama"]').prop('checked', true);
    $('#vc_char').val('kombinasi');
    $('#vc_len').val('8');
    $('#vc_prefix').val('');
    $('#vc_ucase').prop('checked', true);
    generatePreview();
}
</script>
