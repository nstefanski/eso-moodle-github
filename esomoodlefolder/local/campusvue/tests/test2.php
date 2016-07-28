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

//get token
echo 'cvGetToken: ' . substr(cvGetToken(), 0, 20) . ' ...<hr>';

//get searchable attributes
$entityType = 'CampusGroup';
echo "GetSearchableAttributes for $entityType : ";
$cem = new cvEntityMsg($entityType);
print_R($cem->getSearchableAttributes() );
echo '<hr>';

/*/
$entityType = 'CourseSection';
$cem = new cvEntityMsg($entityType);
$fieldName = 'StartDate';
$fieldValue = '2016-07-06T00:00:00';
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
print_R($cem->getEntity());
echo '<hr>';//*/

//get LengthMinutes based on ClassSchedId and Date
$entityType = 'ClassAttendance';
$cem = new cvEntityMsg($entityType);
$fieldName = 'ClassSchedId';
$fieldValue = 3653;
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
$fieldName = 'Date';
$fieldValue = '2016-07-13T00:00:00';
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
	//$result = $cem->getEntity();
	//print_R($result);
	//echo '<hr>';
$getField = 'LengthMinutes';
print_R($cem->getEntityField($getField));
echo '<hr>';

//get SyStudentId based on StudentNumber
$entityType = 'Student';
$fieldName = 'StudentNumber';
$fieldValue = 1607222765;
$paramOperator = 'Equal';
$getField = 'Id';
$cem = new cvEntityMsg($entityType);
$cem->addParam($fieldName, $fieldValue, $paramOperator);
print_R($cem->getEntityField($getField));
echo '<hr>';

$sql = "SELECT * 
		FROM {attendance_log} al 
		WHERE al.remarks REGEXP '^[0-9]+$' ";
$records = $DB->get_records_sql($sql);
print_R($records);