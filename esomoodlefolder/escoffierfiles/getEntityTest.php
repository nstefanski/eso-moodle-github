<?php

try {
	$cill = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices.security/Authentication.asmx?WSDL');
	$args = array('TokenRequest' => array('UserName'=>'193nstefanski',
											'Password'=>'M4ust0t!',
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
		$params = array($param);
		//$qfs = array('QueryFlagType' => 'CountAll');
		$clause = array('SearchParameters'=> $params
						,'Operator'=> 'Undefined' //see ClauseOperatorType in WSDL
						//,'QueryFlags'=> $qfs
						);
		$clauses = array($clause);
		$ent = array('EntitySearchClauses'=> $clauses,
					'RequestId'=> 0,
					'EntityType'=> $entityType);
		$inMsg = array('RowCount' => 0,
						'Entity' => $ent);
		$inMsgs = array($inMsg);
		$args = array('GetEntityRequest' => array('TokenId'=>$token,
													'Entities'=>$inMsgs) );
		
		$result = $client->__soapCall('GetEntity', array($args));
		$entStr = $result->GetEntityResponse->EntityList->GetEntityOutMsg->SerializedEntities->EntityString;

		$xmlStr = new SimpleXMLElement($entStr);
		
		echo $xmlStr->$getField;
	} catch (Exception $e) {
		echo $e;
	}
}