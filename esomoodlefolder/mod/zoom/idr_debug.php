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
//$category = (empty($_GET['category'])) ? 43 : $_GET['category'];  //this is for online only now
//$extradata = (empty($_GET['extradata'])) ? 0 : $_GET['extradata'];
//$download = (empty($_GET['download'])) ? FALSE : $_GET['download'];
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

//$wherecat = $category > 0 ? "AND (ccat.id = $category OR ccat.parent = $category) " : "";

$rows = array();

//instructor individual course stats
/*
$sql = "SELECT CONCAT(u.id,'-',c.id) AS id, CONCAT(u.firstname,' ',u.lastname) AS user, 
	'' AS usergroup, c.shortname AS course, cc.name AS category, 

(SELECT count(*) FROM {role_assignments} ra2 
	JOIN {context} ct2 ON ct2.id = ra2.contextid 
	WHERE ra2.roleid = 5 AND ct2.instanceid = c.id) AS 'numstudents', 

'' AS dedication, '' AS sentmessages, 

(SELECT count(*) FROM {forum_posts} posts 
	JOIN {forum_discussions} disc ON posts.discussion = disc.id 
	WHERE posts.userid = ra.userid 
	AND posts.modified >= $mintime AND posts.modified <= $maxtime 
	AND disc.course = c.id) AS forumposts, 

'' AS feedbackscore, '' AS totalfeedback, 

CASE WHEN c.shortname LIKE 'CA181%' OR c.shortname LIKE 'CA202%' 
THEN ''
ELSE (SELECT count(*) FROM {assign_submission} sub 
	JOIN {assign} a3 ON a3.id = sub.assignment 
	WHERE sub.timemodified <= a3.duedate 
	AND a3.duedate >= $mintime 
	AND sub.timemodified >= $mintime AND sub.timemodified <= $maxtime 
	AND sub.status = 'submitted' 
	AND a3.course = c.id ) / ((SELECT count(*) FROM {assign} a4 
	WHERE a4.course = c.id 
	AND a4.duedate >= $mintime 
	AND a4.duedate <= $maxtime ) * (SELECT count(*) 
	FROM {role_assignments} ra2 
	JOIN {context} ct2 ON ct2.id = ra2.contextid 
	WHERE ra2.roleid = 5 AND ct2.instanceid = c.id)) 
END AS ontime, 

'' AS livesession, 

(SELECT sum(ag.grade)/count(DISTINCT ag.id) FROM {assign_grades} ag 
	JOIN {assign} a ON ag.assignment = a.id 
	WHERE ag.grader = ra.userid 
	AND ag.timemodified >= $mintime AND ag.timemodified <= $maxtime
	AND a.course = c.id AND ag.grade > 0) AS avggrade 

/* , (SELECT CONCAT(count(DISTINCT f.filesize),'@',sum(DISTINCT f.filesize)/960000) 
	FROM {assignfeedback_poodll} af 
	JOIN {assign_grades} ag2 ON af.grade = ag2.id 
	JOIN {files} f ON af.filename = f.filename 
	JOIN {assign} a2 ON af.assignment = a2.id 
	WHERE ag2.grader = ra.userid 
	AND f.timemodified >= $mintime AND f.timemodified <= $maxtime 
	AND f.filename <> '.' 
	AND a2.course = c.id ) AS poodllfeedback, 

(SELECT CONCAT(count(DISTINCT f2.filesize),'@',sum(DISTINCT f2.filesize)/2400000) 
	FROM {files} f2 
	JOIN {context} cx2 ON f2.contextid = cx2.id 
	JOIN {course_modules} cm ON cx2.instanceid = cm.id 
	WHERE f2.userid = ra.userid 
	AND f2.component = 'assignfeedback_file' 
	AND f2.timemodified >= $mintime AND f2.timemodified <= $maxtime 
	AND f2.filename <> '.' 
	AND cm.course = c.id ) AS filefeedback, /

FROM {role_assignments} ra 
JOIN {context} cx ON cx.id = ra.contextid 
JOIN {course} c ON c.id = cx.instanceid 
JOIN {course_categories} cc ON cc.id = c.category 
JOIN {user} u ON ra.userid = u.id 
WHERE (ra.roleid = 3 OR ra.roleid = 10) 
AND u.firstname LIKE 'Chef %' 
AND c.shortname NOT LIKE '%rientation%' 
AND cc.path LIKE '/42/43%' 
order by c.id ";
$instructor_roles = $DB->get_records_sql($sql);

$headers = array((object) array_keys((array)reset($instructor_roles)));

foreach ($instructor_roles AS $instructor_role) {
	list($uid, $cid) = explode("-", $instructor_role->id);
	$fbcount = 0;
	
	$sql = "SELECT af.id, files.filesize AS 'bytes' FROM {assignfeedback_poodll} af ".
			"JOIN {assign_grades} grades ON af.grade = grades.id ".
			"JOIN {files} files ON af.filename = files.filename ".
			"JOIN {assign} assign ON af.assignment = assign.id ".
			"WHERE grades.grader = $uid ".
			"AND files.timemodified >= $mintime AND files.timemodified <= $maxtime ".
			"AND files.filename <> '.' ".
			"AND course = $cid";
	$poodllfeedback = $DB->get_records_sql($sql);
	foreach ($poodllfeedback as $fb) {
		$poodlltotal = $poodlltotal + $fb->bytes;
		$fbcount++;
	}
	$poodlltotal = $poodlltotal/960000; //approx bytes per minute at 128kbps
	
	$sql = "SELECT files.id, files.filesize AS 'bytes' FROM {files} files ".
			"JOIN {context} c ON files.contextid = c.id ".
			"JOIN {course_modules} cm ON c.instanceid = cm.id ".
			"WHERE files.userid = $uid ".
			"AND files.component = 'assignfeedback_file'".
			"AND files.timemodified >= $mintime AND files.timemodified <= $maxtime ".
			"AND files.filename <> '.' ".
			"AND cm.course = $cid";
	$filefeedback = $DB->get_records_sql($sql);
	foreach ($filefeedback as $fb) {
		$filetotal = $filetotal + $fb->bytes;
		$fbcount++;
	}
	$filetotal = $filetotal/2400000; //approx bytes per minute at 320kbps
	
	/*$sql = "SELECT f.id, (f.filesize/960000) AS mins 
	FROM {files} f 
	JOIN {assignfeedback_poodll} af ON af.filename = f.filename 
	JOIN {assign_grades} ag2 ON af.grade = ag2.id 
	JOIN {assign} a2 ON af.assignment = a2.id 
	WHERE ag2.grader = $uid 
	AND f.timemodified >= $mintime AND f.timemodified <= $maxtime 
	AND f.filename <> '.' 
	AND a2.course = $cid ";
	$poodllfb = $DB->get_records_sql($sql);

	$sql = "SELECT f2.id, (f2.filesize/2400000) AS mins 
	FROM {files} f2 
	JOIN {context} cx2 ON f2.contextid = cx2.id 
	JOIN {course_modules} cm ON cx2.instanceid = cm.id 
	WHERE f2.userid = $uid 
	AND f2.component = 'assignfeedback_file' 
	AND f2.timemodified >= $mintime AND f2.timemodified <= $maxtime 
	AND f2.filename <> '.' 
	AND cm.course = $cid ";
	$filefb = $DB->get_records_sql($sql);
	
	$feedback = array_merge($poodllfb, $filefb);
	print_r($feedback);
	foreach ($feedback AS $fb){
		if ($fb->mins > 0) {
			$fbcount++;
			$fbmins += $fb->mins;
		}
	}*/
	/*$poodll = explode("@", $instructor_role->poodllfeedback);
	$file = explode("@", $instructor_role->filefeedback);
	
	$fbmins = $poodll[1] + $file[1];
	$fbcount = $poodll[0] + $file[0];
	
	$fbtotal = $poodlltotal + $filetotal;
	
	$instructor_role->feedbackscore = $fbtotal/$fbcount;
	$instructor_role->totalfeedback = $fbtotal;
}

$rows = array_merge($headers,$instructor_roles);

//instructor sitewide stats
$service = new mod_zoom_webservice();	//tk 11-20-15

$zoomuserids = array();
$allmessages = array();
$sitededication = array();

$sql = "SELECT u.id, u.firstname, u.lastname, u.email FROM {user} u ".
		"WHERE u.firstname LIKE 'Chef %' ".
		"AND (SELECT count(*) FROM {role_assignments} ra ".
		"	JOIN {context} cx ON cx.id = ra.contextid ".
		"	JOIN {course} c ON c.id = cx.instanceid ".
		"	JOIN {course_categories} cc ON cc.id = c.category ".
		"	WHERE ra.userid = u.id ".
		"	AND cc.path LIKE '/42/43%' ".
		"	AND (ra.roleid = 3 OR ra.roleid = 10) ) > 0 ".
		"order by u.firstname ";
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
		'course' => "",
		'category' => "Live Sessions",
		'numstudents' => "",
		'dedication' => $zoomtime[$instructor->id] / (60*60), //hours
		'sentmessages' => "",
	);
}//*/

