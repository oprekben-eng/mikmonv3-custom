<?php
/**
 * PPP Secrets List (PPPoE Users)
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$totals = $API->comm("/ppp/secret/print", array("count-only" => ""));
$getsecr = $API->comm("/ppp/secret/print");

?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-users"></i> PPP Secrets <small>| Total: <?= $totals ?></small></h3>
            </div>
            <div class="card-body">
                <div class="pd-10">
                    <a class="btn bg-primary" href="./?ppp=addsecret&session=<?= $session ?>"><i class="fa fa-plus"></i> Add Secret</a>
                </div>
                <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Password</th>
                                <th>Service</th>
                                <th>Profile</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($getsecr as $s): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($s['name']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['password'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['service'] ?? 'any') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($s['profile'] ?? 'default') ?></td>
                                    <td class="align-middle text-center">
                                        <a href="./?secret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Edit" class="btn bg-info btn-sm"><i class="fa fa-edit"></i></a>
                                        <?php if ($s['disabled'] == "true"): ?>
                                            <a href="./?enable-pppsecret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Enable" class="btn bg-success btn-sm"><i class="fa fa-check"></i></a>
                                        <?php else: ?>
                                            <a href="./?disable-pppsecret=<?= urlencode($s['name']) ?>&session=<?= $session ?>" title="Disable" class="btn bg-warning btn-sm"><i class="fa fa-lock"></i></a>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" onclick="if(confirm('Are you sure you want to delete secret <?= htmlspecialchars($s['name']) ?>?')) window.location='./?remove-pppsecret=<?= urlencode($s['name']) ?>&session=<?= $session ?>';" title="Delete" class="btn bg-danger btn-sm"><i class="fa fa-trash"></i></a>
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
