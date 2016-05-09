<?php

require_once('../../config.php');

global $DB, $CFG;

require_once($CFG->libdir. '/excellib.class.php');
require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php'); //tk 11-20-15
require_once($CFG->dirroot.'/mod/zoom/escoffierlib.php');

$mindate = (empty($_GET['mindate'])) ? date_format_string(time()-7*24*60*60, '%Y-%m-%d') : $_GET['mindate'];
$minclock = (empty($_GET['minclock'])) ? '00:05' : $_GET['minclock'];
$maxdate = (empty($_GET['maxdate'])) ? date_format_string(time(), '%Y-%m-%d') : $_GET['maxdate'];
$maxclock = (empty($_GET['maxclock'])) ? '00:05' : $_GET['maxclock'];
$limit = (empty($_GET['limit'])) ? 1080 : $_GET['limit']; //18 minutes
$action = (empty($_GET['action'])) ? 'Calculate' : $_GET['action'];

if ($mindate && $minclock) {
	list($y, $m, $d) = explode("-", $mindate);
	list($h, $i, $s) = explode(":", $minclock);
	$mintime = make_timestamp($y, $m, $d, $h, $i, $s);
}

if ($maxdate && $maxclock) {
	list($y, $m, $d) = explode("-", $maxdate);
	list($h, $i, $s) = explode(":", $maxclock);
	$maxtime = make_timestamp($y, $m, $d, $h, $i, $s);
}

$rows = array();

//instructor individual course stats
//*
$sql = "SELECT CONCAT(u.id,'-',c.id) AS id, CONCAT(u.firstname,' ',u.lastname) AS user, 
	'' AS usergroup, c.shortname AS course, cc.name AS category, 

(SELECT count(DISTINCT ra2.id) FROM {role_assignments} ra2 
	JOIN {context} cx2 ON cx2.id = ra2.contextid 
	WHERE ra2.roleid = 5 AND cx2.instanceid = c.id) AS 'numstudents', 

'' AS dedication, '' AS sentmessages, 

(SELECT count(*) FROM {forum_posts} posts 
	JOIN {forum_discussions} disc ON posts.discussion = disc.id 
	WHERE posts.userid = ra.userid 
	AND posts.modified >= $mintime AND posts.modified <= $maxtime 
	AND disc.course = c.id) AS forumposts, 

(SELECT ( (SUM(CASE WHEN f.component = 'assignfeedback_poodll' THEN f.filesize ELSE 0 END)/960000 + 
	SUM(CASE WHEN f.component = 'assignfeedback_file' THEN f.filesize ELSE 0 END)/570000) / COUNT(f.id) )
	/* filesize in this table is stored in Bytes
	to determine the divisor in this formula, take the bitrate, normally Kbps (Kilo bits per second), and convert to Bpm (Bytes per minute) 
		128 Kilo bits per second
		/ 8 = 16 Kilo Bytes per second
			  * 60 = 960 Kilo Bytes per minute 
					 * 1000 = 960000 Bytes per minute
		128 / 8 * 60 * 1000 = 960000
	see http://www.audiomountain.com/tech/audio-file-size.html for details
	*/
	FROM {files} f 
	JOIN {context} cx2 ON f.contextid = cx2.id 
	JOIN {course_modules} cm ON cx2.instanceid = cm.id
	WHERE cm.course = c.id 
	AND (f.component = 'assignfeedback_file' OR f.component = 'assignfeedback_poodll') 
	AND f.timemodified >= $mintime AND f.timemodified <= $maxtime 
	AND f.userid = ra.userid AND f.filesize > 0 ) AS feedbackscore, 

(SELECT (SUM(CASE WHEN f.component = 'assignfeedback_poodll' THEN f.filesize ELSE 0 END)/960000 + 
	SUM(CASE WHEN f.component = 'assignfeedback_file' THEN f.filesize ELSE 0 END)/570000) 
	FROM {files} f 
	JOIN {context} cx2 ON f.contextid = cx2.id 
	JOIN {course_modules} cm ON cx2.instanceid = cm.id
	WHERE cm.course = c.id 
	AND (f.component = 'assignfeedback_file' OR f.component = 'assignfeedback_poodll') 
	AND f.timemodified >= $mintime AND f.timemodified <= $maxtime 
	AND f.userid = ra.userid AND f.filesize > 0 ) AS totalfeedback, 

