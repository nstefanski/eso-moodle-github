<?php
require_once "../config.php";
require_login();

require_once('update_zen_phone.php');

$zdprefix = "escoffier"; 
$zdauthpath = "http://my.escoffieronline.com/escoffierfiles/zendesk_auth.php";

$results = updateZenPhone($USER);

$location = $zdauthpath . "?return_to=" . urlencode("https://" . $zdprefix . ".zendesk.com/hc/en-us/requests");

// Redirect
header("Location: " . $location);
?>
