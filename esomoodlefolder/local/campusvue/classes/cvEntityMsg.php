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
			if (!isset($result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities)){
				//add error handling
			}
			$entArray = $result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities;
			$xmlArray = array();
			foreach ($entArray as $ent) {
				$xmlStr = new SimpleXMLElement($ent->EntityString);
				$xmlArray[] = $xmlStr;
			}
			return $xmlArray;
		}
		$entStr = $result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities->EntityString;
		$xmlStr = new SimpleXMLElement($entStr);
		return $xmlStr;
	}
	
	public function getEntityField($fieldName) {
		$result = $this->getEntity();
		if (gettype($result) == 'object' && get_class($result) == 'SimpleXMLElement') {
			return $result->$fieldName->__toString();
		} elseif (gettype($result) == 'array') {
			//array of Simple XML Elements
			$returnArray = array();
			foreach ($result as $xmlObj) {
				$returnArray[] = $xmlObj->$fieldName->__toString();
			}
			return $returnArray;
		}
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
	
	/* helper function, see 
	 * http://www.mycampusinsight.com/support/CampusNexus%20Service%20Catalog/Default.htm#Utilities/SimpleLists.htm%3FTocPath%3DUtilities%7CGet%2520List%7CList%2520Types%7C_____1 
	 */
	public function listEntityTypes() {
		return array('AcademicAdvisor','AcademicYear','Activity','ActivityEventStatus','ActivityEventType','ActivityResult','ActivityTemplate','AddressType','AdmissionsOfficer','Advisor','Agency','AgencyBranch','Application','AreaOfInterest','AreaOfStudy','AreaOfStudyType','Bank','BankAccount','BillingMethod','Campus','CampusGroup','CampusModule','CashPayment','CIPCode','Citizenship','ClassAttendance','College','County','Country','Course','CourseSection','CreditCard','DegreeCourse','Document','Employer','EmployerContact','EmployerJob','EmploymentStatus','EnrolledProgram','EnrollmentAdvisor','Ethnicity','ExtraCurricular','FinancialAidAward','FundSource','Gender','GradeLetter','GradeLevel','GradeScale','HighSchool','LeadCategory','LeadEntranceTest','LeadInquiry','LeadSource','LeadType','LmsVendor','MaritalStatus','Nationality','PasswordProfile','PostalCode','PreviousEducation','Program','ProgramVersion','ProspectPreviousEducation','QuickLeadTemplate','SchoolStatus','Shift','Staff','StaffGroup','StartDate','State','Student','StudentAcademicYear','StudentAddress','StudentBankAccount','StudentLeaseHistory','StudentSubsidiaryLedger','SubsidiaryLedgerType','Suffix','Term','Test','Title','TransactionCode');
	}
	
	/* helper function, see 
	 * http://www.mycampusinsight.com/support/CampusNexus%20Service%20Catalog/Default.htm#Utilities/WSDL_GetEntity.htm%3FTocPath%3DUtilities%7CGet%2520Entity%7C_____4 
	 */
	public function listParamOperators() {
		return array('Equal','NotEqual','GreaterThan','GreaterThanOrEqual','LessThan','LessThanOrEqual','Like','IsNull','IsNullOrZero','IsNullOrEmptyString','IsNotNull','Between','In','NotIn','NotLike','BitWiseOR','BitWiseAND','NotBitWiseOR','NotBitWiseAND');
	}
}