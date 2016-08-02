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
 * helper function to authenticate date format '2016-07-13T00:00:00'
 *
 * @param mixed $dateString can be int or DateTime object
 * @return string $dateString
 */
function cvFormatDate($dateString) {
	//timestamp
	if (is_numeric($dateString)){
		if ($dateString < 15000000000 ) { //corresponds to 6/23/1970 in milliseconds, or 5/1/2445 in seconds
			//timestamp is in seconds
			return date('Y-m-d\TH:i:s', $dateString);
		} else {
			//timestamp is in milliseconds
			return date('Y-m-d\TH:i:s', $dateString/1000);
		}
	}
	//object
	if (gettype($dateString) == 'object') {
		if (get_class($dateString) == 'DateTime'){
			return $dateString->format('Y-m-d\TH:i:s');
		}
	}
	//string
	return $dateString;
}

/**
 * zero out time in AttendanceDate
 *
 * @param string $dateString in format '2016-07-13T00:00:00'
 * @return string $dateString
 */
function zeroTime($dateString) {
	$dt = explode('T', $dateString);
	return $dt[0] . 'T00:00:00';
}