//print_r($instructors);

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

/* , (SELECT CONCAT(count(DISTINCT f.filesize),'@',sum(DISTINCT f.filesize)/960000) 
	FROM {assignfeedback_poodll} af 
	JOIN {assign_grades} ag2 ON af.grade = ag2.id 
	JOIN {files} f ON af.filename = f.filename 
	JOIN {assign} a2 ON af.assignment = a2.id 
	WHERE ag2.grader = ra.userid 
	AND f.timemodified >= $mintime AND f.timemodified <= $maxtime 
	AND f.filename <> '.' 
	AND a2.course = c.id ) AS poodllfeedback, 

(SELECT CONCAT(count(DISTINCT f2.filesize),'@',sum(DISTINCT f2.filesize)/2400000) 
	FROM {files} f2 
	JOIN {context} cx2 ON f2.contextid = cx2.id 
	JOIN {course_modules} cm ON cx2.instanceid = cm.id 
	WHERE f2.userid = ra.userid 
	AND f2.component = 'assignfeedback_file' 
	AND f2.timemodified >= $mintime AND f2.timemodified <= $maxtime 
	AND f2.filename <> '.' 
	AND cm.course = c.id ) AS filefeedback, */
	
//*
		echo "Mintime: $mintime  Maxtime: $maxtime <br /><br />";
		
		$sql = "SELECT sub.id, sub.timemodified, sub.status, a3.duedate FROM {assign_submission} sub 
	JOIN {assign} a3 ON a3.id = sub.assignment 
	WHERE  a3.course = 528 
	AND sub.timemodified <= a3.duedate 
	AND a3.duedate >= $mintime 
	AND sub.timecreated >= $mintime AND sub.timecreated <= $maxtime 
	/*AND sub.status = 'submitted' */ ";
		$feedback = $DB->get_records_sql($sql);
		print_r($feedback);
		echo "<br /><br />";
		
		$sql = "SELECT a4.id FROM {assign} a4 
	WHERE a4.course = 528 
	AND a4.duedate >= $mintime 
	AND a4.duedate <= $maxtime ";
		$feedback = $DB->get_records_sql($sql);
		print_r($feedback);
		echo "<br /><br />";
		
		$sql = "SELECT ra2.id 
	FROM {role_assignments} ra2 
	JOIN {context} ct2 ON ct2.id = ra2.contextid 
	WHERE ra2.roleid = 5 AND ct2.instanceid = 528 ";
		$feedback = $DB->get_records_sql($sql);
		print_r($feedback);
		echo "<br /><br />";
		
		$sql = "SELECT ag.id, ag.grade FROM {assign_grades} ag 
	JOIN {assign} a ON ag.assignment = a.id 
	WHERE ag.grader = 1870  
	AND ag.timemodified >= $mintime AND ag.timemodified <= $maxtime
	AND a.course = 520 AND ag.grade > 0";
		$feedback = $DB->get_records_sql($sql);
		print_r($feedback);
		
		$count_shit = 0;
		$total_shit = 0;
		foreach ($feedback AS $fb) {
			$count_shit++;
			$total_shit += $fb->grade;
		}
		
		echo "<br />Count: $count_shit <br />";
		echo "<br />Total: $total_shit <br />";
		echo "<br />Average: ".$total_shit/$count_shit." <br />";

	
	echo '<h1>Instructor Report</h1>';
	echo 'Limit: <select name="limit" form="instructorreport">';
	for ($i=1; $i<=150; $i++) {
		echo '<option value="'.$i*60;
		if ($i*60 == $limit) {
			echo '" selected="selected';
		}
		echo '">'.$i.' minutes</option>';
	}
	echo '</select>';//*/
	
	//$categorymax = key( array_slice( $DB->get_records('course_categories', null, 'id ASC', 'id'), -1, 1, TRUE ) );
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