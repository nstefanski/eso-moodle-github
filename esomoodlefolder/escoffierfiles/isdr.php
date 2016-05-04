<?php

require_once('../config.php');

global $DB, $CFG;

require_once($CFG->libdir. '/excellib.class.php');
require_once($CFG->dirroot.'/blocks/dedication/dedication_lib.php');

$mindate = (empty($_GET['mindate'])) ? date_format_string(time()-(8*7*24*60*60), '%Y-%m-%d') : $_GET['mindate'];
$minclock = (empty($_GET['minclock'])) ? '00:05' : $_GET['minclock'];
$maxdate = (empty($_GET['maxdate'])) ? date_format_string(time()+(24*60*60), '%Y-%m-%d') : $_GET['maxdate'];
$maxclock = (empty($_GET['maxclock'])) ? '00:05' : $_GET['maxclock'];
$limit = (empty($_GET['limit'])) ? 1080 : $_GET['limit']; //18 minutes
$defaultOri = (empty($_GET['defaultOri'])) ? '' : $_GET['defaultOri'];
$defaultCore = (empty($_GET['defaultCore'])) ? '' : $_GET['defaultCore'];
$defaultPracCA = (empty($_GET['defaultPracCA'])) ? '' : $_GET['defaultPracCA'];
$defaultPracBK = (empty($_GET['defaultPracBK'])) ? '' : $_GET['defaultPracBK'];
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
//$instructor_roles = $DB->get_records_sql($sql);

//$headers = array((object) array_keys((array)reset($instructor_roles)));

//$rows = array_merge($headers,$instructor_roles);

//instructor sitewide stats
//$service = new mod_zoom_webservice();	//tk 11-20-15

//$zoomuserids = array();
//$allmessages = array();
//$sitededication = array();

$sql = "SELECT u.id AS md_id, 
			(SELECT uid.data FROM {user_info_data} uid 
				JOIN {user_info_field} uif ON uid.fieldid = uif.id AND uif.shortname = 'cvueid' 
				WHERE uid.userid = u.id ) AS CVue_id, 
			u.firstname, u.lastname, u.email, FROM_UNIXTIME(startdate.data) AS startdate, 
			CASE WHEN campus.data = 'Boulder - Online' THEN 'Culinary Arts' 
				WHEN campus.data = 'Online' THEN 'Baking and Pastry' 
				ELSE 'N/a' END AS program,
			status.data AS status, 
				
			ori_c.id AS oriid, core_c.id AS coreid, prac_c.id AS pracid, 
			'' AS ori_ded, '' AS core_ded, '' AS prac_ded, 
			0 AS ori_avses, 0 AS core_avses, 0 AS prac_avses 

		FROM {user} u 
			JOIN {user_info_data} startdate ON startdate.userid = u.id AND 
				(SELECT uif.shortname FROM {user_info_field} uif WHERE startdate.fieldid = uif.id) LIKE 'startdate' 
			JOIN {user_info_data} campus ON campus.userid = u.id AND 
				(SELECT uif.shortname FROM {user_info_field} uif WHERE campus.fieldid = uif.id) LIKE 'campus' 
			JOIN {user_info_data} programtype ON programtype.userid = u.id AND 
				(SELECT uif.shortname FROM {user_info_field} uif WHERE programtype.fieldid = uif.id) LIKE 'programtype' 
			JOIN {user_info_data} status ON status.userid = u.id AND 
				(SELECT uif.shortname FROM {user_info_field} uif WHERE status.fieldid = uif.id) LIKE 'Status' 
			LEFT JOIN {course} ori_c ON ori_c.id = (SELECT c.id
				FROM {user_enrolments} ue 
				JOIN {enrol} e ON ue.enrolid = e.id 
				JOIN {course} c ON e.courseid = c.id AND c.shortname LIKE '%orientation%' 
				WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)
			LEFT JOIN {course} core_c ON core_c.id = (SELECT c.id
				FROM {user_enrolments} ue 
				JOIN {enrol} e ON ue.enrolid = e.id 
				JOIN {course} c ON e.courseid = c.id AND c.shortname LIKE 'CE115%' 
				WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)
			LEFT JOIN {course} prac_c ON prac_c.id = (SELECT c.id
				FROM {user_enrolments} ue 
				JOIN {enrol} e ON ue.enrolid = e.id 
				JOIN {course} c ON e.courseid = c.id 
					AND (c.shortname LIKE 'CA102%' OR c.shortname LIKE 'BK101%') 
				WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)

		WHERE startdate.data IS NOT NULL 
			AND startdate.data > (UNIX_TIMESTAMP() - (2.5*7*24*60*60) ) 
			AND startdate.data < (UNIX_TIMESTAMP() + (6.5*7*24*60*60) ) 
			AND programtype.data = 'Certificate Program' 
			AND campus.data <> 'Boulder' 
			AND campus.data <> 'Austin' 
			/* AND u.suspended = 0 */ 
			AND u.deleted = 0 
			/* AND (SELECT uid.data FROM {user_info_data} uid 
				JOIN {user_info_field} uif ON uid.fieldid = uif.id AND uif.shortname = 'Status' 
				WHERE uid.userid = u.id ) = 'Active' */ 

		ORDER BY status.data, ori_c.id, core_c.id, prac_c.id ";
