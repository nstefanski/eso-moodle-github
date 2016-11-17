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

global $CFG;
require_once($CFG->dirroot.'/local/campusvue/lib.php');

$keepFor = 5; //tk

$dir = $CFG->dirroot.'/local/campusvue/logs';
$files = scandir($dir);

foreach ($files as $key => $filename) {
	if ($filename != '.' && $filename != '..') {
		$filedir = $dir . '/' . $filename;
		if (file_exists($filedir)) {
			$daysAgo = (time() - filemtime($filedir) )/86400;
			if ($daysAgo > $keepFor) {
				//unlink($filedir);
				echo "Deleted $filename ...";
			}
		}
	}
}