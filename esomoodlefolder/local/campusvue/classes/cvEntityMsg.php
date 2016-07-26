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
 * 
 *
 * @package   local_campusvue
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/campusvue/lib.php');

/**
 * 
 *
 * @package    local_campusvue
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cvEntityMsg {
	public $EntityType = '';
	public $RequestId = 0;
	public $EntitySearchClauses = array();
	
	public function __construct ($EntityType) {
        $this->EntityType = $EntityType;
		$this->EntitySearchClauses[0] = (object) array('SearchParameters'=> array(),'Operator'=> 'Undefined');
    }
	
	public function incrementRequestId() {
		$this->RequestId ++;
	}
	
	public function addParam($fieldName, $fieldValue, $paramOperator) {
		$param = array('FieldName'=>$fieldName,
					'FieldValue'=>$fieldValue,
					'Operator'=>$paramOperator);
		$this->EntitySearchClauses[0]->SearchParameters[] = $param;
		$clause = $this->EntitySearchClauses[0];
		if ($clause->Operator == 'Undefined' && count($clause->SearchParameters) > 1) {
			$this->EntitySearchClauses[0]->Operator = 'And';
		}
	}
	
	public function makeAndClause() {
		if ($this->EntitySearchClauses[0]->Operator != 'Undefined') {
			$this->EntitySearchClauses[0]->Operator = 'And';
		}
	}
	
	public function makeOrClause() {
		if ($this->EntitySearchClauses[0]->Operator != 'Undefined') {
			$this->EntitySearchClauses[0]->Operator = 'Or';
		}
	}
	
	public function getEntity($token = null, $client = null) {
		if ($token == null) {
			$token = cvGetToken();
		}
		if ($client == null) {
			$client = cvBuildClient('cmc.campuslink.webservices','GetEntityWebService.asmx');
		}
		$inMsg = array('RowCount' => 0,
						'Entity' => $this);
		$inMsgs = array($inMsg);
		$args = array('GetEntityRequest' => array('TokenId' => $token,
													'Entities' => $inMsgs) );
		$result = $client->__soapCall('GetEntity', array($args));
		if (!isset($result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities->EntityString)){
			//add error handling
		}
		$entStr = $result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities->EntityString;
		$xmlStr = new SimpleXMLElement($entStr);
		return $xmlStr;
	}
	
	public function getEntityField($fieldName) {
		return $this->getEntity()->$fieldName;
	}
	
	public function getSearchableAttributes($token = null, $client = null) {
		if ($token == null) {
			$token = cvGetToken();
		}
		if ($client == null) {
			$client = cvBuildClient('cmc.campuslink.webservices','GetEntityWebService.asmx');
		}
		$inMsg = array('BusinessEntityType' => $this->EntityType);
		$inMsgs = array($inMsg);
		$args = array('GetSearchableAttributeRequest' => array('TokenId'=>$token,
																'GetSearchableAttributes'=>$inMsgs) );
		$result = $client->__soapCall('GetSearchableAttributes', array($args));
		if (!isset($result->GetSearchableAttributeResponse->GetSearchableAttributes->GetSearchableAttributeOutMsg->SearchableAttributes)){
			//add error handling
		}
		$attributes = $result->GetSearchableAttributeResponse->GetSearchableAttributes->GetSearchableAttributeOutMsg->SearchableAttributes;
		$return = array();
		foreach ($attributes AS $att){
			$return[$att->Name] = $att->Value;
		}
		return $return;
	}
}