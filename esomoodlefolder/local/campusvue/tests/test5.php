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
require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');

$maxtime = 1477515840;
$mintime = $maxtime - 624960;
$test = $mintime;

print_R($test);
echo 'hi';

$test = new mdAttendance($maxtime, $mintime, null, 'weekcomp');

print_R($test);