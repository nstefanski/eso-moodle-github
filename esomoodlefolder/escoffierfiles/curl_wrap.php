<?php
/* 
 * $url is endpoint of curl function, ie "/users.json"
 * $json is the json encoded payload to pass, otherwise NULL
 * $action is the type of request. Valid values are 'GET', 'POST', 'PUT', and 'DELETE'
 * $user can be a user object or NULL (if null set to admin account)
 */
function curlWrap($url, $json, $action, $user = null)
{
	$zdurl = "https://escoffier.zendesk.com/api/v2";
	/* Note: do not put a trailing slash at the end of v2 */
	$zdapikey = "r9MI9XovwgEoTz156E6kRBNkYARl8DiK0XOGr9ou";
	/* see https://escoffier.zendesk.com/agent/admin/api */
	
	$zduser = "";
	if (is_null($user)) {
		$zduser = "nstefanski@escoffieronline.com";
	} elseif (is_object($user)) {
		$zduser = $user->email;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, $zdurl.$url);
	curl_setopt($ch, CURLOPT_USERPWD, $zduser."/token:".$zdapikey);
	switch($action){
		case "POST":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "GET":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			break;
		case "PUT":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			break;
		case "DELETE":
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
		default:
			break;
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //tk
	$output = curl_exec($ch);
	//echo 'Curl output: ' . curl_error($ch) . '<br />'; //tk
	curl_close($ch);
	$decoded = json_decode($output);
	return $decoded;
}
?>