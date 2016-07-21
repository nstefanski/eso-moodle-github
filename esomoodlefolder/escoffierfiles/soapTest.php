<?php

try {
	$client = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices.security/Authentication.asmx?WSDL');
	$params = array('TokenRequest' => array('UserName'=>'193nstefanski',
											'Password'=>'M4ust0t!',
											'TokenNeverExpires'=>false) );
	print_R($params);
	echo '<br><br>';
	$result = $client->__soapCall('GetAuthorizationToken', array($params));
	print_R($result->TokenResponse->TokenId);
	
	$json = json_encode($result); //needed??
	$token = $result->TokenResponse->TokenId;
} catch (Exception $e) {
	echo $e;
}

echo '<hr>';

$studentid = 206552;
$csid = 3653;
$attdate = '2016-07-13T00:00:00';

if ($token){
  try {
  	$client = new SoapClient('https://api5079.campusnet.net/cmc.campuslink.webservices/AttendanceWebService.asmx?WSDL', array('trace'=>TRUE));
  	//print_R($client);
  	//echo '<br>';
  	//var_dump($client->__getFunctions());
  	//echo '<br>';
  	//var_dump($client->__getTypes());
	
	$inMsg = array('StudentId' => $studentid,
					'CourseSectionId' => $csid,
					'AttendanceDate' => $attdate,
					'IsDependentCourse' => false,
					'UpdateExistingAttendance' => true,
					'MinutesAttended' => 99,
					'MinutesAbsent' => 33,
					'IsExcused' => false);
	$inMsgs = array($inMsg);
	$params = array('PostAttendanceTransactionRequest' => array('TokenId'=>$token,
																'Attendances'=>$inMsgs) );
	
	//print_R($params);
	//echo '<br>';
  	$result = $client->__soapCall('PostAttendanceTransaction', array($params));
  	print_R($result);
  	
  } catch (Exception $e) {
  	echo $e;
  }
  echo '<hr>';
}