<?php
/**
 * Add PPP Secret
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $service = $_POST['service'];
    $profile = $_POST['profile'];
    $local = $_POST['local_address'];
    $remote = $_POST['remote_address'];
    $comment = $_POST['comment'];

    $params = array(
        "name" => $name,
        "password" => $password,
        "service" => $service,
        "profile" => $profile,
    );

    if (!empty($local)) $params["local-address"] = $local;
    if (!empty($remote)) $params["remote-address"] = $remote;
    if (!empty($comment)) $params["comment"] = $comment;

    $API->comm("/ppp/secret/add", $params);
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}

// Get profiles (Optimized API Payload)
$profiles = $API->comm("/ppp/profile/print", array(
    ".proplist" => "name"
));
?>

<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-user-plus"></i> Add PPP Secret</h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <table class="table">
                        <tr>
                            <td class="align-middle">Name <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="name" required></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Password <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="password" required></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Service</td>
                            <td>
                                <select class="form-control" name="service">
                                    <option value="any">any</option>
                                    <option value="async">async</option>
                                    <option value="l2tp">l2tp</option>
                                    <option value="ovpn">ovpn</option>
                                    <option value="pppoe" selected>pppoe</option>
                                    <option value="pptp">pptp</option>
                                    <option value="sstp">sstp</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Profile</td>
                            <td>
                                <select class="form-control" name="profile">
                                    <option value="default">default</option>
                                    <?php foreach ($profiles as $p): ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Local Address</td>
                            <td><input class="form-control" type="text" name="local_address" placeholder="e.g. 192.168.1.1"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Remote Address</td>
                            <td><input class="form-control" type="text" name="remote_address" placeholder="IP Address or Pool Name"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Comment</td>
                            <td><input class="form-control" type="text" name="comment"></td>
                        </tr>
                    </table>
                    <div style="padding: 10px;">
                        <button type="submit" class="btn bg-primary"><i class="fa fa-save"></i> Save</button>
                        <a href="./?ppp=secrets&session=<?= $session ?>" class="btn bg-warning"><i class="fa fa-close"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
