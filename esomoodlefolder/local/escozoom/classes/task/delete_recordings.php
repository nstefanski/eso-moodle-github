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
 * Library of interface functions and constants for module zoom
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the zoom specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    local_escozoom
 * @copyright  2015 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_escozoom\task;

defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/mod/zoom/locallib.php');
require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->dirroot.'/local/escozoom/escoffierlib.php');

/**
 * Scheduled task to sychronize meeting data.
 *
 * @package   mod_zoom
 * @copyright 2015 UC Regents
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_recordings extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        //return get_string('deleterecordings', 'mod_zoom');
		return 'Delete Escoffier zoom meeting recordings';
    }

    /**
     * Deletes recordings older than 3 hrs from nick's account
     *
     * @return boolean
     */
    public function execute() {
		$apiCalls = 0;
		$bytesKept = 0;
		$bytesDeleted = 0;
		
		//get all users
		$response = zoom_get_all_users();
		$apiCalls = $response['api_calls'];
		$users = $response['users']; //array
		
		foreach ($users as $user) {
			//set deletion threshold
			switch ($user->email){
				//set separate case for each exception to the rule
				case "cafe@escoffier.edu":
					$daysOld = 11;
					break;
				case "ambassadors@escoffier.edu":
					$daysOld = 11;
					break;
				case "studio@escoffier.edu":
					$daysOld = 11;
					break;
				//rule is -- delete after 48 hours
				default:
					$daysOld = 2;
			}
			
			//get size and count of files within threshold
			$kept = zoom_count_user_recordings($user->id, $daysOld); //array
			$apiCalls = $apiCalls + $kept['api_calls'];
			$bytesKept = $bytesKept + $kept['bytes'];
			
			//delete files beyond threshold
			$deleted = zoom_delete_user_recordings($user->id, $daysOld);
			$apiCalls = $apiCalls + $deleted['api_calls'];
			$bytesDeleted = $bytesDeleted + $deleted['bytes'];
		}
		mtrace("...kept " . round($bytesKept / 1024 / 1024 / 1024, 2) . " GB of data");
		mtrace("...deleted " . round($bytesDeleted / 1024 / 1024 / 1024, 2) . " GB of data");
		
		//send debug message to user: Nick
		$sendmessage = zoom_send_debug_message($apiCalls, $bytesKept, $bytesDeleted);
		if ($sendmessage) {
			mtrace("...debug message sent");
		} else {
			mtrace("...debug message failed");
		}
		
		mtrace("... used $apiCalls api calls");

        return true;
    }
}
