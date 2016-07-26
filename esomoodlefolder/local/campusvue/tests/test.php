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

global $CFG;
require_once($CFG->dirroot.'/local/campusvue/lib.php');
require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
//require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendanceMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');

echo 'cvGetToken: ' . substr(cvGetToken(), 0, 20) . ' ...<hr>';

$entityType = 'ClassAttendance';
echo "GetSearchableAttributes for $entityType : ";
$cem = new cvEntityMsg($entityType);
print_R($cem->getSearchableAttributes() );
echo '<hr>';

$cem = new cvEntityMsg($entityType);
$fieldName = 'ClassSchedId';
$fieldValue = 3653;
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
/*$fieldName = 'Date';
$fieldValue = '2016-07-13T00:00:00';
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);*/
print_R($cem->getEntity() );
//echo 'cvEntityMsg: ' . $cem->getEntityField('LengthMinutes') . '<hr>';

/*$studentid = 206552;
$csid = 3653;
$attdate = '2016-07-13T00:00:00';
$minsAbsent = 13;
echo 'cvAttendanceMsg (string): ';
$cam = new cvAttendanceMsg($studentid, $csid, $attdate, $minsAbsent);
print_R($cam);

$attdate = 1469527200;
echo '<br><br>cvAttendanceMsg (unix timestamp): ';
$cam = new cvAttendanceMsg($studentid, $csid, $attdate, $minsAbsent);
print_R($cam);
echo '<br><br>zeroTime(): ';
$cam->zeroTime();
print_R($cam);
echo '<br><br>incrementDay(): ';
$cam->incrementDay();
print_R($cam);

$attdate = 1470632700000;
echo '<br><br>cvAttendanceMsg (unix ms timestamp): ';
$cam = new cvAttendanceMsg($studentid, $csid, $attdate, $minsAbsent);
print_R($cam);

$attdate = new DateTime('2016-07-13 00:00:00');
echo '<br><br>cvAttendanceMsg (php datetime): ';
$cam = new cvAttendanceMsg($studentid, $csid, $attdate, $minsAbsent);
print_R($cam);
echo '<hr>';*/

$cam = new cvAttendancesMsg();
$sid = 206552;
$csid = 3653;
$attdates = array('2016-07-12T00:00:00',1468386000,'2016-07-14T00:00:00',new DateTime('2016-07-15 00:00:00'));
$minsAbsent = array(12,13,14,15);
foreach ($attdates as $key => $attdate) {
	$cam->addAttendanceZeroTime($sid, $csid, $attdate, $minsAbsent[$key], 0, true);
}
echo 'cvAttendancesMsg: ';
print_R($cam);
echo '<br><br>';
//print_R($cam->postAttendanceTransaction(null,null,true));
echo '<hr>';