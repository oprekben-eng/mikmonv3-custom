<?php
/**
 * Admin Transactions Page Redesign
 * View voucher transactions per agent with ledger-like styling
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

$db = Database::getConnection();

// Get list of agents for dropdown
$stmt = $db->query("SELECT id, username, name FROM agents WHERE is_active = 1 ORDER BY name ASC");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get Selected Agent
$selected_agent = intval($_GET['agent_id'] ?? 0);

// Fetch transactions
$current_balance = 0;
if ($selected_agent > 0) {
    $stmt = $db->prepare("SELECT balance FROM agents WHERE id = ?");
    $stmt->execute([$selected_agent]);
    $current_balance = intval($stmt->fetchColumn() ?: 0);

    $stmt = $db->prepare("
        SELECT vt.*, a.name as agent_name, a.username as agent_username 
        FROM voucher_transactions vt
        JOIN agents a ON vt.agent_id = a.id
        WHERE vt.agent_id = ?
        ORDER BY vt.id DESC
    ");
    $stmt->execute([$selected_agent]);
} else {
    // Or just all if no agent selected
    $stmt = $db->query("
        SELECT vt.*, a.name as agent_name, a.username as agent_username 
        FROM voucher_transactions vt
        JOIN agents a ON vt.agent_id = a.id
        ORDER BY vt.id DESC
        LIMIT 500
    ");
}

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
/* Clean Ledger Styling matching screenshot */
.ledger-select-group {
    padding: 15px;
    border-bottom: 1px solid #ddd;
}
.ledger-select-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
}
select.ledger-select {
    width: 100%;
    padding: 8px;
    border: 1px solid #9bbcf2;
    border-radius: 3px;
    box-sizing: border-box;
    max-width: 100%;
}
.table-ledger {
    margin: 0;
}
.table-ledger > thead > tr > th {
    font-weight: bold;
}
.table-ledger > tbody > tr > td {
    vertical-align: middle;
    padding: 10px 15px;
    font-size: 13px;
}
.badge-action {
    background-color: #ffeaea;
    color: #d9534f;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    display: inline-block;
}
.text-amount-out {
    color: #d9534f;
    font-weight: bold;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-history" style="margin-right:8px;"></i> Transaksi Agent</h3>
            </div>
        
            <div class="ledger-select-group">
                <label>Pilih Agent</label>
                <form id="filterForm" method="GET" action="">
                    <!-- Preserve existing query parameters but overwrite agent_id -->
                    <input type="hidden" name="agent" value="transactions">
                    <input type="hidden" name="session" value="<?= htmlspecialchars($_GET['session']) ?>">
                    
                    <select name="agent_id" class="ledger-select form-control" onchange="document.getElementById('filterForm').submit()">
                        <option value="0">-- Semua Agent --</option>
                        <?php foreach ($agents as $a): 
                            $safe_name = trim($a['name']);
                            if (strlen($safe_name) > 20) $safe_name = substr($safe_name, 0, 17) . '...';
                            $safe_uname = trim($a['username']);
                            if (strlen($safe_uname) > 15) $safe_uname = substr($safe_uname, 0, 12) . '...';
                        ?>
                            <option value="<?= $a['id'] ?>" <?= $selected_agent == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($safe_name) ?> (<?= htmlspecialchars($safe_uname) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
            <table class="table table-ledger table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>SN</th>
                        <th>Saldo Sebelum</th>
                        <th>Saldo Sesudah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Calculate relative running balance strictly if one agent is selected
                    $running_balance = $current_balance;
                    foreach ($transactions as $t): 
                        $date_str = date('d M Y H:i', strtotime($t['created_at']));
                        $username = $t['username'];
                        $profile = $t['profile'];
                        $note = "Generate voucher: $username";
                        if ($selected_agent == 0) {
                            $note .= " by " . $t['agent_username'];
                        }

                        $str_sebelum = '-';
                        $str_sesudah = '-';
                        if ($selected_agent > 0) {
                            $saldo_sesudah = $running_balance;
                            $saldo_sebelum = $running_balance + $t['price'];
                            $running_balance = $saldo_sebelum; // Move backwards
                            
                            $str_sebelum = 'Rp ' . number_format($saldo_sebelum, 0, ',', '.');
                            $str_sesudah = 'Rp ' . number_format($saldo_sesudah, 0, ',', '.');
                        }
                    ?>
                    <tr>
                        <td><?= $date_str ?></td>
                        <td><span class="badge-action">Generate</span></td>
                        <td class="text-amount-out">-Rp <?= number_format($t['price'], 0, ',', '.') ?></td>
                        <td>-</td>
                        <td>-</td>
                        <td><?= $str_sebelum ?></td>
                        <td><?= $str_sesudah ?></td>
                        <td><?= htmlspecialchars($note) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (count($transactions) == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted pd-20">
                            Belum ada riwayat transaksi agen.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    </div>
</div>
