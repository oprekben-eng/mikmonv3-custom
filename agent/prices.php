<?php
/**
 * Prices Settings Page (Admin) Redesigned
 * Grouped by Agent with a single Modal to set prices
 */
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
    exit;
}

require_once('./database/database.php');

$db = Database::getConnection();

// Get list of agents
$stmt = $db->query("SELECT id, username, name FROM agents WHERE is_active = 1 ORDER BY name ASC");
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all configured prices
$stmt = $db->query("
    SELECT ap.*, a.name, a.username 
    FROM agent_prices ap
    JOIN agents a ON ap.agent_id = a.id
    ORDER BY a.name ASC, ap.sell_price ASC
");
$all_prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group prices by agent
$grouped_prices = [];
foreach ($all_prices as $p) {
    if (!isset($grouped_prices[$p['agent_id']])) {
        $grouped_prices[$p['agent_id']] = [
            'name' => $p['name'],
            'username' => $p['username'],
            'prices' => []
        ];
    }
    $grouped_prices[$p['agent_id']]['prices'][] = $p;
}

// Get Mikrotik profiles for the dropdown
$profiles = [];
if (isset($API)) {
    $profiles = $API->comm("/ip/hotspot/user/profile/print");
}

?>
<style>
/* Custom Layout for Prices Redesign */
/* Removed hardcoded light-theme colors to inherit native dark UI from body */

/* Modal Overlay */
#priceModal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 600px;
    max-width: 90%;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    position: relative;
    animation: slideDown 0.3s ease;
}
@keyframes slideDown {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    color: #aaa;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}
.modal-close:hover {
    color: #333;
}
.modal-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
</style>

<!-- Top Control -->
<div class="row">
    <div class="col-12">
        <div class="card box-bordered mb-20">
            <div class="card-header">
                <h3><i class="fa fa-tags"></i> Kelola Harga Agent</h3>
            </div>
            <div class="card-body">
                <button class="btn bg-primary text-bold" onclick="openModal()">
                    <i class="fa fa-plus"></i> Tambah Harga
                </button>
            </div>
        </div>
    </div>
</div>

<div id="priceMsg"></div>

<!-- Agent Groups -->
<div class="row">
    <div class="col-12">
        
        <?php if (count($grouped_prices) == 0): ?>
            <div class="card box-bordered pd-20 text-center text-muted">Belum ada harga agen yang diatur. Silakan klik Tambah Harga.</div>
        <?php endif; ?>

        <?php foreach ($grouped_prices as $ag_id => $group): ?>
        <div class="card box-bordered mb-20">
            <div class="card-header text-bold">
                <i class="fa fa-user"></i> <?= htmlspecialchars($group['name']) ?> (<?= htmlspecialchars($group['username']) ?>)
            </div>
            <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                <table class="table table-bordered table-striped" style="margin:0; border:none;">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Profit</th>
                            <th>Data Limit</th>
                            <th>Update</th>
                            <th style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['prices'] as $p): 
                            $profit = $p['sell_price'] - $p['buy_price'];
                            $limit_text = $p['data_limit'] > 0 ? number_format($p['data_limit'], 0, ',', '.') . ' MB' : 'Unlimited';
                            $date_text = date('d M Y', strtotime($p['created_at']));
                        ?>
                        <tr>
                            <td class="align-middle text-bold"><?= htmlspecialchars($p['profile']) ?></td>
                            <td class="align-middle">Rp <?= number_format($p['buy_price'], 0, ',', '.') ?></td>
                            <td class="align-middle">Rp <?= number_format($p['sell_price'], 0, ',', '.') ?></td>
                            <td class="align-middle text-success text-bold">Rp <?= number_format($profit, 0, ',', '.') ?></td>
                            <td class="align-middle"><?= htmlspecialchars($limit_text) ?></td>
                            <td class="align-middle"><?= $date_text ?></td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm bg-warning text-white" title="Edit" 
                                    onclick="editPrice(<?= $ag_id ?>, '<?= htmlspecialchars($p['profile']) ?>', <?= $p['buy_price'] ?>, <?= $p['sell_price'] ?>, <?= $p['data_limit'] ?>)">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm bg-danger text-white" title="Hapus" 
                                    onclick="deletePrice(<?= $p['id'] ?>)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<!-- Modal Form -->
