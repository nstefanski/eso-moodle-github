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
 * 
 *
 * @package   local_campusvue
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/campusvue/lib.php');

//scheduled task will get all attendance sessions within range, with course.id and course.idnumber (cvid)
//for each SESSION with a session in period, make a new mdAttendance
//mdAttendance grabs all the user attendance logs for the period and constructs array of relevant info:
//	studentid (md and cv), minutes late, excused or other flags

/**
 * 
 *
 * @package    local_campusvue
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mdAttendance {
	public $mdSessionId = 0;
	public $mdCourseId = 0;
	public $cvCourseId = 0;
	public $token = null;
	public $Attednances = array();
	
	public function __construct ($mdSessionId = 0, $mdCourseId = 0, $cvCourseId = 0, $token = null) {
		global $DB;
		if ($mdSessionId == 0) {
			//must have a course id, assume one session per course
			
		} else {
			$mdCourseId = $mdCourseId ? $mdCourseId : $DB->get_record_sql();
			$cvCourseId = $cvCourseId ? $cvCourseId : $DB->get_record_sql();
		}
	}
	
	public function mdGetAttendanceLogs () {
	
	}
}