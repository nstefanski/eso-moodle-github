<?php
// This file is part of Moodle - http://moodle.org/
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
 * Definition of scheduled tasks.
 *
 * @package    local_campusvue
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_campusvue\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/campusvue/lib.php');

class update_cv_attendances extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('update_cv_attendances', 'local_campusvue');
    }
	
	public function execute() {
		$i = 1;//1
		$j = 0;//0
		$minTime = mktime(0, 0, 0, date("m"), date("d")-$i, date("Y"));
		$maxTime = mktime(0, 0, 0, date("m"), date("d")-$j, date("Y"));
		mtrace("... running attendance for period $minTime to $maxTime");
		
		$token = cvGetToken();
		//mtrace($token);
		
		$ua = updateAttendance($maxTime, $minTime, $token);
		if ($ua) {
			if ($ua->Attendances) {
				$msgArray = $ua->Attendances->PostAttendanceOutMsg;
				$msgs = count($msgArray);
				$errs = 0;
				$exErr = 0;
				$vaErr = 0;
				foreach ($msgArray as $outMsg) {
					if ($outMsg->MessageStatus == 'FailedExecution') {
						$errs++;
						$exErr++;
					} elseif ($outMsg->MessageStatus == 'FailedValidation') {
						$errs++;
						$vaErr++;
					} elseif (!empty($outMsg->MessageErrorCode)) {
						$errs++;
					}
				}
				mtrace("... sent $msgs Attendance Messages with $errs errors ");
				if ($errs) {
					mtrace("... $exErr Failed Execution ");
					mtrace("... $vaErr Failed Validation ");
					$otErr = $errs - $exErr - $vaErr;
					mtrace("... $otErr Unknown Errors ");
				}
			} else {
				mtrace("... no Attendance Messages to send ");
			}
		} else {
			mtrace("... could not send Attendance Messages ");
		}
		
        return true;
	}

}