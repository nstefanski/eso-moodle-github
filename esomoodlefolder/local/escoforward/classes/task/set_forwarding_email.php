<?php
// This file is part of the Escoffier Forwarding plugin for Moodle - http://moodle.org/
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
 * @package    local_escoforward
 * @copyright  2016 Triumph higher Education Group
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_escoforward\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/escoforward/locallib.php');

/**
 * Scheduled task to update forwarding email addresses
 *
 * @package   local_escoforward
 * @copyright 2016 Triumph Higher Education Group
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_forwarding_email extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('setforwardingemail', 'local_escoforward');
    }

    /**
     * Updates forwarding emails if custom field is not blank and has not been set to a placeholder.
     *
     * @return boolean
     */
    public function execute() {
        global $DB;
		
		$prefName = 'message_processor_email_email';
		
		$fieldShortname = 'escoforwardemail';
		$fieldId = $DB->get_record('user_info_field',array('shortname'=>$fieldShortname),'id')->id;
		
		$setting = null; //TODO: grab a setting for whether there should be a timelimit
		
		if ($setting) {
			$timelimit = "AND u.timecreated > UNIX_TIMESTAMP() - $setting ";
			$timelimit = "AND u.timemodified > UNIX_TIMESTAMP() - $setting ";
		} else {
			$timelimit = "";
		}
		
		$sql = "SELECT uid.id, uid.userid, /*CONCAT(u.firstname,' ',u.lastname), u.email, 
					FROM_UNIXTIME(u.timecreated), FROM_UNIXTIME(u.timemodified), */
					uid.data, up.value
				FROM {user_info_data} uid 
					JOIN {user} u ON uid.userid = u.id 
					LEFT JOIN {user_preferences} up 
						ON up.userid = u.id AND up.name = '$prefName' 
				WHERE uid.fieldid = $fieldId 
					AND uid.data <> '' 
					AND u.suspended = 0 AND u.deleted = 0 
					$timelimit 
					AND (up.value = '' OR up.value IS NULL) /* */";
		mtrace($sql);
		$users = $DB->get_records_sql($sql);

        // Check all meetings, in case they were deleted/changed on Zoom.
        /*$zooms = $DB->get_recordset_select('zoom', 'status <> ?', array(ZOOM_MEETING_EXPIRED));//*/

        if (!isset($users)) {
			mtrace("... ... До свидания мир!");
            return true;
        }

        esco_update_forwarding($users);
        //$users->close();//*/	
		
		mtrace("... ... Привет мир!");
		mtrace(count($users) . " users found");

        return true;
    }
}
