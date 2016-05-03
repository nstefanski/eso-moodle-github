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
 * Escoffier forward local plugin install functions
 *
 * @package   local_escoforward
 * @copyright 2013 onwards Johan Reinalda (http://www.thunderbird.edu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_escoforward_install() {

	$catid = 1; //default 'Other' profile category
	// insert new profile category
	// $category = new stdClass();
	// $category->name = get_string('categoryname','local_escoforward');
	// $category->sortorder = 1;
	// $catid = $DB->insert_record('user_info_category',$category);
	
	// Initial insert of some new faculty related profile fields
	$field = new stdClass();
	
	// default settings; hide field for all but user.
	$field->descriptionformat = 1;
	$field->categoryid = $catid;
	$field->required = 0;
	$field->locked = 1;
	$field->visible = 0;	// admin only; i.e. not visible on profile (by user nor others)
	$field->forceunique = 0;
	$field->signup = 0;
	$field->defaultdataformat = 0;
	$field->param1 = 60;
	$field->param2 = 2048;
	// $field->defaultdata = '';
	// $field->param3 = 0;
	// $field->param4 = 
	// $field->param5 =

	$field->shortname = 'escoforwardemail';	// NOTE: moodle does not allow - or _ in this field!
	$field->name = get_string('forwardemailname','local_escoforward');
	$field->datatype = 'text';
	$field->description = get_string('forwardemaildescr','local_escoforward');
	$field->sortorder = 1;
	addField($field);
	
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
		echo '<p>' . get_string('customprofilefieldstring','local_escoforward') . ' "' . $field->shortname . '" '
				. get_string('alreadyexists','local_escoforward') . '</p>';
}
