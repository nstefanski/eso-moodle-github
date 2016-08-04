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
require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendanceSession.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');

//get yesterday and today (midnight)
$dt = new DateTime();
$i = 1;
$maxTime = $dt->setTime(0, 0)->getTimestamp();
$minTime = $dt->modify("-$i day")->getTimestamp();
//echo "$maxTime<br/>$minTime<br/>";

//get category limit as WHERE clause
$config = get_config('local_campusvue');
if (!empty($config->manualcatlimit)) {
	$catlimit = explode(',',$config->manualcatlimit);
	$paths = count($catlimit);
	$catStr = "AND (cc.path LIKE '" . $catlimit[0] . "%'";
	for ($i = 1; $i < $paths; $i++) {
		$catStr = $catStr . " OR cc.path LIKE '" . $catlimit[$i] . "%'";
	}
	$catStr = $catStr . ")";
}
//echo "$catStr <br/>";

$sql = "SELECT sess.id, 
			CASE WHEN sess.groupid > 0 
				THEN (SELECT g.idnumber FROM {groups} g 
						WHERE g.id = sess.groupid ) 
				ELSE c.idnumber END AS cvcoursesectionid, 
			sess.sessdate, sess.duration, sess.description 
		FROM {attendance_sessions} sess 
			JOIN {attendance} a ON sess.attendanceid = a.id 
			JOIN {course} c ON a.course = c.id 
			JOIN {course_categories} cc ON c.category = cc.id 
		WHERE sess.sessdate >= $minTime AND sess.sessdate < $maxTime 
			$catStr ";
echo "$sql <br/>";
$records = $DB->get_records_sql($sql);
print_R($records);
echo '<hr/>';

//$att = new mdAttendance($maxTime, $minTime);
//print_R($att);
$i = 1;
$limits = cvGetTimeLimits($i);
print_R($limits);
echo '<br/><br/>';

echo 'day: ' . date("j") . ' month: ' . date("n") . ' year: ' . date("Y");
echo '<br/>minTime: ' . mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
echo '<br/>maxTime: ' . mktime(0, 0, 0, date("m")  , date("d"), date("Y"));