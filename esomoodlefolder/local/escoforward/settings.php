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
 * AspirEDU Integration
 *
 * @package    local_escoforward
 * @author     Triumph Higher Education Group
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page

    $settings = new admin_settingpage('local_escoforward', new lang_string('pluginname', 'local_escoforward'));
	$ADMIN->add('localplugins', $settings);

	$options = array(
        0 => get_string('nolimit', 'local_escoforward'),
        1 => get_string('limitmodified', 'local_escoforward'),
        2 => get_string('limitcreated', 'local_escoforward'),
    );
    $default = 0;

    $settings->add(new admin_setting_configselect('local_escoforward/limittype',
        get_string('limittype', 'local_escoforward'), 
		get_string('limittype_help', 'local_escoforward'), $default, $options));
		
	$settings->add(new admin_setting_configtext('local_escoforward/timelimit',
        get_string('timelimit', 'local_escoforward'),
        get_string('timelimit_help', 'local_escoforward'), 3600, PARAM_INT));
}