<?php
/**
 * Add PPP Profile
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $local = $_POST['local_address'];
    $remote = $_POST['remote_address'];
    $rate = $_POST['rate_limit'];
    $onlyone = $_POST['only_one'];
    $comment = $_POST['comment'];

    $params = array(
        "name" => $name,
    );

    if (!empty($local)) $params["local-address"] = $local;
    if (!empty($remote)) $params["remote-address"] = $remote;
    if (!empty($rate)) $params["rate-limit"] = $rate;
    if (!empty($onlyone)) $params["only-one"] = $onlyone;
    if (!empty($comment)) $params["comment"] = $comment;

    $API->comm("/ppp/profile/add", $params);
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
    exit;
}

$pools = $API->comm("/ip/pool/print", array(
    ".proplist" => "name"
));
?>

<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-plus"></i> Add PPP Profile</h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <table class="table">
                        <tr>
                            <td class="align-middle">Name <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="name" required></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Local Address</td>
                            <td><input class="form-control" type="text" name="local_address" placeholder="IP Address"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Remote Address</td>
                            <td>
                                <select class="form-control" name="remote_address">
                                    <option value="">Select Pool...</option>
                                    <?php foreach ($pools as $p): ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Rate Limit (rx/tx)</td>
                            <td><input class="form-control" type="text" name="rate_limit" placeholder="e.g. 512k/1M"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Only One</td>
                            <td>
                                <select class="form-control" name="only_one">
                                    <option value="default">default</option>
                                    <option value="yes">yes</option>
                                    <option value="no">no</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Comment</td>
                            <td><input class="form-control" type="text" name="comment"></td>
                        </tr>
                    </table>
                    <div style="padding: 10px;">
                        <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Save</button>
                        <a href="./?ppp=profiles&session=<?= $session ?>" class="btn bg-warning"><i class="fa fa-close"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
