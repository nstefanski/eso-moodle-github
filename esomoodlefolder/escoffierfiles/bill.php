<?php

try {
	//$cill = new SoapClient("http://coreapi6238:8088/Cmc.CampusLink.WebServices.Security/Authentication.asmx?WSDL");
	$cill = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices.security/Authentication.asmx?WSDL');
	//print_R($cill);
	//var_dump($cill->__getFunctions());
	//var_dump($cill->__getTypes());
	$params = array('TokenRequest' => array('UserName'=>'193nstefanski',
											'Password'=>'M4ust0t!',
											'TokenNeverExpires'=>false) );
	print_R($params);
	echo '<br><br>';
	$result = $cill->__soapCall('GetAuthorizationToken', array($params));
	print_R($result->TokenResponse->TokenId);
	
	$json = json_encode($result); //needed??
	$token = $result->TokenResponse->TokenId;
} catch (Exception $e) {
	echo $e;
}

echo '<hr>';

try {
	//$sill = new SoapClient('https://api5079.campusnet.net/cmc.integration.webservices.wcf/CoursesService.svc?WSDL',
	$sill = new SoapClient('https://api5079.campusnet.net/cmc.integration.webservices.wcf/CourseSectionService.svc?WSDL',
                           array('soap_version' => 'SOAP_1_2',
                                 'location'=>'https://api5079.campusnet.net/cmc.integration.webservices.wcf/CourseSectionService.svc',
                                 'trace'=>TRUE) );
	print_R($sill);
} catch (Exception $e) {
	echo $e;
}
echo '<hr>';

$studentid = 206552;
$csid = 3653;
$attdate = '2016-07-13T00:00:00';
$attdate2 = '2016-07-14T00:00:00';

try {
	$client = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices/GetEntityWebService.asmx?WSDL', array('trace'=>TRUE));
	print_R($client);
	//var_dump($client->__getFunctions());
	//var_dump($client->__getTypes());
	
	$esp = array('FieldName'=>'LastName',
				'FieldName'=>'Handy',
				'Operator'=>'Equal'); //see ParameterOperatorType in WSDL
	$esps = array($esp);
	//$qfs = array('QueryFlagType' => 'CountAll');
	$clause = array('SearchParameters'=> $esps,
					'Operator'=> 'And'
					//,'QueryFlags'=> $qfs
					);
	$clauses = array($clause);
	$ent = array('EntitySearchClauses'=> $clauses,
				'RequestId'=> 0);
	$inMsg = array('RowCount' => 0,
					'Entity' => $ent);
	$inMsgs = array($inMsg);
	$params = array('GetEntityRequest' => array('TokenId'=>$token,
												'Entities'=>$inMsgs) );
	echo '<br>';
	print_R($params);
	echo '<br>';
	
	$result = $client->__soapCall('GetEntity', array($params));
  	print_R($result);
} catch (Exception $e) {
	echo $e;
}
echo '<hr>';

