<?php
/**
 * Add IP Pool
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $ranges = $_POST['ranges'];
    $nextpool = $_POST['nextpool'];

    $params = array(
        "name" => $name,
        "ranges" => $ranges
    );

    if (!empty($nextpool) && $nextpool != "none") {
        $params["next-pool"] = $nextpool;
    }

    $API->comm("/ip/pool/add", $params);
    echo "<script>window.location='./?ppp=ippool&session=" . $session . "'</script>";
    exit;
}

$pools = $API->comm("/ip/pool/print");
?>

<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-plus"></i> Add IP Pool</h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <table class="table">
                        <tr>
                            <td class="align-middle">Name <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="name" required placeholder="pool1"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Addresses / Ranges <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="ranges" required placeholder="192.168.1.10-192.168.1.254"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Next Pool</td>
                            <td>
                                <select class="form-control" name="nextpool">
                                    <option value="none">none</option>
                                    <?php foreach ($pools as $p): ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div style="padding: 10px;">
                        <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Save</button>
                        <a href="./?ppp=ippool&session=<?= $session ?>" class="btn bg-warning"><i class="fa fa-close"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
