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

function get_users_isdr ($mintime, $maxtime, $limit = 1080) {
	global $DB;
	$sql = "SELECT u.id AS md_id, 
				/*(SELECT uid.data FROM {user_info_data} uid 
					JOIN {user_info_field} uif ON uid.fieldid = uif.id AND uif.shortname = 'cvueid' 
					WHERE uid.userid = u.id ) AS CVue_id,*/ 
				u.idnumber AS CVue_id, 
				u.firstname, u.lastname, u.email, FROM_UNIXTIME(startdate.data) AS startdate, 
				CASE WHEN campus.data = 'Boulder - Online' THEN 'Culinary Arts' 
					WHEN campus.data = 'Online' THEN 'Baking and Pastry' 
					ELSE 'N/a' END AS program,
				status.data AS status, 
					
				ori_c.id AS oriid, core_c.id AS coreid, prac_c.id AS pracid, 
				'' AS ori_ded, '' AS core_ded, '' AS prac_ded, 
				0 AS ori_avses, 0 AS core_avses, 0 AS prac_avses 

			FROM {user} u 
				JOIN {user_info_data} startdate ON startdate.userid = u.id AND 
					(SELECT uif.shortname FROM {user_info_field} uif WHERE startdate.fieldid = uif.id) LIKE 'startdate' 
				JOIN {user_info_data} campus ON campus.userid = u.id AND 
					(SELECT uif.shortname FROM {user_info_field} uif WHERE campus.fieldid = uif.id) LIKE 'campus' 
				JOIN {user_info_data} programtype ON programtype.userid = u.id AND 
					(SELECT uif.shortname FROM {user_info_field} uif WHERE programtype.fieldid = uif.id) LIKE 'programtype' 
				JOIN {user_info_data} status ON status.userid = u.id AND 
					(SELECT uif.shortname FROM {user_info_field} uif WHERE status.fieldid = uif.id) LIKE 'Status' 
				LEFT JOIN {course} ori_c ON ori_c.id = (SELECT c.id
					FROM {user_enrolments} ue 
					JOIN {enrol} e ON ue.enrolid = e.id 
					JOIN {course} c ON e.courseid = c.id AND c.shortname LIKE '%orientation%' 
					WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)
				LEFT JOIN {course} core_c ON core_c.id = (SELECT c.id
					FROM {user_enrolments} ue 
					JOIN {enrol} e ON ue.enrolid = e.id 
					JOIN {course} c ON e.courseid = c.id AND c.shortname LIKE 'CE115%' 
					WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)
				LEFT JOIN {course} prac_c ON prac_c.id = (SELECT c.id
					FROM {user_enrolments} ue 
					JOIN {enrol} e ON ue.enrolid = e.id 
					JOIN {course} c ON e.courseid = c.id 
						AND (c.shortname LIKE 'CA102%' OR c.shortname LIKE 'BK101%') 
					WHERE ue.userid = u.id AND ue.status = 0 LIMIT 1)

			WHERE startdate.data IS NOT NULL 
				AND startdate.data > (UNIX_TIMESTAMP() - (2.5*7*24*60*60) ) 
				AND startdate.data < (UNIX_TIMESTAMP() + (6.5*7*24*60*60) ) 
				AND programtype.data = 'Certificate Program' 
				AND campus.data <> 'Boulder' 
				AND campus.data <> 'Austin' 
				/* AND u.suspended = 0 */ 
				AND u.deleted = 0 
				/* AND (SELECT uid.data FROM {user_info_data} uid 
					JOIN {user_info_field} uif ON uid.fieldid = uif.id AND uif.shortname = 'Status' 
					WHERE uid.userid = u.id ) = 'Active' */ 

			ORDER BY status.data, ori_c.id, core_c.id, prac_c.id ";
	try {
		$stus = $DB->get_records_sql($sql);
	} catch (moodle_exception $e) {
		return false;
	}
	
	foreach ($stus AS $stu) {
		try {
			$student = $DB->get_record('user', array('id' => $stu->md_id), 'id,firstname,lastname,email');
		} catch (moodle_exception $e) {
			return false;
		}
		
		if($stu->oriid) {
			if ($lastOri != $stu->oriid) {
				$lastOri = $stu->oriid;
				$oriCourse = $DB->get_record('course', array('id' => $stu->oriid), 'id,shortname');
				$oriDm = new block_dedication_manager($oriCourse, $mintime, $maxtime, $limit);
			}
			$ori_dedDetail = $oriDm->get_user_dedication($student, false);
			foreach ($ori_dedDetail AS $sesDetail) {
				$stu->ori_ded += $sesDetail->dedicationtime;
			}
			$stu->ori_ded /= 3600; //hours
			$stu->ori_avses = ($stu->ori_ded / count($ori_dedDetail) * 60); //mins
			//$stu->ori_ded = ($oriDm->get_user_dedication($student, true))/3600;//hours
		}
		if($stu->coreid) {
			if ($lastCore != $stu->coreid) {
				$lastCore = $stu->coreid;
				$coreCourse = $DB->get_record('course', array('id' => $stu->coreid), 'id,shortname');
				$coreDm = new block_dedication_manager($coreCourse, $mintime, $maxtime, $limit);
			}
			$core_dedDetail = $coreDm->get_user_dedication($student, false);
			foreach ($core_dedDetail AS $sesDetail) {
				$stu->core_ded += $sesDetail->dedicationtime;
			}
			$stu->core_ded /= 3600; //hours
			$stu->core_avses = ($stu->core_ded / count($core_dedDetail) * 60); //mins
		}
		if ($stu->pracid) {
			if ($lastPrac != $stu->pracid) {
				$lastPrac = $stu->pracid;
				$pracCourse = $DB->get_record('course', array('id' => $stu->pracid), 'id,shortname');
				$pracDm = new block_dedication_manager($pracCourse, $mintime, $maxtime, $limit);
			}
			$prac_dedDetail = $pracDm->get_user_dedication($student, false);
			foreach ($prac_dedDetail AS $sesDetail) {
				$stu->prac_ded += $sesDetail->dedicationtime;
			}
			$stu->prac_ded /= 3600; //hours
			$stu->prac_avses = ($stu->prac_ded / count($prac_dedDetail) * 60); //mins
		}
	}
	return $stus;
}

function write_csv ($rows, $filename = "csv_report", $headers = null) {
	global $CFG;
	
	$data = "";
	foreach ($rows AS $row) { 
		foreach ($row AS $column) {
			$data.= $column.",";
		}
		$data.= "\n";
	}

	$path = 'sheetreport';
	$csvfilename = $CFG->dirroot.'/'.$path.'/'.$filename.'.csv';
		
	$csv_handler = fopen ($csvfilename,'w');
	fwrite ($csv_handler,$data);
	fclose ($csv_handler);
	
	return $csvfilename;
}
