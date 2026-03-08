<?php
/**
 * Edit PPP Secret
 */
if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

$secret_name = $_GET['secret'];

if (isset($_POST['name'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $password = $_POST['password'];
    $service = $_POST['service'];
    $profile = $_POST['profile'];
    $local = $_POST['local_address'];
    $remote = $_POST['remote_address'];
    $comment = $_POST['comment'];

    $params = array(
        ".id" => $id,
        "name" => $name,
        "password" => $password,
        "service" => $service,
        "profile" => $profile,
    );

    if (!empty($local)) {
        $params["local-address"] = $local;
    }
    if (!empty($remote)) {
        $params["remote-address"] = $remote;
    }
    $params["comment"] = $comment;

    $API->comm("/ppp/secret/set", $params);
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
    exit;
}

$getsecr = $API->comm("/ppp/secret/print", array("?name" => $secret_name));
if (count($getsecr) === 0) {
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
    exit;
}
$secr = $getsecr[0];

$profiles = $API->comm("/ppp/profile/print");
?>

<div class="row">
    <div class="col-8">
        <div class="card box-bordered">
            <div class="card-header">
                <h3><i class="fa fa-edit"></i> Edit PPP Secret</h3>
            </div>
            <div class="card-body">
                <form autocomplete="off" method="post" action="">
                    <input type="hidden" name="id" value="<?= $secr['.id'] ?>">
                    <table class="table">
                        <tr>
                            <td class="align-middle">Name <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="name" required value="<?= htmlspecialchars($secr['name']) ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Password <span class="text-danger">*</span></td>
                            <td><input class="form-control" type="text" name="password" required value="<?= htmlspecialchars($secr['password'] ?? '') ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Service</td>
                            <td>
                                <select class="form-control" name="service">
                                    <?php 
                                    $services = ['any', 'async', 'l2tp', 'ovpn', 'pppoe', 'pptp', 'sstp'];
                                    $current = $secr['service'] ?? 'any';
                                    foreach ($services as $srv): 
                                    ?>
                                        <option value="<?= $srv ?>" <?= $current == $srv ? 'selected' : '' ?>><?= $srv ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Profile</td>
                            <td>
                                <select class="form-control" name="profile">
                                    <option value="default">default</option>
                                    <?php 
                                    $current_prof = $secr['profile'] ?? 'default';
                                    foreach ($profiles as $p): 
                                    ?>
                                        <option value="<?= htmlspecialchars($p['name']) ?>" <?= $current_prof == $p['name'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-middle">Local Address</td>
                            <td><input class="form-control" type="text" name="local_address" value="<?= htmlspecialchars($secr['local-address'] ?? '') ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Remote Address</td>
                            <td><input class="form-control" type="text" name="remote_address" value="<?= htmlspecialchars($secr['remote-address'] ?? '') ?>"></td>
                        </tr>
                        <tr>
                            <td class="align-middle">Comment</td>
                            <td><input class="form-control" type="text" name="comment" value="<?= htmlspecialchars($secr['comment'] ?? '') ?>"></td>
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
