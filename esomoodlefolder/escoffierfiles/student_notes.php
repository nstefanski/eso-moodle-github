<?php 

require_once('../config.php');

require_login();

$c = $_GET["c"] ? $_GET["c"] : 0;

if($c && is_numeric($c)){
	$context = context_course::instance($c);
	if(has_capability('moodle/notes:view', $context)){
		global $DB;
		$sql = "SELECT ra.userid, CONCAT(u.firstname,' ',u.lastname) AS name, 
					(SELECT COUNT(*) 
						FROM {post} p 
						WHERE p.userid = ra.userid 
							AND p.module = 'notes' ) AS notes 
				FROM {role_assignments} ra 
					JOIN {user} u ON ra.userid = u.id 
					JOIN {context} cx ON ra.contextid = cx.id 
						JOIN {course} c ON cx.instanceid = c.id AND cx.contextlevel = 50 /* course */ 
				WHERE ra.roleid = 5 /* student */ 
					AND (SELECT COUNT(*) 
						FROM {post} p 
						WHERE p.userid = ra.userid 
							AND p.module = 'notes' ) > 0
					AND c.id = $c ";
		try {
			$stus = $DB->get_records_sql($sql);
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
		?>
		<div id="notesblock">
		<?php
	
		if($stus){
			echo "<ol>";
			foreach($stus as $stu){
				echo '<li><a target="_blank" href="/notes/index.php?user='.
					$stu->userid.'&course='.$c.'">'.$stu->name.' ('.$stu->notes.')</a></li>';
			}
			echo "</ol>";
		} else {
			echo "No students to display";
		}
		
		?>
		</div>
		<?php
	} else {
		//bad permissions, display nothing
	}
}