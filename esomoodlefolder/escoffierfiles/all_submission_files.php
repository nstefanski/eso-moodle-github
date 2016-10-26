<?php

require_once('../config.php');

require_login();

if( is_siteadmin() ) {
	global $DB;

	$c = $_GET["c"] ? $_GET["c"] : 0;
	$cm = $_GET["cm"] ? $_GET["cm"] : 0;

	if ($c || $cm) {
		if($cm){
			$filter = "AND cm.id = $cm ";
			$course_module = $DB->get_record('course_modules', array('id' => $cm));
			if($course_module->id) {
				$module_type = $DB->get_record('modules', array('id' => $course_module->module));
				$module = $DB->get_record($module_type->name, array('id' => $course_module->instance));
				$title = ucfirst($module_type->name) . ' ' . $module->name;
			} else {
				$title = "No such course module";
			}
			
		} else {
			$filter = "AND cm.course = $c ";
			$course = $DB->get_record('course', array('id' => $c));
			$title = $course->fullname;
		}

		$sql = "SELECT f.id, f.author, 
					CASE WHEN cm.module = 1 THEN 'assign' 
						 WHEN cm.module = 9 THEN 'forum' 
						 ELSE 'err' END AS activity_type, 
					CASE WHEN cm.module = 1 
							THEN (SELECT a.name FROM {assign} a 
								WHERE a.id = cm.instance )
						 WHEN cm.module = 9 
							THEN (SELECT a.name FROM {forum} a 
								WHERE a.id = cm.instance ) 
						 ELSE 'err' END AS activity_name, 
					CONCAT('/pluginfile.php/', f.contextid, '/', f.component, '/', 
							f.filearea, '/', f.itemid, f.filepath, f.filename) AS filepath, 
					f.mimetype, f.filesize 
				FROM {files} f
					JOIN {context} cx ON f.contextid = cx.id 
						JOIN {course_modules} cm ON cx.instanceid = cm.id 
				WHERE cx.contextlevel = 70 
					AND (cm.module = 1 OR cm.module = 9)
					AND (f.component LIKE 'assignsubmission_%' OR f.component = 'mod_forum')
					AND f.filename <> '.'
					$filter ";

		$records = $DB->get_records_sql($sql);
		
		?>
	<html>
		<head>
			<style>
				img {
					max-width: 20px;
					float: left;
				}
			</style>
		</head>
		<body>
		<?php 
		
		echo "<h2>$title</h2>";
		
		$links = '';
		$imgs = '';
		$linkCount = 0;
		$imgCount = 0;
		
		foreach($records as $record) {
			$alt = urlencode($record->author) . ' ' . $record->activity_type . '_' . urlencode($record->activity_name);
			if(substr($record->mimetype, 0, 5) == 'image') {
				$imgs .= '<img src="'.$record->filepath.'" alt="'.$alt.'"/>';
				$imgCount++;
			} else {
				$links .= '<a href="'.$record->filepath.'">'.$alt.'</a><br />';
				$linkCount++;
			}
		}
		
		echo "<p>$linkCount links:</p>$links<hr /><p>$imgCount images:</p>$imgs";
		//print_R($records);
		?>
		</body>
	</html>
		<?php
	} else {
		echo 'Course id or course module id required';
	}
}