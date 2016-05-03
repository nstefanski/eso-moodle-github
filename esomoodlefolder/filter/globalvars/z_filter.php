<?php
class filter_globalvars extends moodle_text_filter {
	public function filter($text, array $options = array()) {
		global $USER, $COURSE, $PAGE;
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
			
			//$text = str_replace('%%config_wwwroot%%', $CFG->wwwroot, $text);
		}
		
		return $text;
	}
}
?>