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

defined('MOODLE_INTERNAL') || die();

// Ensure the configurations for this site are set
if ( $hassiteconfig ){
	
	$settings = new admin_settingpage( 'local_campusvue', get_string('pluginname', 'local_campusvue') );
	
	$ADMIN->add( 'localplugins', $settings );
	
	//general settings
	$settings->add(new admin_setting_heading('local_campusvue/serverheading', get_string('serverheading', 'local_campusvue'), ''));
	
	$servername = new admin_setting_configtext('local_campusvue/servername', get_string('servername_title', 
		'local_campusvue'), get_string('servername_desc', 'local_campusvue'), null, PARAM_URL);
	$settings->add($servername);
	
	$username = new admin_setting_configtext('local_campusvue/username', get_string('username_title', 
		'local_campusvue'), get_string('username_desc', 'local_campusvue'), null, PARAM_TEXT);
	$settings->add($username);
	
	$password = new admin_setting_configtext('local_campusvue/password', get_string('password_title', 
		'local_campusvue'), get_string('password_desc', 'local_campusvue'), null, PARAM_TEXT);
	$settings->add($password);
	
	//attendance settings
	$settings->add(new admin_setting_heading('local_campusvue/attendanceheading', get_string('attendanceheading', 'local_campusvue'), ''));
	
	/*$manualcatlimit = new admin_setting_configtextarea('local_campusvue/manualcatlimit', get_string('manualcatlimit_title', 
		'local_campusvue'), get_string('manualcatlimit_desc', 'local_campusvue'), '/%');
	$settings->add($manualcatlimit);/**/
	$catrecords = $DB->get_records('course_categories',array(),'sortorder');
    if ($catrecords) {
        $cats = [];
		$cats['/'] = '(' . get_string('allcategories', 'local_campusvue') . ')';
        foreach ($catrecords as $catrecord) {
			$indent = '';
			for ($i = 1; $i < $catrecord->depth; $i++) {
				$indent = $indent . '-> ';
			}
            $cats[$catrecord->path] = $indent . $catrecord->name;
        }
        //asort($cats);
        $manualcatlimit = new admin_setting_configmultiselect('local_campusvue/manualcatlimit', get_string('manualcatlimit_title', 
			'local_campusvue'), get_string('manualcatlimit_desc', 'local_campusvue'), [], $cats);
		$settings->add($manualcatlimit);
    }/**/
}