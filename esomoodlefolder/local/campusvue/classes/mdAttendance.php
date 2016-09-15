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
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendanceSession.php');

/**
 * 
 *
 * @package    local_campusvue
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mdAttendance {
	public $maxTime;
	public $minTime = 0;
	public $token = null;
	public $Attendance = array();
	
	public function __construct ($maxTime, $minTime = 0, $token = null) {
		$this->maxTime = $maxTime ? $maxTime : time();
		$this->minTime = $minTime;
		if (!$token) {
			$token = cvGetToken();
		}
		$this->token = $token;
		$this->Attendance = array();
		$sessionList = $this->getSessionList($this->maxTime, $this->minTime);
		foreach ($sessionList as $session) {
			if (!empty($session->id) && !empty($session->cvid) && !empty($session->sessdate)) { //can't do attendance without these
				$cvFlag = $this->checkCVFlag($session->description);
				$date = $this->zeroTime($this->cvFormatDate($session->sessdate));
				
				// cvFlag means attendance session was created by CampusVue, so we can use the session length in Moodle
				// otherwise, we need to get the session length stored in CampusVue with the API
				$sessionLength = $cvFlag ? $session->mins : $this->cvGetSessionLength($session->cvid, $date);
				
				//$session->description = $cvFlag;
				//$session->sessdate = $date;
				$this->Attendance[] = new mdAttendanceSession($session->id, $session->cvid, $date, $sessionLength, $this->token);
			}
		}
	}
	
	public function getSessionList($maxTime, $minTime) {
		global $DB;
		$catStr = $this->getCategoryClause();
		$sql = "SELECT sess.id, 
					CASE WHEN sess.groupid > 0 
						THEN (SELECT g.idnumber FROM {groups} g 
								WHERE g.id = sess.groupid ) 
						ELSE c.idnumber END AS cvid, 
					sess.sessdate, ROUND(sess.duration / 60, 0) AS mins, sess.description 
				FROM {attendance_sessions} sess 
					JOIN {attendance} a ON sess.attendanceid = a.id 
					JOIN {course} c ON a.course = c.id 
					JOIN {course_categories} cc ON c.category = cc.id 
				WHERE sess.sessdate >= $minTime AND sess.sessdate < $maxTime 
					$catStr ";
		$list = $DB->get_records_sql($sql);
		return $list;
	}
	
	//get category limit as WHERE clause
	public function getCategoryClause() {
		$catStr = "";
		$config = get_config('local_campusvue');
		if (!empty($config->manualcatlimit)) {
			$catlimit = explode(',',$config->manualcatlimit);
			$paths = count($catlimit);
			$catStr = "AND (cc.path LIKE '" . $catlimit[0] . "%'";
			for ($i = 1; $i < $paths; $i++) {
				$catStr = $catStr . " OR cc.path LIKE '" . $catlimit[$i] . "%'";
			}
			$catStr = $catStr . ")";
		}
		return $catStr;
	}
	
	/**
	 * helper function to authenticate date format '2016-07-13T00:00:00'
	 *
	 * @param mixed $dateString can be int or DateTime object
	 * @return string $dateString
	 */
	public function cvFormatDate($dateString) {
		//timestamp
		if (is_numeric($dateString)){
			if ($dateString < 15000000000 ) { //corresponds to 6/23/1970 in milliseconds, or 5/1/2445 in seconds
				//timestamp is in seconds
				return date('Y-m-d\TH:i:s', $dateString);
			} else {
				//timestamp is in milliseconds
				return date('Y-m-d\TH:i:s', $dateString/1000);
			}
		}
		//object
		/*if (gettype($dateString) == 'object') {
			if (get_class($dateString) == 'DateTime'){
				return $dateString->format('Y-m-d\TH:i:s');
			}
		}*/
		//string
		return $dateString;
	}

	/**
	 * zero out time in AttendanceDate
	 *
	 * @param string $dateString in format '2016-07-13T00:00:00'
	 * @return string $dateString
	 */
	public function zeroTime($dateString) {
		$dt = explode('T', $dateString);
		return $dt[0] . 'T00:00:00';
	}
	
	public function checkCVFlag($string) {
		//to add, after we figure out how we are going to flag things from CVue
		return false;
	}
	
	//get LengthMinutes based on ClassSchedId (CourseSectionId) and Date
	public function cvGetSessionLength($courseSectionId, $date) {
		global $CFG;
		require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
		$cem = new cvEntityMsg('ClassAttendance');
		$cem->addParam('ClassSchedId', $courseSectionId, 'Equal');
		$cem->addParam('Date', $date, 'Equal');
		return $cem->getEntityField('LengthMinutes', $this->token); //this is returning an empty array if no results are found 
	}
}