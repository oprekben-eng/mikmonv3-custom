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
$total_vouchers = count($transactions);
?>

<style>
    /* Styling for the cards */
    .v-grid { display: flex; flex-wrap: wrap; margin-left: -10px; margin-right: -10px; }
    .v-col { width: 20%; padding: 10px; } /* 5 per row max on large screens */
    .v-card {
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .v-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
    .v-title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
    .v-pass { background: rgba(128,128,128,0.1); font-family: monospace; padding: 5px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    .v-profile { font-size: 14px; font-weight: bold; opacity: 0.8; }
    .v-price { font-size: 13px; opacity: 0.7; margin-bottom: 10px; }
    .v-status { 
        display: inline-block; 
        background: #e8f8f5; 
        color: #1abc9c; 
        padding: 2px 15px; 
        border-radius: 20px; 
        font-size: 12px; 
        font-weight: bold; 
        margin-bottom: 15px; 
    }
    .v-date { font-size: 11px; opacity: 0.6; margin-bottom: 15px; flex-grow: 1; }
    .v-actions { display: flex; gap: 5px; }
    .v-btn {
        flex: 1;
        padding: 8px 5px;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        color: #fff;
    }
    .v-btn-copy { background: #3b82f6; transition: 0.2s;}
    .v-btn-copy:hover { background: #2563eb; }
    
    @media (max-width: 1200px) { .v-col { width: 25%; } } /* 4 per row */
    @media (max-width: 992px) { .v-col { width: 33.333%; } } /* 3 per row */
    @media (max-width: 768px) { .v-col { width: 50%; } } /* 2 per row */
    @media (max-width: 480px) { .v-col { width: 100%; } } /* 1 per row */

    #toast-msg {
        position: fixed; top: 20px; right: 20px; background: #2ecc71; color: white; padding: 10px 20px; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        z-index: 9999; display: none; font-weight: bold;
    }
</style>

<div id="toast-msg"><i class="fa fa-check"></i> Disalin!</div>

<div class="row">
    <div class="col-12 mb-20" style="border-bottom: 1px solid rgba(128,128,128,0.2); padding-bottom: 15px;">
        <h3 style="margin-top:0;"><i class="fa fa-ticket"></i> Vouchers</h3>
        <div style="font-size: 14px; opacity: 0.8;">Total Vouchers: <?= $total_vouchers ?></div>
    </div>
</div>

<div class="v-grid">
    <?php foreach ($transactions as $t): ?>
    <div class="v-col">
        <div class="v-card card box-bordered">
            <div class="v-title"><?= htmlspecialchars($t['username']) ?></div>
            <div class="v-pass"><?= htmlspecialchars($t['password']) ?></div>
            <div class="v-profile"><?= htmlspecialchars($t['profile']) ?></div>
            <div class="v-price">Price: Rp <?= number_format($t['price'], 0, ',', '.') ?></div>
            <div><span class="v-status">Active</span></div>
            <div class="v-date"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></div>
            
            <div class="v-actions">
                <button class="v-btn v-btn-copy" onclick="copyVoucher('<?= htmlspecialchars($t['username']) ?>', '<?= htmlspecialchars($t['password']) ?>')">
                    <i class="fa fa-clone"></i> Copy/Salin
                </button>
            </div>
            
            <?php if (!empty($t['customer_phone'])): ?>
            <div style="font-size: 11px; margin-top: 10px; color: <?= $t['wa_sent'] ? '#2ecc71' : '#e67e22' ?>">
                <i class="fa fa-whatsapp"></i> <?= htmlspecialchars($t['customer_phone']) ?> 
                <span style="text-decoration: underline; cursor:pointer;" onclick="resendWa(<?= $t['id'] ?>, '<?= $t['customer_phone'] ?>')">(Kirim Ulang)</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php if ($total_vouchers == 0): ?>
    <div class="col-12 text-center" style="margin-top: 50px; color: #888;">
        <i class="fa fa-ticket" style="font-size: 50px; color: #555; margin-bottom: 15px;"></i>
        <p>Anda belum men-generate voucher apapun.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function copyVoucher(user, pass) {
    var text = "Username: " + user + (user !== pass ? "\nPassword: " + pass : "");
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast();
        });
    } else {
        var tempInput = document.createElement("textarea");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        showToast();
    }
}

function showToast() {
    $('#toast-msg').fadeIn('fast').delay(2000).fadeOut('fast');
}

function resendWa(id, phone) {
    var pr = prompt("Kirim rincian voucher ini ke nomor WhatsApp:", phone);
    if (pr != null && pr.trim() !== "") {
        $.ajax({
            url: '../process/send_wa.php',
            type: 'POST',
            data: { id: id, phone: pr },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('WhatsApp Terkirim!');
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan jaringan.');
            }
        });
    }
}
</script>
