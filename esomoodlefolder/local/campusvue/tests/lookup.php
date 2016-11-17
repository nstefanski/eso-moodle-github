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
 * Tests
 */

//defined('MOODLE_INTERNAL') || die();
require_once('../../../config.php');

require_login();

if( is_siteadmin() ) {

	$idnumber = (empty($_GET['idnumber'])) ? NULL : $_GET['idnumber'];

	echo "<h1>CVue Lookup by Student Number</h1>";

	if($idnumber){
		global $CFG, $DB;
		require_once($CFG->dirroot.'/local/campusvue/lib.php');

		$token = cvGetToken();
		$cvid = cvGetSyStudentId($idnumber, $token);
		echo "SyStudentId: $cvid";
	}

}