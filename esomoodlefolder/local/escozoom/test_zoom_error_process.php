<?php

require_once('../../config.php');

global $DB, $CFG;

//require_once($CFG->libdir. '/excellib.class.php');
require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->dirroot.'/local/escozoom/escoffierlib.php');

echo "Hello world!";

$service = new mod_zoom_webservice();

$zoommindate = "2016-01-29";
$zoommaxdate = "2016-02-04";
$p = 1;
$ps = 30;
$url = 'report/getaccountreport';
$data = array('from' => $zoommindate, 'to' => $zoommaxdate, 'page_size' => $ps, 'page_number' => $p);
$apicalls++;
//$response = $service->make_call($url, $data);

echo "<br />Bonjour le monde!<br />";

$apicalls++;
try {
	$response = $service->make_call($url, $data);
	print_r($response);
	echo "<br />Guten Tag Welt!<br />";
} catch (moodle_exception $e) {
	// If our API key broke, it will return "Invalid api key or secret.".
	//echo $e->getMessage();
	print_r($e);
	echo "scheisse...";
	if (strpos($e->getMessage(), 'Invalid api key or secret.') === false) {
		// Error is not something expected.
		echo "scheisse...";
	} else {
		echo "herrgottsdonnernacheinmal!";
	}
} finally {
    echo "<br />Dzień dobry świat!<br />";
}

echo "<br />Привет мир!";

function make_call_w_error ($url, $data) {
	try {
		$response = $service->make_call($url, $data);
	} catch (moodle_exception $e) {
		if (strpos($e->getMessage(), 'Invalid api key or secret.') === true) {
			return array('error' => 1);
		}
		return array('error' => 0);
	}
	return $response;
}