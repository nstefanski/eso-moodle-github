<?php

// This file is part of Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die();

/**
 * Creates a new Soap client using the server name from settings
 *
 * @param string $servicepath of the webservice, ie 'cmc.campuslink.webservices.security' or 'cmc.integration.webservices.wcf'
 * @param string $servicename of the webservice, ie 'Authentication.asmx' or 'CoursesService.svc'
 * @param string $type of description file, default 'WSDL'
 * @return SoapClient object
 */
function cvBuildClient($servicepath, $servicename, $type = 'WSDL') {
	$config = get_config('local_campusvue');
	if (!isset($config->servername)) {
		//Throw error
		throw new moodle_exception('errorservernamenotfound', 'local_campusvue');
	}
	
	$endpoint = $config->servername . '/' . $servicepath . '/' . $servicename . '?' . $type;
	$client = new SoapClient($endpoint);
	
	return $client;
}

/**
 * Gets a new Token from CampusVue using username and password from settings
 *
 * @param bool $tokenNeverExpiresjust , default false
 * @return string $token
 */
function cvGetToken($tokenNeverExpires = false) {
	$config = get_config('local_campusvue');
	if (!isset($config->username) || !isset($config->password)) {
		//Throw error
		throw new moodle_exception('errorusernamenotfound', 'local_campusvue');
	}
	$client = cvBuildClient('cmc.campuslink.webservices.security', 'Authentication.asmx');
	$args = array('TokenRequest' => array('UserName' => $config->username,
										'Password' => $config->password,
										'TokenNeverExpires' => $tokenNeverExpires) );
	$result = $client->__soapCall('GetAuthorizationToken', array($args));
	if (!isset($result->TokenResponse->TokenId)){
		//add error handling
	}
	$token = $result->TokenResponse->TokenId;
	return $token;
}
	
/**
 * get SyStudentId based on StudentNumber
 *
 * @param int $StudentNumber
 * @return int $SyStudentId or null
 */
function cvGetSyStudentId($StudentNumber, $token = null) {
	if (empty($StudentNumber)) { return null; }
	global $CFG;
	require_once($CFG->dirroot.'/local/campusvue/classes/cvEntityMsg.php');
	$cem = new cvEntityMsg('Student');
	$cem->addParam('StudentNumber', $StudentNumber, 'Equal');
	if (!$token) {
		$token = cvGetToken();
	}
	$SyStudentId = $cem->getEntityField('Id', $token);
	return $SyStudentId;
}

/**
 * Executes the scheduled task
 *
 * @param 
 * @return 
 */
function updateAttendance($maxTime, $minTime, $token = null, $method = 'manual', $debug = false) {
	global $CFG;
	require_once($CFG->dirroot.'/local/campusvue/classes/mdAttendance.php');
	require_once($CFG->dirroot.'/local/campusvue/classes/cvAttendancesMsg.php');
	
	$att = new mdAttendance($maxTime, $minTime, $token, $method);
	$file = mdLogJSON($att, $method.'_MDdata');
	
	$msg = new cvAttendancesMsg();
	
	foreach($att->Attendance as $sess) {
		foreach($sess->Attendances as $attendance)
			$msg->addAttendance($attendance->StudentId, $sess->CourseSectionId, $sess->AttendanceDate, $attendance->MinutesAbsent, 0, false, $attendance->Excused);
	}
	
	$file = mdLogJSON($msg, $method.'_request');
	
	if ($msg->Attendances && !$debug) {
		try {
			$result = $msg->postAttendanceTransaction($token);
			$file = mdLogJSON($result, $method.'_response');
		} catch (moodle_exception $e) {
			return false;
		}
	} else {
		$result = $msg;
	}
	
	return $result;
}

/**
 * 
 *
 * @param 
 * @return 
 */
function mdLogJSON($data, $filename, $headers = null) {
	global $CFG;
	
	$timestamp = time();
	$path = 'local/campusvue/logs';
	$filename = $CFG->dirroot.'/'.$path.'/'.$filename.'_'.$timestamp.'.json';
		
	$handler = fopen ($filename,'w');
	fwrite ($handler,json_encode($data));
	fclose ($handler);
	
	return $filename;
}