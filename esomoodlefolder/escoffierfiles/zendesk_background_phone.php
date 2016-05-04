<?php
require_once('../config.php');
require_login();
require_once('update_zen_phone.php');

$results = updateZenPhone($USER);
//print_r($results);
//when API calls are done, do nothing

?>