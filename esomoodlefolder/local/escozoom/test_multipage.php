<?php

require_once('../../config.php');

global $DB, $CFG;

require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->dirroot.'/local/escozoom/locallib.php');

echo "Hello world!<br />";

//$url = 'user/get';
//$data = array('id' => 'O4sZU-3NTQyMjteKgAr71g');

$service = new mod_zoom_webservice();
//$response = $service->make_call('user/getbyemail', array('email' => 'nstefanski@escoffier.edu', 'login_type' => '100'));

$response = make_call_multipage('report/getaccountreport', array('from' => '2016-1-24', 'to' => '2016-2-5'));

print_r($response);

echo "<br />Bonjour le monde!";