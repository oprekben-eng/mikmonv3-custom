<?php
/**
 * Agent Dashboard Redesign (Dark Theme)
 */
if (!isset($_SESSION['agent'])) exit;
$agent = $_SESSION['agent'];
$db = Database::getConnection();

// Get stats
$stmt = $db->prepare("SELECT COUNT(*) as total_trx, SUM(price) as total_spent FROM voucher_transactions WHERE agent_id = ?");
$stmt->execute([$agent['id']]);
$stats = $stmt->fetch();

$total_trx = $stats['total_trx'] ?? 0;
$total_spent = $stats['total_spent'] ?? 0;

$stmt = $db->prepare("SELECT COUNT(*) as today_trx FROM voucher_transactions WHERE agent_id = ? AND DATE(created_at) = CURDATE()");
$stmt->execute([$agent['id']]);
$today_trx = $stmt->fetch()['today_trx'] ?? 0;

// Get Voucher Profiles
$stmt = $db->prepare("SELECT * FROM agent_prices WHERE agent_id = ? ORDER BY sell_price ASC");
$stmt->execute([$agent['id']]);
$profiles = $stmt->fetchAll();

?>
<style>
/* Dashboard Specific Dark Theme Styles */
.dash-balance-card {
    background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    color: #fff;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(108, 92, 231, 0.2);
}
.dash-balance-label { font-size: 14px; opacity: 0.9; margin-bottom: 15px; }
.dash-balance-amount { font-size: 40px; font-weight: bold; margin-bottom: 10px; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); }
.dash-agent-code { font-size: 13px; opacity: 0.8; }

.section-title {
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}
.section-title i { margin-right: 8px; }

/* Dashboard Summary Widgets */
.summary-grid {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
}
.sum-box {
    flex: 1;
    padding: 20px 15px;
    border-radius: 6px;
    color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}
