<?php
/**
 * Remove PPP Profile
 * Included by index.php
 */

if ($session == "") {
    echo "<script>window.location='./?id=sessions'</script>";
}

if ($removepprofile != "") {
    $API->comm("/ppp/profile/remove", array(
        ".id" => "$removepprofile",
    ));
    echo "<script>window.location='./?ppp=profiles&session=" . $session . "'</script>";
}
