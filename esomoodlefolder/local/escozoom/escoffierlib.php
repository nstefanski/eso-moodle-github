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

defined('MOODLE_INTERNAL') || die();

global $CFG;
//require_once($CFG->dirroot.'/mod/zoom/lib.php');
require_once($CFG->dirroot.'/mod/zoom/classes/webservice.php');
require_once($CFG->libdir.'/messagelib.php');

/**
 * Delete all meeting recordings by a particular host older than a certain threshold
 *
 * @param string $hostId
 * @param int $daysOld
 * @return array
 */
function zoom_delete_user_recordings($hostId = "", $daysOld = 10) {
	$apiCalls = 0;
	//$files = array();
	$p = 1;
	$ps = 15;
	$to = date("m/d/Y h:i a",time() - ($daysOld * 24 * 60 * 60) );

	$service = new mod_zoom_webservice();

	$url = 'recording/list';
	$data = array('host_id' => $hostId, 'page_size' => $ps, 'page_number' => $p, 'to' => $to);
	$response = $service->make_call($url, $data);
	$apiCalls++;

	if ($response && $response->total_records > 0) {
		$meetings = $response->meetings; //array
		
		$p = $response->page_number;
		$pp = $response->page_count;
		while ($p < $pp) {
			$p++;
			$url = 'recording/list';
			$data = array('host_id' => $hostId, 'page_size' => $ps, 'page_number' => $p, 'to' => $to);
			$response = $service->make_call($url, $data);
			$apiCalls++;
			$meetings = array_merge($meetings, $response->meetings);
		}
		
		$bytes = 0;
		$count = 0;
		foreach ($meetings as $meeting) {
			if($meeting->total_size > 0) {
				$bytes = $bytes + $meeting->total_size;
			}

			//foreach ($meeting->recording_files as $file) {
				//store files?
				//$files[] = $file->download_url;
			//}
			//delete
			$url = 'recording/delete';
			$data = array('meeting_id' => $meeting->uuid);
			$response = $service->make_call($url, $data);
			$apiCalls++;
		}
		
		//$response = $service->make_call($url, $data);
		
		return array('api_calls' => $apiCalls, 'bytes' => $bytes);
	} else {
		return array('api_calls' => $apiCalls, 'bytes' => 0);
	}
}

/**
 * Evaluate size of all meeting recordings by a particular host newer than a certain threshold
 *
 * @param string $hostId
 * @param int $daysOld
 * @return array
 */
function zoom_count_user_recordings($hostId = "", $daysOld = 365) {
	$apiCalls = 0;
	$p = 1;
	$ps = 15;
	$from = date("m/d/Y h:i a",time() - ($daysOld * 24 * 60 * 60) );

	$service = new mod_zoom_webservice();

	$url = 'recording/list';
	$data = array('host_id' => $hostId, 'page_size' => $ps, 'page_number' => $p, 'from' => $from);
	$response = $service->make_call($url, $data);
	$apiCalls++;

	if ($response && $response->total_records > 0) {
		$meetings = $response->meetings; //array
		
		$p = $response->page_number;
		$pp = $response->page_count;
		while ($p < $pp) {
			$p++;
			$url = 'recording/list';
			$data = array('host_id' => $hostId, 'page_size' => $ps, 'page_number' => $p, 'from' => $from);
			$response = $service->make_call($url, $data);
			$apiCalls++;
			$meetings = array_merge($meetings, $response->meetings);
		}
		
		$bytes = 0;
		foreach ($meetings as $meeting) {
			if($meeting->total_size > 0) {
				$bytes = $bytes + $meeting->total_size;
			}
		}
		
		return array('api_calls' => $apiCalls, 'bytes' => $bytes);
	} else {
		return array('api_calls' => $apiCalls, 'bytes' => 0);
	}
}

/**
 * Get all zoom users
 *
 * @return array
 */
function zoom_get_all_users() {
	$apiCalls = 0;
	$p = 1;
	$ps = 30;

	$service = new mod_zoom_webservice();

	$url = 'user/list';
	$data = array('page_size' => $ps, 'page_number' => $p);
	$response = $service->make_call($url, $data);
	$apiCalls++;

	if ($response && $response->total_records > 0) {
		$users = $response->users; //array
		
		$p = $response->page_number;
		$pp = $response->page_count;
		while ($p < $pp) {
			$p++;
			$url = 'user/list';
			$data = array('page_size' => $ps, 'page_number' => $p);
			$response = $service->make_call($url, $data);
			$apiCalls++;
			$users = array_merge($users, $response->users);
		}
	}
	
	$response = array('api_calls' => $apiCalls, 'users' => $users);
	return $response;
}

/**
 * Send debug message to admin user (in this case, userid = 7)
 *
 * @return int of message on db table or FALSE if error
 */
function zoom_send_debug_message($apiCalls = 0, $bytesKept = 0, $bytesDeleted = 0) {
	global $DB;
	//require_once($CFG->libdir.'/messagelib.php');
	
	$telluser = $DB->get_record('user', array('id'=>'7'));
	
	$messagehtml = "kept " . round($bytesKept / 1024 / 1024 / 1024, 2) . " GB of data, <br />deleted " . 
					round($bytesDeleted / 1024 / 1024 / 1024, 2) . " GB of data, <br />used $apiCalls api calls";
	
	$messagetext = html_to_text($messagehtml);
	mtrace("...message text set");
	
	$eventdata = new stdClass();
	
	$eventdata->component         = 'moodle';    // the component sending the message. Along with name this must exist in the table message_providers
	$eventdata->name              = 'notices';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
	$eventdata->userfrom          = $telluser;      // user object
	$eventdata->userto            = $telluser;        // user object
	$eventdata->subject           = 'Zoom recordings deleted';   // very short one-line subject
	$eventdata->fullmessage       = $messagetext;      // raw text
	$eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
	$eventdata->fullmessagehtml   = $messagehtml;      // html rendered version
	$eventdata->smallmessage      = '';             // useful for plugins like sms or twitter
	
	$result = message_send($eventdata);
	
	return $result;
}

/**
 * Make a zoom api call expecting multiple pages,
 * page_number and page_size will be set if not in data
 *
 * @param string $url
 * @param array $data optional array
 * @return object $response or FALSE if error
 *     array $response keys should match normal keys from api call,
 *     with addition of $response->api_calls counting number of calls made
 */
function make_call_multipage ($url, $data = array()) {
	$apiCalls = 0;
	
	if (!$data['page_number']) {
		$data['page_number'] = 1;
	}
	$p = $data['page_number'];
	if (!$data['page_size']) {
		$data['page_size'] = 30;
	}
	$ps = $data['page_size'];
	
	$service = new mod_zoom_webservice();
	
	$apiCalls++;
	try {
		$response = $service->make_call($url, $data);
	} catch (moodle_exception $e) {
		return false;
	}
	
	$pp = $response->page_count;
	$listdata = array();
	while ($pp > $p) {
		$listdata = array_merge($listdata, end($response));
		$data['page_number'] = $p++;
		$apiCalls++;
		try {
			$response = $service->make_call($url, $data);
		} catch (moodle_exception $e) {
			return false;
		}
	}
	$listdata = array_merge($listdata, end($response));
	$key = key($response);
	$response->$key = $listdata;
	$response->api_calls = $apiCalls;
	
	return $response;
}