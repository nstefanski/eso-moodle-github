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
	//public $cvFlag = false;
	//public $token = null;
	public $Attendances = array();
	
	public function __construct ($mdSessionId = 0, $CourseSectionId = 0, $AttendanceDate = '', $SessionLength = 0, $cvFlag = false, $token = null) {
		$this->mdSessionId = $mdSessionId;
		$this->CourseSectionId = $CourseSectionId;
		$this->AttendanceDate = $AttendanceDate;
		if (!$cvFlag && !$token) {
			$token = cvGetToken();
		}
		$this->SessionLength = $cvFlag ? $SessionLength : $this->cvGetSessionLength();
		$this->Attendances = array();
		$mdLogs = $this->mdGetAttendanceLogs();
		foreach ($mdLogs as $log) {
			if (empty($log->cvid)) {
				//add another token check?
				$log->cvid = $this->cvGetSyStudentId($log->idnumber);
			}
			$absent = 0;
			//switch to set absent time based on status and remarks
			$this->Attendances[] = (object) array('StudentId' => $log->cvid, 'MinutesAbsent' => $absent);
		}
	}
	
	public function mdGetAttendanceLogs() {
		global $DB;
		$sql = "SELECT al.id, u.idnumber, cvid.data AS cvid, 
					stat.description AS status, al.remarks 
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
	
	//get LengthMinutes based on ClassSchedId (CourseSectionId) and Date
	public function cvGetSessionLength() {
		include $CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php';
		$cem = new cvEntityMsg('ClassAttendance');
		$cem->addParam('ClassSchedId', $this->CourseSectionId, 'Equal');
		$cem->addParam('Date', $this->AttendanceDate, 'Equal');
		return $cem->getEntityField('LengthMinutes', $this->token);
	}
	
	//get SyStudentId based on StudentNumber
	public function cvGetSyStudentId($StudentNumber) {
		include $CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php';
		$cem = new cvEntityMsg('Student');
		$cem->addParam('StudentNumber', $StudentNumber, 'Equal');
		return $cem->getEntityField('Id', $this->token);
	}
}