$stus = $DB->get_records_sql($sql);

//print_r($stus);

$lastOri = 0;
$lastCore = 0;
$lastPrac = 0;

foreach ($stus AS $stu) {
	//sent messages
	/*$where = 'useridfrom = :useridfrom AND timecreated >= :mintime AND timecreated <= :maxtime AND notification = 0';
	$selectparams = array(
		'useridfrom' => $stu->id,
		'mintime' => $mintime,
		'maxtime' => $maxtime
	);
	$sentmessages[$stu->id] = $DB->count_records_select('message', $where, $selectparams) + $DB->count_records_select('message_read', $where, $selectparams);//*/

	$student = $DB->get_record('user', array('id' => $stu->md_id), 'id,firstname,lastname,email');
	
	if($stu->oriid || $defaultOri) {
		if(!$stu->oriid) {
			$stu->oriid = $defaultOri;
		}
		if ($lastOri != $stu->oriid) {
			$lastOri = $stu->oriid;
			$oriCourse = $DB->get_record('course', array('id' => $stu->oriid), 'id,shortname');
			$oriDm = new block_dedication_manager($oriCourse, $mintime, $maxtime, $limit);
		}
		$ori_dedDetail = $oriDm->get_user_dedication($student, false);
		foreach ($ori_dedDetail AS $sesDetail) {
			$stu->ori_ded += $sesDetail->dedicationtime;
		}
		$stu->ori_ded /= 3600; //hours
		$stu->ori_avses = ($stu->ori_ded / count($ori_dedDetail) * 60); //mins
		//$stu->ori_ded = ($oriDm->get_user_dedication($student, true))/3600;//hours
	}
	if($stu->coreid || $defaultCore) {
		if(!$stu->coreid) {
			$stu->coreid = $defaultCore;
		}
		if ($lastCore != $stu->coreid) {
			$lastCore = $stu->coreid;
			$coreCourse = $DB->get_record('course', array('id' => $stu->coreid), 'id,shortname');
			$coreDm = new block_dedication_manager($coreCourse, $mintime, $maxtime, $limit);
		}
		$core_dedDetail = $coreDm->get_user_dedication($student, false);
		foreach ($core_dedDetail AS $sesDetail) {
			$stu->core_ded += $sesDetail->dedicationtime;
		}
		$stu->core_ded /= 3600; //hours
		$stu->core_avses = ($stu->core_ded / count($core_dedDetail) * 60); //mins
		//$stu->core_ded = ($coreDm->get_user_dedication($student, true))/3600;//hours
	}
	if ($stu->pracid || ($defaultPracCA && $defaultPracBK)) {
		if(!$stu->pracid) {
			if ($stu->program == 'Culinary Arts') {
				$stu->pracid = $defaultPracCA;
			} else {
				$stu->pracid = $defaultPracBK;
			}
		}
		if ($lastPrac != $stu->pracid) {
			$lastPrac = $stu->pracid;
			$pracCourse = $DB->get_record('course', array('id' => $stu->pracid), 'id,shortname');
			$pracDm = new block_dedication_manager($pracCourse, $mintime, $maxtime, $limit);
		}
		$prac_dedDetail = $pracDm->get_user_dedication($student, false);
		foreach ($prac_dedDetail AS $sesDetail) {
			$stu->prac_ded += $sesDetail->dedicationtime;
		}
		$stu->prac_ded /= 3600; //hours
		$stu->prac_avses = ($stu->prac_ded / count($prac_dedDetail) * 60); //mins
		//$stu->prac_ded = ($pracDm->get_user_dedication($student, true))/3600;//hours
	}
	
	//get site time
	//code ripped from dedication_lib.php with param:courseid removed
	/*$where = 'userid = :userid AND time >= :mintime AND time <= :maxtime';
	$selectparams = array(
		'userid' => $stu->id,
		'mintime' => $mintime,
		'maxtime' => $maxtime
	);
	$logs = $DB->get_records_select('log', $where, $selectparams, 'time ASC', 'id,time,ip');
	$sitededication[$stu->id] = 0;
	if ($logs) {
		$previouslog = array_shift($logs);
		$previouslogtime = $previouslog->time;
		$sessionstart = $previouslogtime;

		foreach ($logs as $log) {
			if (($log->time - $previouslogtime) > $limit) {
				$dedication = $previouslogtime - $sessionstart;
				$sitededication[$stu->id] += $dedication;
				$sessionstart = $log->time;
			}
			$previouslogtime = $log->time;
		}
		$dedication = $previouslogtime - $sessionstart;
		$sitededication[$stu->id] += $dedication; //seconds
	}//*/
	
	//build results array
	/*$rows[] = (object) array(
		'id' => $stu->id . "-1",
		'name' => $stu->firstname . " " . $stu->lastname,
		'usergroup' => "",
		'course' => "Online Campus",
		'category' => "Sitewide",
		'numstudents' => "",
		'dedication' => $sitededication[$stu->id] / (60*60), //hours
		'sentmessages' => $sentmessages[$stu->id],
	);//*/
	
	//get zoom time
	
	//build results array
	
}//*/

