<?php
/**
 * PPP Profiles List
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$totals = $API->comm("/ppp/profile/print", array("count-only" => ""));
$getprof = $API->comm("/ppp/profile/print");

?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-pie-chart"></i> PPP Profiles <small>| Total: <?= $totals ?></small></h3>
            </div>
            <div class="card-body">
                <div class="pd-10">
                    <a class="btn bg-primary" href="./?ppp=add-profile&session=<?= $session ?>"><i class="fa fa-plus"></i> Add Profile</a>
                </div>
                <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Local Address</th>
                                <th>Remote Address</th>
                                <th>Rate Limit (rx/tx)</th>
                                <th>Only One</th>
                                <th>Comment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($getprof as $p): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($p['name']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['local-address'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['remote-address'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['rate-limit'] ?? 'Unlimited') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['only-one'] ?? 'default') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['comment'] ?? '') ?></td>
                                    <td class="align-middle text-center">
                                        <?php if ($p['default'] !== "true"): ?>
                                            <a href="./?ppp=edit-profile&profile=<?= urlencode($p['name']) ?>&session=<?= $session ?>" title="Edit" class="btn bg-info btn-sm"><i class="fa fa-edit"></i></a>
                                            <a href="javascript:void(0);" onclick="if(confirm('Delete profile <?= htmlspecialchars($p['name']) ?>?')) window.location='./?remove-pprofile=<?= urlencode($p['name']) ?>&session=<?= $session ?>';" title="Delete" class="btn bg-danger btn-sm"><i class="fa fa-trash"></i></a>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fa fa-lock"></i> Default</span>
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
