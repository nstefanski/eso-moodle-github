<?php
 /* should always be called by a page that requires Moodle config and login */
//require_once('../config.php');
//require_login();

require_once('curl_wrap.php');

/* 
 * $user should be a user object
 * $returns array including last API call params and results
 */
function updateZenPhone($user = null) 
{
	/*if (is_null($user)) {
		$user = $USER;
	}//*/
	$name = $user->firstname.' '. $user->lastname; 
	$email = $user->email;
	$mdid = $user->id;
	$orgid = null; //use if we want to pass an organization_id for user creation
	$country = $user->country;
	$international = false;
	$payload = null;
	$apicalls = 0;

	//get user phone if in US
	$phone = str_replace(array('(',')','-',' ','.','+','cell','mobile'), '', trim($user->phone1));
	if ($phone) {
		if ($country == "US") {
			if (substr($phone,0,1) != "1") {
				$phone = "1" . $phone;
			}
			if (strlen($phone) == 11) {
				$phone = '+' . $phone;
			} else {
				$phone = null;
			}
		} else {
			$phone = '+' . $phone;
			$international = true;
		}
	}

	//SEARCH for user with given email
	$query = "type:user email:$email";
	$apiurl = "/search.json?query=" . urlencode($query);
	$action = "GET";
	$data = curlWrap($apiurl, null, $action);
	$apicalls++;

	if($data->results) {
		$zdid = $data->results[0]->id;
	} else {
		$zdid = null;
	}

	//if phone: SEARCH for identity with that phone
	if ($phone && !$international) {
		//$phoneName = "Caller " . substr($phone,0,2) . " (" . substr($phone,2,3) . ") " . substr($phone,5,3) . "-" . substr($phone,8,4);
		//$query = 'type:user phone:' . $phone . ' name:' . '"' . $phoneName . '"';
		$query = "type:user phone:$phone";
		$apiurl = "/search.json?query=" . urlencode($query);
		$action = "GET";
		$data = curlWrap($apiurl, null, $action);
		$apicalls++;
		if($data->results) {
			$conflicts = 0;
			foreach ($data->results as $result) {
				if (substr($result->name,0,8) == "Caller +") {
					$zdcallerid = $result->id;
				} elseif ($result->id == $zdid) {
					//do nothing
				} else {
					$conflicts++; //ruh roh shaggy, looks like someone else is using this phone number!
					//We can add code to try to solve this later, for now we're just going to skip setting this phone number
				}
			}
			if ($conflicts) {
				$phone = null;
			}
		} else {
			$zdcallerid = null;
		}
	} elseif ($phone && $international) { //this case exists in case we want to add international support later
		$phone = null;
	} else {
		$zdcallerid = null;
	}

	if ($zdid) {
		if ($zdcallerid) {
			//MERGE users
			$apiurl = "/users/$zdcallerid/merge.json";
			$payload = array("user" => array("id" => $zdid) );
			$json = json_encode($payload);
			$action = "PUT";
			$data = curlWrap($apiurl, $json, $action);
			$apicalls++;
		} elseif ($phone) { //if no phone, then we're done
			//CREATE new identity for user
			$apiurl = "/users/$zdid/identities.json";
			$payload = array("identity" => array("type" => "phone_number", "value" => "$phone", "verified" => true) );
			$json = json_encode($payload);
			$action = "POST";
			$data = curlWrap($apiurl, $json, $action); //if this user already has a phone set, this will just return an error
			$apicalls++;
			if ($data->error) {
				//send an error report
			}
		}
	} else {
		if ($zdcallerid) {
			//UPDATE user with $zdcallerid
			$apiurl = "/users/$zdcallerid.json";
			$payload = array("user" => array("name" => $name, "email" => $email, "external_id" => $mdid, "organization_id" => $orgid) );
			$json = json_encode($payload);
			$action = "PUT";
			$data = curlWrap($apiurl, $json, $action);
			$apicalls++;
			$zdid = $zdcallerid;
		} else {
			//CREATE new user
			$apiurl = "/users.json";
			$identities = array();
			$identities[] = array("type" => "email", "value" => "$email", "verified" => true, "primary" => true);
			if ($phone) {
				//add phone identity
				$identities[] = array("type" => "phone_number", "value" => "$phone", "verified" => true);
			}
			$payload = array("user" => array("name" => $name, "external_id" => $mdid, "organization_id" => $orgid, "identities" => $identities) );
			$json = json_encode($payload);
			$action = "POST";
			$data = curlWrap($apiurl, $json, $action);
			$apicalls++;
			$zdid = $data->user->id;
		}
	}
	
	$results = array(
		"user" => array(
			"name" => $name,
			"email" => $email,
			"mdid" => $mdid,
			"zdid" => $zdid,
			"org" => $orgid,
			"country" => $country,
			"phone" => $phone
		),
		"apicalls" => $apicalls,
		"apiurl" => $apiurl,
		"payload" => $payload,
		"action" => $action,
		"data" => $data
	);
	
	return $results;
}
?>