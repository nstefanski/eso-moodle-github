<?php

require_once('../config.php');

global $CFG;

require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');

$table_data = array();
$total_bytes = 0;
$total_count = 0;
$service = new mod_zoom_webservice();

$url = 'user/list';
$data = array('page_size' => 50, 'page_number' => 1);
$response = $service->make_call($url, $data);

if ($response && $response->total_records > 0) {
	$users = $response->users; //array
	foreach ($users as $user) {
		$table_data[] = (object) [
			'id' => $user->id,
			'email' => $user->email,
			'recording_size' => 0,
			'recording_count' => 0,
		];
	}
}

echo "<table border=\"1\" >
		<tr>
			<th>Email</th>
			<th>Recordings (#)</th>
			<th>Recordings (GB)</th>
		</tr>";

foreach ($table_data as $user) {
	$url = 'recording/list';
	$data = array('host_id' => $user->id, 'page_size' => 50, 'page_number' => 1);
	$response = $service->make_call($url, $data);

	$bytes = 0;
	$count = 0;

	if ($response && $response->total_records > 0) {
		$meetings = $response->meetings; //array
		foreach ($meetings as $meeting) {
			if($meeting->total_size > 0) {
				$bytes = $bytes + $meeting->total_size;
				$count++;
				$total_bytes = $total_bytes + $meeting->total_size;
				$total_count++;
			}
		}
	}
	
	$user->recording_size = round($bytes / 1024 / 1024 / 1024, 2); //GB
	$user->recording_count = $count;
	echo "<tr>
			<td>$user->email</td>
			<td>$user->recording_count</td>
			<td>$user->recording_size</td>";
	
	if ($user->enable_cloud_auto_recording == "true") {
		switch ($user->email) {
			case "cafe@escoffier.edu":
				echo "<td>10 days</td>";
				break;
			case "nstefanski@escoffier.edu":
				echo "<td>2 hours</td>";
				break;
			default:
				echo "<td>2 days</td>";
		}
	}
	
	echo "</tr>";
}

$total_gb = round($total_bytes / 1024 / 1024 / 1024, 2); //GB

echo "<tr>
		<td><b>Total</b></td>
		<td>$total_count</td>
		<td>$total_gb</td>
	</tr>
</table>";

//echo 'Filesize:<br />';
//echo $bytes . ' bytes<br />';
//echo round($bytes / 1024 / 1024 / 1024, 2) . ' GB<br />';

?>