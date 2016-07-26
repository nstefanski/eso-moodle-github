<?php

try {
	$cill = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices.security/Authentication.asmx?WSDL');
	$args = array('TokenRequest' => array('UserName'=>'***REMOVED***',
											'Password'=>'***REMOVED***',
											'TokenNeverExpires'=>false) );
	$result = $cill->__soapCall('GetAuthorizationToken', array($args));
	$token = $result->TokenResponse->TokenId;
} catch (Exception $e) {
	echo $e;
}

$entityType = 'Student';
$fieldName = 'StudentNumber';
$fieldValue = 1607222765;
$paramOperator = 'Equal';
$getField = 'Id';

//$fieldName = 'SSN';
//$fieldValue = '777-55-3333';

if ($token){
	try {
		$client = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices/GetEntityWebService.asmx?WSDL', array('trace'=>TRUE));
		
		//args for GetSearchableAttributes
		/*$inMsg = array('BusinessEntityType' => $entityType);
		$inMsgs = array($inMsg);
		$args = array('GetSearchableAttributeRequest' => array('TokenId'=>$token,
																'GetSearchableAttributes'=>$inMsgs) );
		$result = $client->__soapCall('GetSearchableAttributes', array($args));
		$attributes = $result->GetSearchableAttributeResponse->GetSearchableAttributes->GetSearchableAttributeOutMsg->SearchableAttributes;
		foreach ($attributes AS $att){
			echo "$att->Name [ $att->Value ]<br>";
		}*/
		
		//args for GetEntity
		$param = array('FieldName'=>$fieldName,
					'FieldValue'=>$fieldValue,
					'Operator'=>$paramOperator); //see ParameterOperatorType in WSDL
		$param2 = array('FieldName'=>'SSN',
					'FieldValue'=>'777-55-3333',
					'Operator'=>$paramOperator); //see ParameterOperatorType in WSDL
		$params = array($param, $param2);
		//$qfs = array('QueryFlagType' => 'CountAll');
		$clause = (object) array('SearchParameters'=> $params
						,'Operator'=> 'And' //see ClauseOperatorType in WSDL
						//,'QueryFlags'=> $qfs
						);
		$clauses = array($clause);
		$ent = (object) array('EntitySearchClauses'=> $clauses,
					'RequestId'=> 0,
					'EntityType'=> $entityType);
		$inMsg = array('RowCount' => 0,
						'Entity' => $ent);
		$inMsgs = array($inMsg);
		$args = array('GetEntityRequest' => array('TokenId'=>$token,
													'Entities'=>$inMsgs) );
		
		print_R($args);
		$result = $client->__soapCall('GetEntity', array($args));
		$entStr = $result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities->EntityString;

		$xmlStr = new SimpleXMLElement($entStr);
		
		echo '<br><br>' . $xmlStr->$getField;
	} catch (Exception $e) {
		echo $e;
	}
}