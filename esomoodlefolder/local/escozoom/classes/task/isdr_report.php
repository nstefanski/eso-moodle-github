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
 * @package    local_escozoom
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_escozoom\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/dedication/dedication_lib.php');
require_once($CFG->dirroot.'/local/escozoom/locallib.php');

/**
 * Scheduled task to sychronize meeting data.
 *
 * @package   local_escozoom
 * @copyright 
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class isdr_report extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        //return get_string('deleterecordings', 'mod_zoom');
		return 'ISDR Report';
    }

    /**
     * Runs ISDR and saves to directory
     *
     * @return boolean
     */
    public function execute() {
		$mindate = date_format_string(time()-(8*7*24*60*60), '%Y-%m-%d');
		list($y, $m, $d) = explode("-", $mindate);
		$mintime = make_timestamp($y, $m, $d, 0, 5, 0);
		$maxdate = date_format_string(time()+(24*60*60), '%Y-%m-%d');
		list($y, $m, $d) = explode("-", $maxdate);
		$maxtime = make_timestamp($y, $m, $d, 0, 5, 0);
		
		mtrace("... running ISDR from period $mindate to $maxdate ");
		
		$stus = get_users_isdr($mintime, $maxtime);
		$headers = array((object) array_keys((array)reset($stus)));
		$rows = array_merge(array(array("$mindate to $maxdate")),$headers,$stus);
		$csvpath = write_csv($rows, 'isdr');
		
		mtrace("... saved " . count($rows) . " rows to $csvpath ");
		
        return true;
    }
}