<div id="priceModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-title"><i class="fa fa-plus-circle"></i> Set Harga Baru</div>
        
        <form id="priceForm" onsubmit="return savePrice(event);">
            <div class="row">
                <div class="col-6">
                    <label>Pilih Agent</label>
                    <select name="agent_id" id="modal_agent_id" class="form-control" required style="width:100%; border:1px solid #9bbcf2; background:#f0f8ff;">
                        <option value="">-- Pilih Agent --</option>
                        <?php foreach ($agents as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label>Profile</label>
                    <select name="profile" id="modal_profile" class="form-control" required>
                        <option value="">-- Pilih Profile --</option>
                        <?php foreach ($profiles as $p): 
                            if ($p['name'] == 'default') continue;
                        ?>
                            <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mt-10">
                <div class="col-6">
                    <label>Harga Beli</label>
                    <input type="number" name="buy_price" id="modal_buy_price" class="form-control" required min="0">
                </div>
                <div class="col-6">
                    <label>Harga Jual</label>
                    <input type="number" name="sell_price" id="modal_sell_price" class="form-control" required min="0">
                </div>
            </div>

            <div class="row mt-10">
                <div class="col-12">
                    <label>Data Limit (MB)</label>
                    <input type="number" name="data_limit" id="modal_data_limit" class="form-control" value="0" min="0">
                    <small style="color:#666; font-size:11px;">Isi dengan kuota dalam hitungan MB (Misal 1000 untuk 1GB). 0 artinya Unlimited/Sesuai Profil Router.</small>
                </div>
            </div>

            <div class="row mt-20" style="text-align:right;">
                <div class="col-12">
                    <button type="button" class="btn btn-sm" style="background:#f4f4f4; border:1px solid #ddd; padding:8px 15px;" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-sm" style="background:#f4f4f4; border:1px solid #ddd; padding:8px 15px; font-weight:bold;">
                        <i class="fa fa-save"></i> Simpan Harga
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    $('#priceForm')[0].reset();
    $('#modal_agent_id').prop('disabled', false);
    $('#modal_profile').prop('disabled', false);
    
    // Select styling overrides
    $('#modal_agent_id').css({'border': '1px solid #9bbcf2', 'background': '#f0f8ff'});
    
    $('#priceModal').fadeIn(200);
}

function closeModal() {
    $('#priceModal').fadeOut(200);
}

function editPrice(agent_id, profile, buy, sell, limit) {
    $('#modal_agent_id').val(agent_id).prop('disabled', true); // lock on edit
    $('#modal_profile').val(profile).prop('disabled', true);   // lock on edit
    
    // remove highlight for edit mode
    $('#modal_agent_id').css({'border': '1px solid #ccc', 'background': '#eee'});
    
    $('#modal_buy_price').val(buy);
    $('#modal_sell_price').val(sell);
    $('#modal_data_limit').val(limit);
    
    // Append hidden fields to submit disabled values
    $('#priceForm input[name="agent_id_hidden"]').remove();
    $('#priceForm input[name="profile_hidden"]').remove();
    $('#priceForm').append('<input type="hidden" name="agent_id_hidden" value="'+agent_id+'">');
    $('#priceForm').append('<input type="hidden" name="profile_hidden" value="'+profile+'">');

    $('#priceModal').fadeIn(200);
}

function savePrice(e) {
    e.preventDefault();
    
    // Get data (handle disabled select fields by taking hidden inputs if present)
    var agent_id = $('#modal_agent_id').is(':disabled') ? $('input[name="agent_id_hidden"]').val() : $('#modal_agent_id').val();
    var profile = $('#modal_profile').is(':disabled') ? $('input[name="profile_hidden"]').val() : $('#modal_profile').val();
    var buy = $('#modal_buy_price').val();
    var sell = $('#modal_sell_price').val();
    var limit = $('#modal_data_limit').val();
    
    if(!agent_id || !profile) {
        alert("Pilih Agent dan Profile!");
        return false;
    }
    
    var data = {
        agent_id: agent_id,
        profile: profile,
        buy_price: buy,
        sell_price: sell,
        data_limit: limit
    };
    
    $.post('./process/pprices.php?action=save&session=<?=$_GET['session'] ?? ''?>', data, function(res){
        $('#priceMsg').html(res);
        closeModal();
        setTimeout(() => location.reload(), 1500);
    });
    return false;
}

function deletePrice(id) {
    if(confirm('Hapus pengaturan harga ini? Profil tidak akan bisa dibeli agen.')) {
        $.post('./process/pprices.php?action=delete&session=<?=$_GET['session'] ?? ''?>', {id: id}, function(res){
            $('#priceMsg').html(res);
            setTimeout(() => location.reload(), 1500);
        });
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == document.getElementById('priceModal')) {
        closeModal();
    }
}
</script>
