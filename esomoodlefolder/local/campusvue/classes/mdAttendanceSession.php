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
class mdAttendanceSession {
	public $mdSessionId = 0;
	public $CourseSectionId = 0;
	public $AttendanceDate = '';
	public $SessionLength = 0;
	public $token = null;
	public $Attendances = array();
	
	public function __construct ($mdSessionId = 0, $CourseSectionId = 0, $AttendanceDate = '', $SessionLength = 0, $token = null) {
		$this->mdSessionId = $mdSessionId;
		$this->CourseSectionId = $CourseSectionId;
		$this->AttendanceDate = $AttendanceDate; //find a way to kill class if these three aren't set?
		$this->SessionLength = $SessionLength;
		$this->token = $token;
		
		$this->Attendances = array();
		$mdLogs = $this->mdGetAttendanceLogs();
		foreach ($mdLogs as $log) {
			if (empty($log->cvid)) {
				if (!$this->token) { $this->token = cvGetToken(); } //only case where token is needed
				$log->cvid = $this->cvGetSyStudentId($log->idnumber);
			}
			$absent = 0;
			$excused = false;
			//only numeric remarks
			if ($log->remarks) {
				$log->remarks = preg_replace('/[^0-9]/','',$log->remarks);
			}
			//switch to set absent time based on status and remarks
			switch ($log->status) {
				case 'Present':
					break;
				case 'Excused':
					//$excused = true;
					//no break -- treat excused as absent but add flag
				case 'Absent':
					$absent = $this->SessionLength;
					break;
				case 'Late':
					$absent = $log->remarks <= $this->SessionLength ? $log->remarks : $this->SessionLength;
					break;
				default:
					$log = null; //make no record in attendances for unknown statuses
			}
			if ($log->cvid) {
				$this->Attendances[] = (object) array('StudentId' => $log->cvid, 'MinutesAbsent' => $absent, 'Excused' => $excused
														//, 'Fullname' => $log->fullname // debugging // 
														);
			}
		}
	}
	
	public function mdGetAttendanceLogs() {
		global $DB;
		$sql = "SELECT al.id, u.idnumber, cvid.data AS cvid, 
					stat.description AS status, al.remarks 
					/*, CONCAT(u.firstname,' ',u.lastname) AS fullname /* debugging */
				FROM {attendance_log} al 
				JOIN {user} u ON al.studentid = u.id 
				LEFT JOIN {user_info_data} cvid ON u.id = cvid.userid 
					AND (SELECT field.shortname FROM {user_info_field} field 
						WHERE field.id = cvid.fieldid ) LIKE 'cvueid' 
				JOIN {attendance_statuses} stat ON al.statusid = stat.id 
				WHERE al.sessionid = :sessid ";
		$logs = $DB->get_records_sql($sql, array('sessid' => $this->mdSessionId));
		return $logs;
	}
	
	//get SyStudentId based on StudentNumber
	public function cvGetSyStudentId($StudentNumber) {
		if (empty($StudentNumber)) { return null; }
		global $CFG;
		require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
		$cem = new cvEntityMsg('Student');
		$cem->addParam('StudentNumber', $StudentNumber, 'Equal');
		return $cem->getEntityField('Id', $this->token);
	}
}