if ($token){
  try {
  	$aill = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices/AttendanceWebService.asmx?WSDL', array('trace'=>TRUE));
  	print_R($aill);
  	echo '<br>';
  	var_dump($aill->__getFunctions());
  	echo '<br>';
  	//var_dump($aill->__getTypes());
	
	$inMsg = array('StudentId' => $studentid,
					'CourseSectionId' => $csid,
					'AttendanceDate' => $attdate,
					'IsDependentCourse' => false,
					'UpdateExistingAttendance' => true,
					//'MinutesAttended' => 99,
					'MinutesAbsent' => 13,
					'IsExcused' => false);
	$inMsg2 = array('StudentId' => $studentid,
					'CourseSectionId' => $csid,
					'AttendanceDate' => $attdate2,
					'IsDependentCourse' => false,
					'UpdateExistingAttendance' => true,
					//'MinutesAttended' => 99,
					'MinutesAbsent' => 14,
					'IsExcused' => false);
	$inMsgs = array($inMsg,$inMsg2);
	$params = array('PostAttendanceTransactionRequest' => array('TokenId'=>$token,
																'Attendances'=>$inMsgs) );
	/*$root = '<?xml version="1.0" encoding="UTF-8"?><Activities/>';
	$params = new simpleXMLElement($root); 
		$patr = $params->addChild('PostAttendanceTransactionRequest');
			$patr->addChild('TokenId', $token);
			$aopaim = $patr->addChild('ArrayOfPostAttendanceInMsg');
				$paim = $aopaim->addChild('PostAttendanceInMsg');
					$paim->addChild('StudentId', $studentid);
					$paim->addChild('CourseSectionId', $csid);
					$paim->addChild('AttendanceDate', $attdate);
					$paim->addChild('IsDependentCourse', 0);
					$paim->addChild('UpdateExistingAttendance', true);
					$paim->addChild('MinutesAttended', 99);
					$paim->addChild('MinutesAbsent', 33);
					$paim->addChild('IsExcused', 0);*/
	/*$inMsg = array('StudentId' => $studentid,
					'CourseSectionId' => $csid,
					'AttendanceDate' => $attdate,
					'IsDependentCourse' => false,
					'UpdateExistingAttendance' => true,
					'MinutesAttended' => 99,
					'MinutesAbsent' => 33,
					'IsExcused' => false);
	$inMsg = new stdClass;
		$inMsg->StudentId = $studentid;
		$inMsg->CourseSectionId = $csid;
		$inMsg->AttendanceDate = $attdate;
		$inMsg->IsDependentCourse = false;
		$inMsg->UpdateExistingAttendance = true;
		$inMsg->MinutesAttended = 99;
		$inMsg->MinutesAbsent = 33;
		$inMsg->IsExcused = false;
	$params = array('PostAttendanceTransactionRequest' =>
  	                array('TokenId' => $token,
  	                      'ArrayOfPostAttendanceInMsg' =>
  	                      array($inMsg)
  	                )
  	           );*/
  	/*$params = array('PostAttendanceTransactionRequest' =>
  	                array('TokenId' => $token,
  	                      'ArrayOfPostAttendanceInMsg' =>
  	                      array('PostAttendanceInMsg' =>
  	                            array('StudentId' => $studentid,
  	                                  'CourseSectionId' => $csid,
  	                                  'AttendanceDate' => $attdate,
  	                                  'IsDependentCourse' => false,
  	                                  'UpdateExistingAttendance' => true,
  	                                  'MinutesAttended' => 99,
  	                                  'MinutesAbsent' => 33,
  	                                  'IsExcused' => false)
  	                      )
  	                )
  	           );*/
	print_R($params);
	echo '<br>';
  	$result = $aill->__soapCall('PostAttendanceTransactionBatch', array($params));
  	print_R($result);
  	
  } catch (Exception $e) {
  	echo $e;
  }
  echo '<hr>';
}


//bill
$bill = new SoapClient('http://www.xmlme.com/WSShakespeare.asmx?WSDL', array('trace'=>TRUE));
//print_R($bill);

$request = (empty($_GET['r'])) ? 'polack' : $_GET['r'];

$params = array('Request'=>$request);
$result = $bill->__soapCall("GetSpeech", array($params));

function XMLToArray($xml) {
  $parser = xml_parser_create('ISO-8859-1'); // For Latin-1 charset
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); // Dont mess with my cAsE sEtTings
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); // Dont bother with empty info
  xml_parse_into_struct($parser, $xml, $values);
  xml_parser_free($parser);
  
  $return = array(); // The returned array
  $stack = array(); // tmp array used for stacking
  foreach($values as $val) {
    if($val['type'] == "open") {
      array_push($stack, $val['tag']);
    } elseif($val['type'] == "close") {
      array_pop($stack);
    } elseif($val['type'] == "complete" || $val['type'] == "cdata") { //tk
      array_push($stack, $val['tag']);
      setArrayValue($return, $stack, $val['value']);
      array_pop($stack);
    }//if-elseif
  }//foreach
  return $return;
}//function XMLToArray
  
function setArrayValue(&$array, $stack, $value) {
  if ($stack) {
    $key = array_shift($stack);
    setArrayValue($array[$key], $stack, $value);
    return $array;
  } else {
    $array = $value;
  }//if-else
}//function setArrayValue

$array = XMLToArray($result->GetSpeechResult);
//print_r($array[SPEECH][PLAY]);
$speech = $array[SPEECH];
echo "<b>PLAY:</b> $speech[PLAY] <br><b>SPEAKER:</b> $speech[SPEAKER] <br><b>SPEECH:</b> $speech[SPEECH]";