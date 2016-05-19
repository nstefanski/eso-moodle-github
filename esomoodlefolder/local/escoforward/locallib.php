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
 * Internal library of functions for module zoom
 *
 * All the zoom specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    local_escoforward
 * @copyright  2016 Triumph Higher Education Group
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/escoforward/lib.php');

function esco_update_forwarding($users) {
    global $DB;
	
	$table = 'user_preferences';
	$prefName = 'message_processor_email_email';
	
	$success = 0;
	$error = 0;
	
	foreach ($users as $u) {		
		$dataobject = new stdclass;
		
		if($u->prefid) {
			$dataobject->id = $u->prefid;
			
			$action = 'update';
		} else {
			$dataobject->userid = $u->userid;
			$dataobject->name = $prefName;
			
			$action = 'insert';
		}
		
		$dataobject->value = $u->data;
		
		if ($action === 'update'){
			try {
				if($DB->update_record($table, $dataobject)) {
					$success++;
				}
			} catch (moodle_exception $e) {
				$error++;
			}
		} elseif ($action === 'insert') {
			try {
				if($DB->insert_record($table, $dataobject)) {
					$success++;
				}
			} catch (moodle_exception $e) {
				$error++;
			}
		}
		
		//clear or replace data in custom field
		//this lets user eliminate forwarding if they want to use default email
		$cleardata = str_replace('.', ' dot ', str_replace('@', ' at ', $u->data)); //''; //
		$clearobject = new stdclass;
		$clearobject->id = $u->id;
		$clearobject->data = $cleardata;
		try {
			$DB->update_record('user_info_data', $clearobject);
		} catch (moodle_exception $e) {
			
		}
	}
	
	mtrace("... $success users updated");
	mtrace("... $error errors");
}