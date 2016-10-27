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

/**
 * 
 *
 * @package    local_campusvue
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mdWeekComp {
	public $mdSectionId = 0;
	public $CourseSectionId = 0;
	public $AttendanceDate = '';
	public $SessionLength = 0;
	public $activities = 0;
	public $token = null;
	public $Attendances = array();
	
	public function __construct ($cg = 0, $mdSectionId = 0, $CourseSectionId = 0, $AttendanceDate = '', $SessionLength = 0, $activities = 0, $token = null) {
		$this->mdSectionId = $mdSectionId;
		$this->CourseSectionId = $CourseSectionId;
		$this->AttendanceDate = $AttendanceDate; //find a way to kill class if these three aren't set?
		$this->SessionLength = $SessionLength;
		$this->activities = $activities;
		$this->token = $token;
		
		list($c, $g) = explode("-", $cg);
		
		$this->Attendances = array();
		$mdLogs = $this->mdGetWeekComp($c, $g);
		foreach ($mdLogs as $log) {
			if (empty($log->cvid)) {
				if (!$this->token) { $this->token = cvGetToken(); } //only case where token is needed
				$log->cvid = cvGetSyStudentId($log->idnumber, $this->token);
			}
			$absent = max((1 - ($log->weekcomp + $log->livesess)/$activities) * $SessionLength, 0);
			$excused = false;
			
			if ($log->cvid) {
				$this->Attendances[] = (object) array('StudentId' => $log->cvid, 'MinutesAbsent' => $absent, 'Excused' => $excused
														//, 'Fullname' => $log->fullname // debugging // 
														);
			}
		}
	}
	
	public function mdGetWeekComp($c, $g) {
		global $DB;
		$zoommod = $DB->get_record('modules', array('name'=>'zoom'))->id;
		$secid = $this->mdSectionId;
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
					/*, CONCAT(u.firstname,' ',u.lastname) AS fullname /* debugging */
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
		$logs = $DB->get_records_sql($sql);
		return $logs;
	}
}