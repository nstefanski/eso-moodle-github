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
//require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');

$token = cvGetToken();
print_R($token);
echo '<hr>';

/*$att = new mdAttendance(1, $minTime, $token, 'weekcomp');
print_R($att);
echo '<hr>';*/

$courseSectionId = (empty($_GET['id'])) ? '' : $_GET['id'];
$date = (empty($_GET['date'])) ? '' : $_GET['date'];

print_R($date);
echo '<hr>';

if($courseSectionId && $date){
	require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
	$cem = new cvEntityMsg('ClassAttendance');
	$cem->addParam('ClassSchedId', $courseSectionId, 'Equal');
	$cem->addParam('Date', $date, 'Equal');
	$len = $cem->getEntityField('LengthMinutes', $token); //this is returning an empty array if no results are found 
	print_R($len);
}