CASE WHEN c.shortname LIKE 'CA181%' OR c.shortname LIKE 'CA202%' 
THEN ''
ELSE (SELECT count(DISTINCT sub.id) FROM {assign_submission} sub 
	JOIN {assign} a ON a.id = sub.assignment 
	WHERE sub.timecreated <= a.duedate 
	AND a.duedate >= $mintime 
	AND sub.timecreated >= $mintime AND sub.timecreated <= $maxtime 
	AND sub.status = 'submitted' 
	AND a.course = c.id ) / ((SELECT count(DISTINCT a.id) FROM {assign} a 
	WHERE a.course = c.id 
	AND a.duedate >= $mintime 
	AND a.duedate <= $maxtime ) * (SELECT count(DISTINCT ra2.id) 
	FROM {role_assignments} ra2 
	JOIN {context} cx2 ON cx2.id = ra2.contextid 
	WHERE ra2.roleid = 5 AND cx2.instanceid = c.id)) 
END AS ontime, 

'' AS livesession, 

CASE WHEN c.shortname LIKE 'EXT20%'  
THEN ''
ELSE (SELECT sum(ag.grade)/count(DISTINCT ag.id) FROM {assign_grades} ag 
	JOIN {assign} a ON ag.assignment = a.id 
	WHERE ag.grader = ra.userid 
	AND ag.timemodified >= $mintime AND ag.timemodified <= $maxtime
	AND a.course = c.id AND ag.grade > 0) 
END AS avggrade 

FROM {role_assignments} ra 
JOIN {context} cx ON cx.id = ra.contextid 
JOIN {course} c ON c.id = cx.instanceid 
JOIN {course_categories} cc ON cc.id = c.category 
JOIN {user} u ON ra.userid = u.id 
WHERE (ra.roleid = 3 OR ra.roleid = 10) 
AND u.firstname LIKE 'Chef %' 
AND u.firstname NOT LIKE '%Graham' 
AND c.shortname NOT LIKE '%rientation%' 
AND cc.path LIKE '/42/43%' 
order by c.id ";
$instructor_roles = $DB->get_records_sql($sql);

$headers = array((object) array_keys((array)reset($instructor_roles)));

$rows = array_merge($headers,$instructor_roles);

//instructor sitewide stats
$service = new mod_zoom_webservice();	//tk 11-20-15

$zoomuserids = array();
$allmessages = array();
$sitededication = array();

$sql = "SELECT u.id, u.firstname, u.lastname, u.email FROM {user} u 
		WHERE u.firstname LIKE 'Chef %' 
		AND u.firstname NOT LIKE '%Graham' 
		AND (SELECT count(*) FROM {role_assignments} ra 
			JOIN {context} cx ON cx.id = ra.contextid 
			JOIN {course} c ON c.id = cx.instanceid 
			JOIN {course_categories} cc ON cc.id = c.category 
			WHERE ra.userid = u.id 
			AND cc.path LIKE '/42/43%' 
			AND (ra.roleid = 3 OR ra.roleid = 10) ) > 0 
		order by u.firstname ";
$instructors = $DB->get_records_sql($sql); //tk 2-3-16

