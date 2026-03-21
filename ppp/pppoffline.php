<?php
/**
 * PPP Offline Users
 * Shows PPP Secrets that are NOT currently active (offline/disconnected)
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

// Get all secrets
$getsecr = $API->comm("/ppp/secret/print");

// Get all active connections
$getactive = $API->comm("/ppp/active/print");

// Build array of active usernames for quick lookup
$activeUsers = [];
foreach ($getactive as $a) {
    if (isset($a['name'])) {
        $activeUsers[$a['name']] = true;
    }
}

// Filter: only secrets NOT in active list
$offlineUsers = [];
foreach ($getsecr as $s) {
    if (!isset($activeUsers[$s['name']])) {
        $offlineUsers[] = $s;
    }
}
$totalOffline = count($offlineUsers);

?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-users"></i> PPP Offline <small>| Total: <?= $totalOffline ?></small></h3>
            </div>
            <div class="card-body">
                <div class="pd-10">
                    <a class="btn bg-primary" href="./?ppp=secrets&session=<?= $session ?>"><i class="fa fa-list"></i> All Secrets</a>
                    <a class="btn bg-info" href="./?ppp=active&session=<?= $session ?>"><i class="fa fa-wifi"></i> Active</a>
                </div>
                <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Password</th>
                                <th>Service</th>
                                <th>Profile</th>
                                <th>Last Logged Out</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offlineUsers as $s): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($s['name']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['password'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['service'] ?? 'any') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['profile'] ?? 'default') ?></td>
                                    <td class="align-middle">
                                        <?php 
                                            // Menampilkan log terputus terakhir
                                            if (!empty($s['last-logged-out'])) {
                                                echo "<i class='fa fa-clock-o text-muted'></i> " . htmlspecialchars($s['last-logged-out']);
                                            } else {
                                                echo "<span class='text-muted'>-</span>";
                                            }
                                        ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if (($s['disabled'] ?? 'false') == "true"): ?>
                                            <span class="btn btn-sm bg-warning"><i class="fa fa-ban"></i> Disabled</span>
                                        <?php else: ?>
                                            <span class="btn btn-sm bg-secondary"><i class="fa fa-power-off"></i> Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <a href="./?secret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Edit" class="btn bg-info btn-sm"><i class="fa fa-edit"></i></a>
                                        <?php if (($s['disabled'] ?? 'false') == "true"): ?>
                                            <a href="./?enable-pppsecret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Enable" class="btn bg-success btn-sm"><i class="fa fa-check"></i></a>
                                        <?php else: ?>
                                            <a href="./?disable-pppsecret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Disable" class="btn bg-warning btn-sm"><i class="fa fa-lock"></i></a>
                                        <?php endif; ?>
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
