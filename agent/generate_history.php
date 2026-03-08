<?php
/**
 * Agent Portal Generated Voucher Detail Content
 */
if (!isset($_SESSION['agent'])) exit;

$agent = $_SESSION['agent'];
$id = intval($_GET['id'] ?? 0);

if (!$id) { header("Location: ?page=history"); exit; }

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM voucher_transactions WHERE id = ? AND agent_id = ?");
$stmt->execute([$id, $agent['id']]);
$trx = $stmt->fetch();

if (!$trx) { header("Location: ?page=history"); exit; }
?>
<style>
    .voucher-box { border: 2px dashed #3498db; padding: 20px; border-radius: 10px; background: #fff; max-width: 400px; margin: 0 auto; text-align: center; }
    .v-title { font-size: 20px; font-weight: bold; color: #34495e; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
    .v-data { font-size: 28px; font-family: monospace; font-weight: bold; color: #e74c3c; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
    .v-label { font-size: 14px; color: #7f8c8d; text-transform: uppercase; }
    .v-footer { margin-top: 15px; font-size: 12px; color: #95a5a6; }
</style>

<div class="row">
    <div class="col-12 text-center mb-20">
        <h3><i class="fa fa-check-circle text-success" style="font-size: 40px;"></i><br>Transaksi Berhasil!</h3>
        <p>Saldo terpotong: <b>Rp <?= number_format($trx['price'], 0, ',', '.') ?></b></p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="voucher-box">
            <div class="v-title">Voucher Internet</div>
            
            <div class="v-label">Paket / Profil</div>
            <div class="text-bold mb-10" style="font-size:18px; color: #2ecc71;"><?= htmlspecialchars($trx['profile']) ?></div>
            
            <div class="v-label">Username</div>
            <div class="v-data"><?= htmlspecialchars($trx['username']) ?></div>
            
            <?php if ($trx['username'] !== $trx['password']): ?>
            <div class="v-label">Password</div>
            <div class="v-data"><?= htmlspecialchars($trx['password']) ?></div>
            <?php endif; ?>
            
            <div class="v-footer">
                Generated: <?= date('d M Y H:i', strtotime($trx['created_at'])) ?><br>
                Agen: <?= htmlspecialchars($agent['name']) ?>
            </div>
        </div>
        
        <div class="text-center mt-20">
            <button class="btn bg-primary" onclick="window.print()"><i class="fa fa-print"></i> Cetak Tanda Terima</button>
            <a href="?page=generate" class="btn bg-success"><i class="fa fa-plus"></i> Generate Lagi</a>
        </div>
        
        <?php if (!empty($trx['customer_phone'])): ?>
        <div class="text-center mt-10 text-muted">
            <?php if ($trx['wa_sent']): ?>
                <i class="fa fa-whatsapp text-success"></i> Pesan WA telah dikirimkan ke pelanggan.
            <?php else: ?>
                <i class="fa fa-whatsapp text-warning"></i> Pesan WA gagal dikirim otomatis. Cek riwayat untuk resend.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
