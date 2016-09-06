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

//require_once($CFG->dirroot.'/mod/zoom/locallib.php');
//require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->dirroot.'/local/escozoom/escoffierlib.php');

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
		
		mtrace("... ");
		
        return true;
    }
}
