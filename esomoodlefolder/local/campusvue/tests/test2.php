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
require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');

//get token
echo 'cvGetToken: ' . substr(cvGetToken(), 0, 20) . ' ...<hr>';

//get searchable attributes
$entityType = 'ClassAttendance';
echo "GetSearchableAttributes for $entityType : ";
$cem = new cvEntityMsg($entityType);
print_R($cem->getSearchableAttributes() );
echo '<hr>';

//get LengthMinutes based on ClassSchedId and Date
$cem = new cvEntityMsg($entityType);
$fieldName = 'ClassSchedId';
$fieldValue = 3653;
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
$fieldName = 'Date';
$fieldValue = '2016-07-13T00:00:00';
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
$result = $cem->getEntity();
	//print_R($result);
	//echo '<hr>';
print_R($cem->getEntityField('LengthMinutes'));