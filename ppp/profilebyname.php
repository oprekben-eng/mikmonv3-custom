<?php
/**
 * Edit PPP Profile
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$profile_name = $_GET['profile'];

if (isset($_POST['name'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $local = $_POST['local_address'];
    $remote = $_POST['remote_address'];
    $rate = $_POST['rate_limit'];
    $onlyone = $_POST['only_one'];
    $comment = $_POST['comment'];

    $params = array(
        ".id" => $id,
        "name" => $name,
    );

    if (!empty($local)) {
        $params["local-address"] = $local;
    }
    if (!empty($remote)) {
        $params["remote-address"] = $remote;
    }
    if (!empty($rate)) {
        $params["rate-limit"] = $rate;
    }
    $params["only-one"] = !empty($onlyone) ? $onlyone : "default";
    $params["comment"] = $comment;

    $API->comm("/ppp/profile/set", $params);
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
    exit;
}

$getprof = $API->comm("/ppp/profile/print", array("?name" => $profile_name));
if (count($getprof) === 0) {
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
    exit;
}
$prof = $getprof[0];

$pools = $API->comm("/ip/pool/print");
?>

<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> Edit PPP Profile</h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <input type="hidden" name="id" value="<?= $prof['.id'] ?>">
                    <table class="table">
                        <tr>
                            <td class="align-middle">Name <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="name" required value="<?= htmlspecialchars($prof['name']) ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Local Address</td>
                            <td><input class="form-control" type="text" name="local_address" value="<?= htmlspecialchars($prof['local-address'] ?? '') ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Remote Address</td>
                            <td>
                                <select class="form-control" name="remote_address">
                                    <option value="">Select Pool...</option>
                                    <?php 
                                    $has_match = false;
                                    foreach ($pools as $p): 
                                        $selected = ($prof['remote-address'] ?? '') == $p['name'] ? 'selected' : '';
                                        if ($selected) $has_match = true;
                                    ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>" <?= $selected ?>><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                    
                                    <?php if (!empty($prof['remote-address']) && !$has_match): ?>
                                        <option value="<?= htmlspecialchars($prof['remote-address']) ?>" selected><?= htmlspecialchars($prof['remote-address']) ?></option>
                                    <?php endif; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Rate Limit (rx/tx)</td>
                            <td><input class="form-control" type="text" name="rate_limit" value="<?= htmlspecialchars($prof['rate-limit'] ?? '') ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Only One</td>
                            <td>
                                <select class="form-control" name="only_one">
                                    <?php 
                                    $opts = ['default', 'yes', 'no'];
                                    $curr = $prof['only-one'] ?? 'default';
                                    foreach ($opts as $o): 
                                    ?>
                                        <option value="<?= $o ?>" <?= $curr == $o ? 'selected' : '' ?>><?= $o ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Comment</td>
                            <td><input class="form-control" type="text" name="comment" value="<?= htmlspecialchars($prof['comment'] ?? '') ?>"></td>
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
