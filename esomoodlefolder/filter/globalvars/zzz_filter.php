<?php
class filter_globalvars extends moodle_text_filter {
	public function filter($text, array $options = array()) {
		global $USER, $COURSE, $PAGE, $DB;
		//global $CFG;
		
		$pos = strpos($text,'%%');
	
		if($pos === false) {
			//string not found in text, no need to run filters
		} else {
			$text = str_replace('%%user_id%%', $USER->id, $text);
			$text = str_replace('%%user_firstname%%', $USER->firstname, $text);
			$text = str_replace('%%user_lastname%%', $USER->lastname, $text);
			$text = str_replace('%%user_fullname%%', $USER->firstname.' '.$USER->lastname, $text);
			$text = str_replace('%%user_email%%', $USER->email, $text);
			$text = str_replace('%%user_cvueid%%', $USER->profile['cvueid'], $text);
			$text = str_replace('%%user_cvuestartdate%%', date('F j, Y', $USER->profile['startdate']), $text);
			
			$text = str_replace('%%course_id%%', $COURSE->id, $text);
			$text = str_replace('%%course_shortname%%', $COURSE->shortname, $text);
			$text = str_replace('%%course_fullname%%', $COURSE->fullname, $text);
			$text = str_replace('%%course_startdate%%', date('F j, Y', $COURSE->startdate), $text);
			$text = str_replace('%%course_unixstartdate%%', $COURSE->startdate, $text);
			
			$text = str_replace('%%page_title%%', $PAGE->title, $text);
			$text = str_replace('%%page_theme%%', $PAGE->theme->name, $text);
			$text = str_replace('%%page_device%%', $PAGE->devicetypeinuse, $text);
			
			if ($PAGE->cm) {
				$text = str_replace('%%module_name%%', $PAGE->cm->name, $text);
				$text = str_replace('%%module_week%%', $PAGE->cm->sectionnum, $text);
			}
			
			if ( ($PAGE->docspath == 'user/profile' || $PAGE->pagetype == 'user-profile') && $_GET["id"]) {
				$userview = $DB->get_record('user', array('id'=>$_GET["id"]), 'id,firstname,lastname,email');
				$text = str_replace('%%userview_id%%', $userview->id, $text);
				$text = str_replace('%%userview_firstname%%', $userview->firstname, $text);
				$text = str_replace('%%userview_lastname%%', $userview->lastname, $text);
				$text = str_replace('%%userview_fullname%%', $userview->firstname.' '.$userview->lastname, $text);
				$text = str_replace('%%userview_email%%', $userview->email, $text);
				$userview_cvueid = $DB->get_record('user_info_data', array('userid'=>$_GET["id"],'fieldid'=>9), 'data');
				$text = str_replace('%%userview_cvueid%%', $userview_cvueid->data, $text);
				$userview_cvuestartdate = $DB->get_record('user_info_data', array('userid'=>$_GET["id"],'fieldid'=>16), 'data');
				$text = str_replace('%%userview_cvuestartdate%%', date('F j, Y', $userview_cvuestartdate->data), $text);
			}
			
			//$text = str_replace('%%config_wwwroot%%', $CFG->wwwroot, $text);
		}
		
		return $text;
	}
}
?>