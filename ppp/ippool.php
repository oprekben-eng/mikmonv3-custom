<?php
/**
 * IP Pool List
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$totals = $API->comm("/ip/pool/print", array("count-only" => ""));
$getpool = $API->comm("/ip/pool/print");

?>

<div class="row">
    <div class="col-12">
        <div class="card box-bordered">
            <div class="card-header align-middle">
                <h3><i class="fa fa-database"></i> IP Pool <small>| Total: <?= $totals ?></small></h3>
            </div>
            <div class="card-body">
                <div class="pd-10">
                    <a class="btn bg-primary" href="./?ppp=addpool&session=<?= $session; ?>"><i class="fa fa-plus"></i> Add Pool</a>
                </div>
                <div class="table-responsive" style="white-space: nowrap; overflow-x: auto;">
                    <table class="table table-bordered table-hover" id="dataTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Ranges</th>
                                <th>Next Pool</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($getpool as $p): ?>
                                <tr>
                                    <td class="align-middle"><b><?= htmlspecialchars($p['name']) ?></b></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['ranges'] ?? '') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($p['next-pool'] ?? 'none') ?></td>
                                    <td class="align-middle text-center">
                                        <a href="javascript:void(0);" onclick="if(confirm('Delete pool <?= htmlspecialchars($p['name']) ?>?')) window.location='./?remove-ippool=<?= urlencode($p['.id']) ?>&session=<?= $session ?>';" title="Delete" class="btn bg-danger btn-sm"><i class="fa fa-trash"></i></a>
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
