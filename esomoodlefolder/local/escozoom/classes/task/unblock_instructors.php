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
 * @package    local_escozoom
 * @copyright  2016 Triumph higher Education Group
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_escozoom\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/escozoom/lib.php');

/**
 * Scheduled task to remove instructors from any user's blocked list
 */
class unblock_instructors extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('unblockinstructors', 'local_escozoom');
    }

    /**
     * Remove any records where a user having first name beginning with 'Chef ' is blocked
     *
     * @return boolean
     */
    public function execute() {
        global $DB;
		
		$sql = "SELECT mc.id, mc.blocked, 
					CONCAT(u.firstname,' ',u.lastname) AS user, 
					CONCAT(c.firstname,' ',c.lastname) AS contact 
				FROM {message_contacts} mc 
					JOIN {user} c ON mc.contactid = c.id 
					JOIN {user} u ON mc.userid = u.id 
				WHERE mc.blocked = 1 
					AND c.firstname LIKE 'Chef %' ";
		$blocked_list = $DB->get_records_sql($sql);

        if (!isset($blocked_list)) { 
            return true;
        }

        foreach ($blocked_list AS $blocked ) {
			mtrace("... unblocking $blocked->contact as $blocked->user ...");
			$DB->delete_records('message_contacts', array('id'=>$blocked->id));
		}
		
		mtrace("... " . count($blocked_list) . " blocked users found");

        return true;
    }
}