.sum-val { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
.sum-label { font-size: 12px; opacity: 0.9; display: flex; align-items: center; }
.sum-label i { margin-right: 5px; }

/* Exact widget colors from screenshot */
.sum-box.blue { background: #1cb5e0; }
.sum-box.green { background: #2ed573; }
.sum-box.yellow { background: #ffa502; color: #fff; }
.sum-box.red { background: #ff4757; }

/* Voucher Profiles Grid */
.voucher-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}
.voucher-wrapper {
    flex: 0 0 calc(50% - 7.5px); /* Exactly 2 columns */
}
.voucher-item {
    background: linear-gradient(135deg, #7452d3 0%, #8c6eec 100%);
    border-radius: 8px;
    padding: 20px;
    color: #fff;
    position: relative;
    cursor: pointer;
    transition: 0.2s;
    border: 1px solid rgba(255,255,255,0.05);
    height: 100%; /* fill wrapper height */
}
.voucher-item:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(116, 82, 211, 0.3); }
.v-profile { font-size: 16px; font-weight: bold; margin-bottom: 15px; }
.v-sell-wrap {
    background: rgba(255, 255, 255, 0.15);
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}
.v-sell { font-size: 22px; font-weight: bold; }
.v-meta { font-size: 11px; opacity: 0.8; line-height: 1.6; }

@media (max-width: 900px) {
    .summary-grid { flex-wrap: wrap; }
    .sum-box { flex: 0 0 calc(50% - 7.5px); }
    .voucher-wrapper { flex: 0 0 100%; } /* full width on mobile */
}

/* Modal Generate Voucher Design matching screenshot */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}
.modal-gen {
    background: #fdfdfd;
    width: 600px;
    max-width: 95%;
    border-radius: 6px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    overflow: hidden;
    color: #333;
    font-family: inherit;
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.modal-gen-header {
    background: #fff;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: bold;
    border-bottom: 2px solid #eaebf3;
    display: flex;
    align-items: center;
    color: #4a5568;
}
.modal-gen-header i { margin-right: 10px; color: #5b5fc7; }

.modal-gen-body {
    padding: 20px;
}
.mg-paket-banner {
    background: #6a74e7;
    color: #fff;
    padding: 12px 18px;
    border-radius: 4px;
    font-weight: bold;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(106, 116, 231, 0.3);
}

.mg-group { margin-bottom: 15px; }
.mg-label {
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    color: #4a5568;
}
.mg-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e0;
    border-radius: 4px;
    font-size: 14px;
    color: #4a5568;
    outline: none;
}
.mg-input:focus { border-color: #6a74e7; box-shadow: 0 0 0 2px rgba(106, 116, 231, 0.2); }

.modal-gen-footer {
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    border-top: 1px solid #eaebf3;
}
.btn-gen {
    background: linear-gradient(135deg, #6c5ce7 0%, #8c6eec 100%);
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
    transition: 0.2s;
}
.btn-gen:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(108, 92, 231, 0.4); }
.btn-gen:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
</style>

<div class="row">
    <!-- Balance Header Card -->
    <div class="col-12">
        <div class="dash-balance-card">
            <div class="dash-balance-label">Your Current Balance</div>
            <div class="dash-balance-amount">Rp <?= number_format($agent['balance'], 0, ',', '.') ?></div>
            <div class="dash-agent-code">Agent Code: <?= strtoupper(substr($agent['username'], 0, 6)) ?></div>
        </div>
    </div>
</div>

<div class="section-title">
    <i class="fa fa-pie-chart"></i> Summary
</div>

<!-- 4 Summary Statistics Blocks -->
<div class="summary-grid">
    <div class="sum-box blue">
        <div class="sum-val"><?= number_format($total_trx) ?> <span style="font-size:12px; font-weight:normal;">voucher</span></div>
        <div class="sum-label"><i class="fa fa-ticket"></i> Total Vouchers</div>
    </div>
    
    <div class="sum-box green">
        <div class="sum-val"><?= number_format($total_trx) ?> <span style="font-size:12px; font-weight:normal;">trans</span></div>
        <div class="sum-label"><i class="fa fa-history"></i> Total Transactions</div>
    </div>
    
    <div class="sum-box yellow">
        <div class="sum-val"><?= number_format($today_trx) ?> <span style="font-size:12px; font-weight:normal;">voucher</span></div>
        <div class="sum-label"><i class="fa fa-calendar"></i> Vouchers Today</div>
    </div>
    
    <div class="sum-box red">
        <div class="sum-val">Rp <?= number_format($total_spent, 0, ',', '.') ?></div>
        <div class="sum-label"><i class="fa fa-money"></i> Total Spent</div>
    </div>
</div>

<div class="section-title">
    <i class="fa fa-flash"></i> Generate Voucher
</div>

<!-- Virtual Voucher Packages as visually requested -->
<div class="voucher-grid">
    <?php if (count($profiles) > 0): ?>
        <?php foreach ($profiles as $p): 
            $profit = $p['sell_price'] - $p['buy_price'];
        ?>
        <div class="voucher-wrapper">
            <div class="voucher-item" onclick="openGenModal('<?= htmlspecialchars($p['profile']) ?>', <?= $p['sell_price'] ?>, <?= $p['buy_price'] ?>)">
                <div class="v-profile"><?= htmlspecialchars($p['profile']) ?></div>
                
                <div class="v-sell-wrap">
                    <span class="v-sell">Rp <?= number_format($p['sell_price'], 0, ',', '.') ?></span>
                </div>
                
                <div class="v-meta">
                    <div>Beli: Rp <?= number_format($p['buy_price'], 0, ',', '.') ?></div>
                    <div style="color: #69f0ae;">Profit: Rp <?= number_format($profit, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12" style="width: 100%;">
            <div style="background:#334; padding:20px; border-radius:8px; color:#aaa; text-align:center;">
                <i class="fa fa-info-circle"></i> Admin belum mengatur harga paket untuk Anda.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Generate Voucher -->
<div class="modal-overlay" id="genModal" onclick="closeGenModal(event)">
    <div class="modal-gen" onclick="event.stopPropagation()">
        <div class="modal-gen-header">
            <i class="fa fa-ticket"></i> Generate Voucher
        </div>
        <div class="modal-gen-body">
            <div class="mg-paket-banner" id="mgPaketName">Paket Terpilih: -</div>
            
            <form id="genVoucherForm">
                <input type="hidden" name="profile" id="mgInputProfile" value="">
                
                <div class="mg-group">
                    <label class="mg-label">Jumlah Voucher</label>
                    <input type="number" name="qty" class="mg-input" id="mgInputQty" value="1" min="1" max="100" title="Jumlah voucher: 1-100">
                </div>
                
                <div class="mg-group">
                    <label class="mg-label">Nomor HP Customer (Opsional)</label>
                    <input type="text" name="customer_phone" class="mg-input" placeholder="08123456789" autocomplete="off">
                </div>
                
                <div class="mg-group">
                    <label class="mg-label">Nama Customer (Opsional)</label>
                    <input type="text" name="customer_name" class="mg-input" placeholder="Nama customer" autocomplete="off">
                </div>
            </form>
            
            <div id="mgAlert" style="margin-top:15px; display:none; padding:10px; border-radius:4px; font-size:13px;"></div>
        </div>
        <div class="modal-gen-footer">
            <button type="button" class="btn-gen" id="btnProsesGen" onclick="submitGen()">
                <i class="fa fa-plus-circle"></i> GENERATE VOUCHER
            </button>
        </div>
    </div>
</div>

<script>
var agentBalance = <?= intval($agent['balance']) ?>;
var selectedBuyPrice = 0;

function openGenModal(profile, sellPrice, buyPrice) {
    selectedBuyPrice = buyPrice;
    $('#mgPaketName').text('Paket Terpilih: ' + profile);
    $('#mgInputProfile').val(profile);
    $('#mgAlert').hide().removeClass('bg-danger bg-success text-white');
    $('#btnProsesGen').prop('disabled', false).html('<i class="fa fa-plus-circle"></i> GENERATE VOUCHER');
    
    // reset form manually
    $('input[name="customer_phone"]').val('');
    $('input[name="customer_name"]').val('');
    
    $('#genModal').css('display', 'flex');
}

function closeGenModal(e) {
    // only close if clicked on overlay exactly
    if(e && e.target.id === 'genModal') {
        $('#genModal').hide();
    }
}

function submitGen() {
    var qty = parseInt($('#mgInputQty').val()) || 1;
    var totalCost = selectedBuyPrice * qty;

    if (totalCost > agentBalance) {
        $('#mgAlert').show().addClass('bg-danger text-white').html('<i class="fa fa-ban"></i> Saldo anda tidak cukup! Total Modal: Rp ' + totalCost.toLocaleString('id-ID'));
        return;
    }
    
    if (!confirm('Anda akan mencetak ' + qty + ' voucher. Total pemotongan saldo: Rp ' + totalCost.toLocaleString('id-ID') + '. Lanjutkan?')) return;
    
    $('#btnProsesGen').prop('disabled', true).html('<i class="fa fa-circle-o-notch fa-spin"></i> MEMPROSES...');
    $('#mgAlert').hide();
    
    $.ajax({
        type: "POST",
        url: "../process/generate_agent.php",
        data: $('#genVoucherForm').serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#mgAlert').show().removeClass('bg-danger').addClass('bg-success text-white').html('<i class="fa fa-check"></i> ' + res.message + " Mengalihkan...");
                // Automatically redirect to History Transaction as requested
                setTimeout(function() {
                    window.location.href = '?page=history';
                }, 1000);
            } else {
                $('#mgAlert').show().removeClass('bg-success').addClass('bg-danger text-white').html('<i class="fa fa-ban"></i> ' + res.message);
                $('#btnProsesGen').prop('disabled', false).html('<i class="fa fa-plus-circle"></i> GENERATE VOUCHER');
            }
        },
        error: function() {
            $('#mgAlert').show().removeClass('bg-success').addClass('bg-danger text-white').html('<i class="fa fa-ban"></i> Terjadi kesalahan komunikasi ke server (Mikrotik).');
            $('#btnProsesGen').prop('disabled', false).html('<i class="fa fa-plus-circle"></i> GENERATE Ulang');
        }
    });
}
</script>