foreach ($instructors AS $instructor) {
	//sent messages
	$where = 'useridfrom = :useridfrom AND timecreated >= :mintime AND timecreated <= :maxtime AND notification = 0';
	$selectparams = array(
		'useridfrom' => $instructor->id,
		'mintime' => $mintime,
		'maxtime' => $maxtime
	);
	$sentmessages[$instructor->id] = $DB->count_records_select('message', $where, $selectparams) + $DB->count_records_select('message_read', $where, $selectparams);

	//get site time
	//code ripped from dedication_lib.php with param:courseid removed
	$where = 'userid = :userid AND time >= :mintime AND time <= :maxtime';
	$selectparams = array(
		'userid' => $instructor->id,
		'mintime' => $mintime,
		'maxtime' => $maxtime
	);
	$logs = $DB->get_records_select('log', $where, $selectparams, 'time ASC', 'id,time,ip');
	$sitededication[$instructor->id] = 0;
	if ($logs) {
		$previouslog = array_shift($logs);
		$previouslogtime = $previouslog->time;
		$sessionstart = $previouslogtime;

		foreach ($logs as $log) {
			if (($log->time - $previouslogtime) > $limit) {
				$dedication = $previouslogtime - $sessionstart;
				$sitededication[$instructor->id] += $dedication;
				$sessionstart = $log->time;
			}
			$previouslogtime = $log->time;
		}
		$dedication = $previouslogtime - $sessionstart;
		$sitededication[$instructor->id] += $dedication; //seconds
	}
	
	//build results array
	$rows[] = (object) array(
		'id' => $instructor->id . "-1",
		'name' => $instructor->firstname . " " . $instructor->lastname,
		'usergroup' => "",
		'course' => "Online Campus",
		'category' => "Sitewide",
		'numstudents' => "",
		'dedication' => $sitededication[$instructor->id] / (60*60), //hours
		'sentmessages' => $sentmessages[$instructor->id],
	);
	
	//get zoom time
	$apicalls = 0;
	
	$url = 'user/getbyemail';
	$data = array('email' => $instructor->email, 'login_type' => 100);
	$apicalls++;
	try {
		$response = $service->make_call($url, $data);
		$zoomusernames[$instructor->id] = "$response->first_name $response->last_name";
	} catch (moodle_exception $e) {
		$response = false;
		$zoomusernames[$instructor->id] = "";
	}
	
	$url = 'report/getaccountreport';
	$data = array('from' => $mindate, 'to' => $maxdate);
	$response = make_call_multipage($url, $data);
	if ($response && $response->total_records > 0) {
		$apicalls += $response->api_calls;
		foreach ($response->users AS $zoomuser) {
			if ($zoomuser->meetings > 0) {
				$url = 'report/getuserreport';
				$data = array('user_id' => $zoomuser->user_id, 'from' => $mindate, 'to' => $maxdate);
				$response = make_call_multipage($url, $data);
				if ($response && $response->total_records > 0) {
					$apicalls += $response->api_calls;
					foreach ($response->meetings AS $meeting) {
						foreach ($meeting->participants AS $participant) {
							if ($participant->name == $zoomusernames[$instructor->id]) {
								$join_time = date_create(str_replace(array("T","Z"),array(" "),$participant->join_time));
								$leave_time = date_create(str_replace(array("T","Z"),array(" "),$participant->leave_time));
								$diff = date_diff($join_time,$leave_time);
								$secs = $diff->s + 60 * ($diff->i + 60 * ($diff->h + 24 * $diff->d));
								$zoomtime[$instructor->id] += $secs;
							}
						}
					}
				}
			}
		}
	}
	
	//build results array
	$rows[] = (object) array(
		'id' => $instructor->id . "-z",
		'name' => $instructor->firstname . " " . $instructor->lastname,
		'usergroup' => "",
		'course' => "Live Sessions",
		'category' => "Live Sessions",
		'numstudents' => "",
		'dedication' => $zoomtime[$instructor->id] / (60*60), //hours
		'sentmessages' => "",
	);
}

if ($action == 'Download') {
	$filename = "Instructor Report $mindate to $maxdate";

	$workbook = new MoodleExcelWorkbook('-', 'excel5');
	$workbook->send(clean_filename($filename));

	$myxls = $workbook->add_worksheet('instructor_report');
		$row_count = 0;
	foreach ($rows as $row) {
		$column_count = 0;
		foreach ($row as $content) { //foreach ($row as $index => $content) {
			$myxls->write($row_count, $column_count, $content); //$myxls->write($row_count, $index, $content);
			$column_count++;
		}
		$row_count++;
	}

	$workbook->close();

	return $workbook;
} else {
	echo '<h1>Instructor Dedication Report</h1>';
	echo 'Limit: <select name="limit" form="instructorreport">';
	for ($i=1; $i<=150; $i++) {
		echo '<option value="'.$i*60;
		if ($i*60 == $limit) {
			echo '" selected="selected';
		}
		echo '">'.$i.' minutes</option>';
	}
	echo '</select>';//*/
	
	$page_name = 'idr.php';
	
	echo '<form action="'.$page_name.'" method="get" id="instructorreport">'.
		'From: <input type="date" name="mindate" value="'.$mindate.'">'.' <input type="time" name="minclock" value="'.$minclock.'"></br>'.
		'To: &nbsp; &nbsp; <input type="date" name="maxdate" value="'.$maxdate.'">'.' <input type="time" name="maxclock" value="'.$maxclock.'"></br>'.
		'<div id="buttons"></br><input type="submit" name="action" value="Calculate" style="float: left; margin-right: 20px;">'.
		'<input type="submit"  name="action" value="Download"></div></form>';
	
	echo "<table border=\"1\">";
	foreach ($rows AS $row) {
		echo "<tr>";
		foreach ($row AS $column) {
			echo "<td>$column</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}