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
/*require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendanceSession.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');*/

$i = 1;

$minTime = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
$maxTime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
$token = cvGetToken();

$ua = updateAttendance($maxTime, $minTime, $token);
if ($ua) {
	$msgArray = $ua->Attendances->PostAttendanceOutMsg;
	$msgs = count($msgArray);
	$errs = 0;
	foreach ($msgArray as $outMsg) {
		if ($outMsg->MessageStatus == 'FailedExecution') {
			$errs++;
		}
	}
	echo "Sent $msgs Attendance Messages with $errs errors.";
}
echo '<hr/>';
print_R($ua);
//echo '<hr/>';

/*$cem = new cvEntityMsg('ClassAttendance');
$cem->addParam('ClassSchedId', 3653, 'Equal');
$cem->addParam('Date', '2016-08-03T00:00:00', 'Equal');
print_R($cem);
echo '<hr/>';
try {
	$msg = $cem->getEntity($token);
	print_R($msg);
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
}
echo '<hr/>';
print_R($cem->getEntityField('LengthMinutes', $token));
echo '<hr/>';*/
//this is returning an empty array if no results are found