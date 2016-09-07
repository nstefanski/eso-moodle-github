<?php

require_once('../../config.php');

global $CFG;

require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->dirroot.'/local/escozoom/locallib.php');

$service = new mod_zoom_webservice();
$cafesess = FALSE;
$participants = 0;

$url = 'meeting/live';
$data = array();
try {
	$response = $service->make_call($url, $data);
} catch (moodle_exception $e) {
	$response = FALSE;
}

if ($response) {
	if ($response->total_records > 0) {
		$meetings = $response->meetings; //array
	}
	if ($response->page_size > 1) {
		//scroll through pages and get all meetings?
		//this call does not take normal page params, is it possible to get multipage?
	}
	foreach ($meetings as $meeting) {
		if ($meeting->id == 2246980000) {
			$cafesess = TRUE;
			$uuid = $meeting->uuid;
			
			$url = 'metrics/meetingdetail';
			$data = array('meeting_id' => $uuid, 'type' => 1);
			$response = make_call_multipage($url, $data);
			$participants = $response->participants_count;
			break;
		}
	}
}

echo "<div style=\"display: table;\">";

if ($cafesess) {
	if ($participants == 1) {
		echo "<div style=\"float: left; height: 1.5em; width: 1.5em; margin-right: .25em; border-radius: 50%; background: #0f0;
			background: -webkit-linear-gradient(left top, #0f0, #080); background: -o-linear-gradient(bottom right, #0f0, #080); 
			background: -moz-linear-gradient(bottom right, #0f0, #080); background: linear-gradient(to bottom right, #0f0, #080);\"></div>
			<div style=\"float: left; font-family: Helvetica; font-size: large; font-weight: bold; display: inline-block; display: table-cell; vertical-align: middle;\">
			There is $participants participant in the student commons right now.</div>";
	} else {
		echo "<div style=\"float: left; height: 1.5em; width: 1.5em; margin-right: .25em; border-radius: 50%; background: #0f0;
			background: -webkit-linear-gradient(left top, #0f0, #080); background: -o-linear-gradient(bottom right, #0f0, #080); 
			background: -moz-linear-gradient(bottom right, #0f0, #080); background: linear-gradient(to bottom right, #0f0, #080);\"></div>
			<div style=\"float: left; font-family: Helvetica; font-size: large; font-weight: bold; display: inline-block; display: table-cell; vertical-align: middle;\">
			There are $participants participants in the student commons right now.</div>";
	}
} else {
	/* echo "<div style=\"float: left; height: 1.5em; width: 1.5em; margin-right: .25em; border-radius: 50%; background: #f00;
			background: -webkit-linear-gradient(left top, #f00, #800); background: -o-linear-gradient(bottom right, #f00, #800); 
			background: -moz-linear-gradient(bottom right, #f00, #800); background: linear-gradient(to bottom right, #f00, #800);\"></div>
			<div style=\"float: left; font-family: Helvetica; font-size: large; font-weight: bold; display: inline-block; display: table-cell; vertical-align: middle;\">
			There are no participants in the student commons.</div>"; */
	echo "<div style=\"float: left; font-family: Helvetica; font-size: large; font-weight: bold; display: inline-block; display: table-cell; vertical-align: middle;\"></div>";
}

echo "</div>";

?>