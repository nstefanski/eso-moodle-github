<?php
/*
 *	After this is run once, check all dates are correct with Dates reports:
 *	http://my.escoffieronline.com/blocks/configurable_reports/viewreport.php?id=81
 *	http://my.escoffieronline.com/blocks/configurable_reports/viewreport.php?id=82
 */

require_once('../config.php');

if( is_siteadmin() ) {
	global $DB;
	
	$success = 0;
	$fail = 0;
	$fails = array();
	$noupdate = 0;
	$noupdates = array();
	date_default_timezone_set('America/Chicago');
	
	$records = $DB->get_records_sql("SELECT a.id, a.allowsubmissionsfromdate, a.duedate, a.cutoffdate, a.name, a.course, c.shortname, c.startdate 
									FROM {assign} a JOIN {course} c ON a.course = c.id 
									JOIN {course_categories} cc ON c.category = cc.id 
									WHERE cc.path LIKE '/42/43%'");
		
	foreach ($records as $record) {
		foreach ($record as $key => $value) {
			if ($key == 'allowsubmissionsfromdate' || $key == 'duedate' || $key == 'cutoffdate') {
				$offset = date('I', $record->startdate)*1 - date('I', $value)*1;
				if ($offset != 0 && date('G', $value)*1 != 0) {
					//update time
					$dataobject = new stdclass;
					$dataobject->id = $record->id;
					$dataobject->{$key} = $value + ($offset * 3600); //seconds
					
					//echo "$record->shortname / $record->name <br /> $key <br />";
					//print_r(date('M j Y G:i:s', $record->{$key}));			echo '<br />';
					//print_r(date('M j Y G:i:s', $dataobject->{$key}));		echo '<hr />';
					
					if ($DB->update_record('assign', $dataobject, TRUE)) {
						$success++;
						$successes[] = (object) array('type' => $key, 'record' => $record);
					} else {
						$fail++;
						//$fails[] = $record;
						$fails[] = (object) array('type' => $key, 'record' => $record);
					}
				//}
				} else {
					$noupdate++;
					//$noupdates[] = $record;
					$noupdates[] = (object) array('type' => $key, 'record' => $record);
				}
			}
		}
	}
	
	$quiz_records = $DB->get_records_sql("SELECT q.id, q.timeopen, q.timeclose, q.name, q.course, c.shortname, c.startdate 
										FROM {quiz} q JOIN {course} c ON q.course = c.id 
										JOIN {course_categories} cc ON c.category = cc.id 
										WHERE cc.path LIKE '/42/43%'");
	
	foreach ($quiz_records as $record) {
		foreach ($record as $key => $value) {
			if ($key == 'timeopen' || $key == 'timeclose') {
				$offset = date('I', $record->startdate)*1 - date('I', $value)*1;
				if ($offset != 0 && date('G', $value)*1 != 0) {
					//update time
					$dataobject = new stdclass;
					$dataobject->id = $record->id;
					$dataobject->{$key} = $value + ($offset * 3600); //seconds
					
					//echo "$record->shortname / $record->name <br /> $key <br />";
					//print_r(date('M j Y G:i:s', $record->{$key}));			echo '<br />';
					//print_r(date('M j Y G:i:s', $dataobject->{$key}));		echo '<hr />';
					
					if ($DB->update_record('quiz', $dataobject, TRUE)) {
						$success++;
						$successes[] = (object) array('type' => $key, 'record' => $record);
					} else {
						$fail++;
						//$fails[] = $record;
						$fails[] = (object) array('type' => $key, 'record' => $record);
					}
				//}
				} else {
					$noupdate++;
					//$noupdates[] = $record;
					$noupdates[] = (object) array('type' => $key, 'record' => $record);
				}
			}
		}
	}
	
	echo "Successful updates: $success <br />";
	print_r($successes);
	echo "<hr />Failed updates: $fail <br />";
	print_r($fails);
	echo "<hr />No update attempted: $noupdate <br />";
	print_r($noupdates);
} else {
	//do nothing
}

?>