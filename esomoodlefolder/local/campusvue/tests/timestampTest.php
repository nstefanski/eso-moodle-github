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

global $CFG, $DB;
require_once($CFG->dirroot.'/local/campusvue/lib.php');
require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');

$att = new mdAttendance(1);
print_r($att);

echo "<hr>";

$timestamp = $DB->get_records_sql("SELECT 1 AS id, NOW() + INTERVAL 1 WEEK - INTERVAL 1 DAY AS timestamp");
$dateString = $timestamp[1]->timestamp;
print_r($dateString);

echo "<hr>";

print_r($att->cvFormatDate($dateString));
echo "<hr>";
print_r(is_numeric($dateString));
echo "<hr>";
$dt = new DateTime($dateString);
print_r($dt);
echo "<hr>";
print_r($dt->format('Y-m-d\TH:i:s') );
echo "<hr>";

try{
	$dt2 = new DateTime("asdfghjkl");
	print_r($dt2->format('Y-m-d\TH:i:s') );
	echo "<hr>";
} catch(Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
	echo "<hr>";
}
if($dt2){
	echo "is a date";
} else {
	echo "is not a date";
}