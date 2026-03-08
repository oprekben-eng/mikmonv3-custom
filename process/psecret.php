<?php
/**
 * Process enable/disable/remove PPP Secret
 * Included by index.php
 */

if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

if ($enablesecr != "") {
    $API->comm("/ppp/secret/enable", array(
        ".id" => "$enablesecr",
    ));
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}

if ($disablesecr != "") {
    $API->comm("/ppp/secret/disable", array(
        ".id" => "$disablesecr",
    ));
    // Check active connection and remove it since the secret is disabled
    $API->comm("/ppp/active/remove", array(
        "?name" => "$disablesecr",
    ));
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}

if ($removesecr != "") {
    $API->comm("/ppp/secret/remove", array(
        ".id" => "$removesecr",
    ));
    // Remove active connection
    $API->comm("/ppp/active/remove", array(
        "?name" => "$removesecr",
    ));
    echo "<script>window.location='./?ppp=secrets&session=" . $session . "'</script>";
}