$headers = array((object) array_keys((array)reset($stus)));

$rows = array_merge($headers,$stus);

//print_r($rows);

if ($action == 'Download') {
	$filename = "Incoming Student Dedication Report $mindate to $maxdate";

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
	echo '<h1>Incoming Student Dedication Report</h1>';
	echo 'Limit: <select name="limit" form="instructorreport">';
	for ($i=1; $i<=150; $i++) {
		echo '<option value="'.$i*60;
		if ($i*60 == $limit) {
			echo '" selected="selected';
		}
		echo '">'.$i.' minutes</option>';
	}
	echo '</select>';//*/
	
	$page_name = 'isdr.php';
	
	echo '<form action="'.$page_name.'" method="get" id="instructorreport">'.
		'From: <input type="date" name="mindate" value="'.$mindate.'">'.' <input type="time" name="minclock" value="'.$minclock.'"></br>'.
		'To: &nbsp; &nbsp; <input type="date" name="maxdate" value="'.$maxdate.'">'.' <input type="time" name="maxclock" value="'.$maxclock.'"></br>'.
		'<b>Defaults (optional):</b> <br>'.
		'Orientation: <input type="text" name="defaultOri" value="'.$defaultOri.'"><br>'.' Core: &nbsp; &nbsp; <input type="text" name="defaultCore" value="'.$defaultCore.'"><br>'.
		'PracCA: <input type="text" name="defaultPracCA" value="'.$defaultPracCA.'"><br>'.' PracBK: <input type="text" name="defaultPracBK" value="'.$defaultPracBK.'"><br>'.
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