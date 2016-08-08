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
 * campusvue local plugin install functions
 *
 * @package   local_campusvue
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_campusvue_install() {

	$catid = 1; //default 'Other' profile category
	// insert new profile category
	// $category = new stdClass();
	// $category->name = get_string('profilecategoryname','local_campusvue');
	// $category->sortorder = 1;
	// $catid = $DB->insert_record('user_info_category',$category);
	
	// Initial insert of new profile field
	$field = new stdClass();
	
	// default settings; hide field for all but user.
	$field->descriptionformat = 1;
	$field->categoryid = $catid;
	$field->required = 0;
	$field->locked = 0;
	$field->visible = 0;	// admin only; i.e. not visible on profile (by user nor others)
	$field->forceunique = 1;
	$field->signup = 0;
	$field->defaultdataformat = 0;
	$field->param1 = 8;
	$field->param2 = 7; //6 should be max for SyStudentId
	// $field->defaultdata = '';
	// $field->param3 = 0;
	// $field->param4 = 
	// $field->param5 =

	$field->shortname = 'cvueid';	// NOTE: moodle does not allow - or _ in this field!
	$field->name = get_string('profilefield_name','local_campusvue');
	$field->datatype = 'text';
	$field->description = get_string('profilefield_desc','local_campusvue');
	$field->sortorder = 1;
	addField($field);
	
	//change language customization of Attendance Remarks
	/*$sql = "SELECT * 
			FROM prefix_tool_customlang tcl 
			WHERE tcl.componentid = (SELECT id 
							FROM prefix_tool_customlang_components tclc 
							WHERE tclc.name LIKE 'mod_attendance') 
				AND tcl.stringid LIKE 'remarks' 
				AND tcl.lang = 'en' ";
	$record = $DB->get_record_sql($sql);
	
	$now = time();
	$customization = 'Minutes Missed <br />
						<span style="font-size: 11px; line-height: 12px;">(enter in numeric format, ie "15", not "fifteen")</span>
						<script src="/local/campusvue/js/cvConvertInputs.min.js" type="text/javascript"></script>
						<script name="Remarks" type="text/javascript">
							cvConvertInputs();
						</script>';
	
	$dataobject = new stdClass();
	$dataobject->id = $record->id;
	$dataobject->local = $customization;
	$dataobject->timecustomized = $now;
	
	//$DB->update_record('tool_customlang', $dataobject);*/

	
	return true;
}

/**
 * addField() - add a new custom profile field to the table
 * 
 * @param Object $field - the sql row to add to the {user_info_field} table
 */
function addField($field) {
	global $DB;
	
	// see if this field name already exists. This could be if block was uninstalled and later reinstalled
	$fieldsql = "SELECT id FROM {user_info_field} WHERE shortname = '" . $field->shortname . "'";
	if(!$DB->record_exists_sql($fieldsql))
		$id = $DB->insert_record('user_info_field', $field);
	else
		echo get_string('erroralreadyexists','local_campusvue',$field);
}
