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
require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');

echo cvGetToken() . '<hr>';

$cem = new cvEntityMsg('Student');
$fieldName = 'StudentNumber';
$fieldValue = 1607222765;
$paramOperator = 'Equal';
$cem->addParam($fieldName, $fieldValue, $paramOperator);
print_R($cem);
//$cem->makeOrClause();
//$cem->addParam($fieldName, $fieldValue, $paramOperator);
echo '<br><br>' . $cem->getEntity()->Id;

echo '<br><br>' . $cem->getEntityField('Id');
//$clause = $cem->EntitySearchClauses[0]->Operator;
//echo $clause;