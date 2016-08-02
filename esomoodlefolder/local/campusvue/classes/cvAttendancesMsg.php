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
class cvAttendancesMsg {
	public $Attendances = array();
	
	public function __construct () {}
	
	public function addAttendance($StudentId, $CourseSectionId, $AttendanceDate, $MinutesAbsent = 0, $MinutesAttended = 0, 
										$UpdateExistingAttendance = false, $IsExcused = false, $IsDependentCourse = false) {
		$Attendance = new stdClass();
			$Attendance->StudentId = $StudentId;
			$Attendance->CourseSectionId = $CourseSectionId;
			$Attendance->AttendanceDate = cvFormatDate($AttendanceDate);
			$Attendance->MinutesAbsent = $MinutesAbsent;
			$Attendance->MinutesAttended = $MinutesAttended;
			$Attendance->UpdateExistingAttendance = $UpdateExistingAttendance;
			$Attendance->IsExcused = $IsExcused;
			$Attendance->IsDependentCourse = $IsDependentCourse;
		$this->Attendances[] = $Attendance;
	}
	
	public function addAttendanceZeroTime($StudentId, $CourseSectionId, $AttendanceDate, $MinutesAbsent = 0, $MinutesAttended = 0, 
										$UpdateExistingAttendance = false, $IsExcused = false, $IsDependentCourse = false) {
		$Attendance = new stdClass();
			$Attendance->StudentId = $StudentId;
			$Attendance->CourseSectionId = $CourseSectionId;
			$Attendance->AttendanceDate = zeroTime(cvFormatDate($AttendanceDate));
			$Attendance->MinutesAbsent = $MinutesAbsent;
			$Attendance->MinutesAttended = $MinutesAttended;
			$Attendance->UpdateExistingAttendance = $UpdateExistingAttendance;
			$Attendance->IsExcused = $IsExcused;
			$Attendance->IsDependentCourse = $IsDependentCourse;
		$this->Attendances[] = $Attendance;
	}
	
	/*/helper function to authenticate date format '2016-07-13T00:00:00'
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
		if (gettype($dateString) == 'object') {
			if (get_class($dateString) == 'DateTime'){
				return $dateString->format('Y-m-d\TH:i:s');
			}
		}
		//string
		return $dateString;
	}
	
	//zero out time in AttendanceDate
	public function zeroTime($dateString) {
		$dt = explode('T', $dateString);
		return $dt[0] . 'T00:00:00';
	}/**/
	
	public function incrementDay($dateString, $days = 1) {
		$i = $days >= 0 ? '+'.$days : $days;
		$dt = new DateTime($dateString);
		$dt->modify("$i day");
		return $dt->format('Y-m-d\TH:i:s');
	}
	
	public function postAttendanceTransaction($token = null, $client = null, $batch = false) {
		if ($token == null) {
			$token = cvGetToken();
		}
		if ($client == null) {
			$client = cvBuildClient('cmc.campuslink.webservices','AttendanceWebService.asmx');
		}
		$call = $batch ? 'PostAttendanceTransactionBatch' : 'PostAttendanceTransaction';
		
		$args = array('PostAttendanceTransactionRequest' => array('TokenId' => $token,
																'Attendances' => $this->Attendances) );
		$result = $client->__soapCall($call, array($args));
		if (!isset($result->PostAttendanceTransactionResponse)){
			//add error handling
		}
		return $result->PostAttendanceTransactionResponse;
	}
}