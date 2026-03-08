<?php
/**
 * PPP Active Connections
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$totals = $API->comm("/ppp/active/print", array("count-only" => ""));
$getactive = $API->comm("/ppp/active/print");

?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-wifi"></i> PPP Active Connections <small>| Total: <?= $totals ?></small></h3>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Service</th>
                                <th>Caller ID</th>
                                <th>Address</th>
                                <th>Uptime</th>
                                <th>Encoding</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($getactive as $a): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($a['name']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($a['service'] ?? 'unknown') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($a['caller-id'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($a['address'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($a['uptime'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($a['encoding'] ?? '') ?></td>
                                    <td class="align-middle text-center">
                                        <a href="javascript:void(0);" onclick="if(confirm('Disconnect user <?= htmlspecialchars($a['name']) ?>?')) window.location='./?remove-pactive=<?= urlencode($a['.id']) ?>&session=<?= $session ?>';" title="Disconnect" class="btn bg-danger btn-sm"><i class="fa fa-cut"></i></a>
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
