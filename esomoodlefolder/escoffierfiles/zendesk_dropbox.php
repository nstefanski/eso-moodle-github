<?php
 /* Insert path to Moodle config here */
require_once('../config.php');
require_login();

require_once('update_zen_phone.php');
  
$name = $USER->firstname.' '. $USER->lastname; 
$email = $USER->email;

$dropboxid = "20137030";
$zdprefix = "escoffier"; 
$zdauthpath = "http://my.escoffieronline.com/escoffierfiles/zendesk_auth.php";

$results = updateZenPhone($USER);

//when API calls are done, redirect to Zendesk Auth with return url set to dropbox
$dropbox_url = "http://".$zdprefix.".zendesk.com/account/dropboxes/".$dropboxid."?name=".$name."&email=".$email;

$location = $zdauthpath . "?return_to=" . urlencode($dropbox_url);

header("Location: " . $location);
?>