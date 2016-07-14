<?php

$bill = new SoapClient("http://www.xmlme.com/WSShakespeare.asmx?WSDL", array('trace'=>TRUE));
//print_R($bill);

$request = (empty($_GET['r'])) ? 'polack' : $_GET['r'];

try {
	//$cill = new SoapClient("http://coreapi6238:8088/Cmc.CampusLink.WebServices.Security/Authentication.asmx?WSDL");
	$cill = new SoapClient("https://api5079.campusnet.net/cmc.campuslink.webservices.security/Authentication.asmx?WSDL");
	//print_R($cill);
	//var_dump($cill->__getFunctions());
	//var_dump($cill->__getTypes());
	$params = array('TokenRequest' => array('UserName'=>'193nstefanski', 
											'Password'=>'M4ust0t!',
											'TokenNeverExpires'=>false) );
	print_R($params);
	echo '<br><br>';
	$result = $cill->__soapCall("GetAuthorizationToken", array($params));
	print_R($result);
} catch (Exception $e) {
	echo $e;
}
echo '<hr>';

try {
	$aill = new SoapClient("https://api5079.campusnet.net/cmc.campuslink.webservices/AttendanceWebService.asmx?WSDL");
	print_R($aill);
} catch (Exception $e) {
	echo $e;
}
echo '<hr>';

try {
	$gill = new SoapClient("https://api5079.campusnet.net/cmc.campuslink.webservices/GradesWebService.asmx?WSDL");
	print_R($gill);
} catch (Exception $e) {
	echo $e;
}
echo '<hr>';

//var_dump($bill->__getFunctions());
//var_dump($bill->__getTypes());

$params = array('Request'=>$request);
print_R($params);
echo '<br><br>';
$result = $bill->__soapCall("GetSpeech", array($params));
print_R($result);
echo '<hr>';

//$lastReq = $bill->__getLastRequest();
//var_dump($lastReq);

//$lastResp = $bill->__getLastResponse();
//var_dump($lastResp);

//$xml = simplexml_load_string($result->GetSpeechResult) or die("Error: Cannot create object");
//$json = json_encode($xml);
//$array = json_decode($json,TRUE);
//print_R($array);

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