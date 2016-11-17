<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests
 */

//defined('MOODLE_INTERNAL') || die();
require_once('../../../config.php');

global $CFG, $DB;
require_once($CFG->dirroot.'/local/campusvue/lib.php');

echo "test 6";

$maxTime = 1478062800;
$minTime = 1477890000;
$token = null;
$method = 'weekcomp';

//$ua = updateAttendance($maxTime, $minTime, null, 'weekcomp');

	require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');
	require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
	
	echo "<hr/>";
	
	require_once($CFG->dirroot.'/local/campusvue/classes/mdWeekComp.php');
	
	echo "<hr/>";
	
	$wc = new mdWeekComp('1078-600', 9402, 3328, '2016-10-25T00:00:00', 445, 4, null);
	print_R($wc);
	
	echo "<hr/>";
	
	$att = new mdAttendance($maxTime, $minTime, $token, $method);
	print_R($att);
	
	///////
	
		/*$courseSectionId = 3328;
		$date = "2016-10-25T00:00:00";
	
		require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
		$cem = new cvEntityMsg('ClassAttendance');
		$cem->addParam('ClassSchedId', $courseSectionId, 'Equal');
		$cem->addParam('Date', $date, 'Equal');
		echo "<hr/>";
		print_R($cem);
		$lm = $cem->getEntityField('LengthMinutes', $this->token); //this is returning an empty array if no results are found 
		print_R($lm);*/
		
		//$wc = new mdWeekComp('1078-600', 9402, 3328, '2016-10-25T00:00:00', 445, 4, $att->token);
		//print_R($wc);
		
		/*$cg = '1078-600';
		list($c, $g) = explode("-", $cg);
		
		$zoommod = $DB->get_record('modules', array('name'=>'zoom'))->id;
		echo "<br/> $zoommod <br/>";
		$secid = 9402;//$this->mdSectionId;
		$sql = "SELECT u.idnumber, cvid.data AS cvid, 
					(SELECT COUNT(*) FROM {course_modules_completion} cmc 
						JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id 
						WHERE cmc.userid = u.id AND cmc.completionstate > 0 
							AND cm.module <> $zoommod AND cm.idnumber NOT LIKE 'archive%' 
							AND cm.section = $secid ) AS weekcomp, 
					CASE WHEN (SELECT COUNT(*) FROM {course_modules_completion} cmc 
						JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id 
						WHERE cmc.userid = u.id AND cmc.completionstate > 0 
							AND (cm.module = $zoommod OR cm.idnumber LIKE 'archive%' )
							AND cm.section = $secid ) THEN 1 ELSE 0 END AS livesess 
					/*, CONCAT(u.firstname,' ',u.lastname) AS fullname /* debugging 
				FROM {role_assignments} ra 
					JOIN {context} cx ON ra.contextid = cx.id AND cx.contextlevel = 50 
						JOIN {course} c ON cx.instanceid = c.id 
					JOIN {user} u ON ra.userid = u.id 
						LEFT JOIN {user_info_data} cvid ON u.id = cvid.userid 
							AND (SELECT field.shortname FROM {user_info_field} field 
								WHERE field.id = cvid.fieldid ) LIKE 'cvueid' 
				WHERE ra. roleid = 5 AND c.id = $c ";
		if($g > 0){
			$sql .= "AND (SELECT gm.id FROM {groups_members} gm 
						WHERE gm.userid = u.id AND gm.groupid = $g ) IS NOT NULL  ";
		}
		$mdLogs = $DB->get_records_sql($sql);
		print_R( $mdLogs );
		echo "<hr/>";
		
		$activities = 4;
		$SessionLength = 445;
		$Attendances = array();
		
		foreach ($mdLogs as $log) {
			if (empty($log->cvid)) {
				if (!$att->token) { $att->token = cvGetToken(); } //only case where token is needed
				$log->cvid = cvGetSyStudentId($log->idnumber, $att->token);
				echo "<br/>";
				print_R( $log );
			}
			$absent = max((1 - ($log->weekcomp + $log->livesess)/$activities) * $SessionLength, 0);
			echo "<br/>";
			//print_R( $absent );
			//$excused = false;
			
			if ($log->cvid) {
				$Attendances[] = (object) array('StudentId' => $log->cvid, 'MinutesAbsent' => $absent, 'Excused' => $excused
														//, 'Fullname' => $log->fullname // debugging // 
														);
			}
		}
		print_R($Attendances);*/
	
	///////
	
	echo "whoosh";
	
	$msg = new cvAttendancesMsg();
	
	foreach($att->Attendance as $sess) {
		foreach($sess->Attendances as $attendance)
			$msg->addAttendance($attendance->StudentId, $sess->CourseSectionId, $sess->AttendanceDate, $attendance->MinutesAbsent, 0, false, $attendance->Excused);
	}
	
	echo "<hr/>";
	
	if ($msg->Attendances) {
		try {
			$result = $msg->postAttendanceTransaction($token);
		} catch (moodle_exception $e) {
			echo "ERROR";
		}
	} else {
		$result = $msg;
	}
	
	print_R($result);

//print_R($ua);