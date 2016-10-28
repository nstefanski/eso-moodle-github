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

$string['pluginname'] = 'CampusVue Integration';
$string['serverheading'] = 'External API: Server Settings';
$string['servername_title'] = 'Server Name';
$string['servername_desc'] = 'This is the server name used to access the External API, such as https://api#####.campusnet.net <br />Note: Do NOT use a trailing slash (/)';
$string['errorservernamenotfound'] = 'The Server Name has not been configured';
$string['username_title'] = 'CampusVue Username';
$string['username_desc'] = 'Username of a CampusVue user with superuser or admin access';
$string['password_title'] = 'CampusVue Password';
$string['password_desc'] = 'Password for the CampusVue user';
$string['attendanceheading'] = 'Attendance Settings';
$string['manualcatlimit_title'] = 'Manually Marked Attendance: Limit to Category Paths';
$string['manualcatlimit_desc'] = 'Select categories to be scanned for manually marked attendance.  Child categories are automatically included';
$string['weekcompcatlimit_title'] = 'Weekly Completion Attendance: Limit to Category Paths';
$string['weekcompcatlimit_desc'] = 'Select categories to be scanned for weekly completion attendance.  Child categories are automatically included';
//$string['allcategories'] = 'All Categories';

$string['profilefield_name'] = 'CampusVue ID';
$string['profilefield_desc'] = 'The SyStudentId from CampusVue';

$string['update_cv_attendances'] = 'Update manually marked attendances in CampusVue';
$string['update_cv_weekcomp'] = 'Update weekly completion attendances in CampusVue';

$string['errorusernamenotfound'] = 'The username and password have not been configured';
$string['erroralreadyexists'] = '<p>The custom Profile Field "{$a->shortname}" already exists (probably from previous install)!</p>';
