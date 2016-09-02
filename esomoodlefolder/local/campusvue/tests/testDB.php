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
//require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
/*require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendanceSession.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');*/

echo 'привет мир!<br/>';

//$record = $DB->get_records('assign');
//print_R($record);

//echo '<br/>привет мир!';

$sql = "SELECT * 
		FROM {tool_customlan} tcl 
		WHERE tcl.stringid LIKE 'remarks' 
			AND tcl.lang = 'en'
			AND tcl.componentid = (SELECT tclc.id 
				FROM {tool_customlang_components} tclc 
				WHERE tclc.name LIKE 'mod_attendance') ";

try {				
	$record = $DB->get_record_sql($sql);
} catch (Exception $e) {
    print_R($e);
}

print_R($record);

if($record){
	echo '<br/>bam!';
}