<?php

require_once('../config.php');

require_login();

global $DB, $CFG, $USER;

require_once($CFG->dirroot.'/blocks/dedication/dedication_lib.php');

$userid = $_GET["userid"];

//require capability
$systemcontext = context_system::instance();
require_capability('report/outline:view', $systemcontext);

if ($USER->id != $userid) {  
	$user = $DB->get_record('user', array('id' => $userid), 'id,firstname,lastname');
?>
<html>
<head>
	<title><?php echo $user->firstname . ' ' . $user->lastname; ?></title>
	<style>
	dt h3 {
		border: 1px solid black;
		padding: 2px;
		color: white;
		background-color: #ac151e;
	}
	iframe {
		width: 100%;
		height: 80vh;
	}
	table {
		border-collapse: collapse;
	}

	table, th, td {
		border: 1px solid black;
		padding: 4px;
	}
	div {
		display: inline-block;
	}
	.course {
		width: 18%;
	}
	.grade {
		width: 9%;
		text-align: right;
	}
	.access {
		width: 32%;
		text-align: center;
	}
	.dedication {
		width: 32%;
	}
	img.icon {
		height: 24px;
		width: 24px;
	}
	img {
		max-height: 72px;
	}
	dt.collapse-all {
		z-index: 10000; 
		position: fixed; 
		right: 2%; 
		top: 20px; 
		background-color: #f2e18b; 
		border: 1px solid black; 
		padding: 4px;
	}
	</style>
</head>
<body>
	<h1><?php echo $user->firstname . ' ' . $user->lastname; ?></h1>
	<dl class="accordion">
		<dt class="collapse-all">Collapse All</dt>
		<dd></dd>
		<dt><h3>Student Contact History</h3></dt>
		<dd><table>
			<tr>
				<th>Index</th>
				<th>Instructor Name</th>
				<th>Contact Type</th>
				<th>Call Reason</th>
				<th>Red Flags</th>
				<th>Notes</th>
				<th>Time Created</th>
			</tr>
<?php
	$sql = "SELECT contact.id, 
				CONCAT(inst.firstname,' ',inst.lastname) AS instructor_name, contact.contact_type, contact.call_reason, 
				(SELECT GROUP_CONCAT(flags.red_flag SEPARATOR ', ') 
					FROM {block_contact_student_flags} flags 
					WHERE flags.contact_id = contact.id ) AS red_flags, 
				contact.notes, FROM_UNIXTIME(contact.time_created) AS time_created 
			FROM {block_contact_student} contact 
				JOIN {user} stu ON contact.student_id = stu.id 
				JOIN {user} inst ON contact.instructor_id = inst.id 
			WHERE stu.id = $userid ";
	$records = $DB->get_records_sql($sql);
	//print_r($records);
	
	foreach ($records as $record) {
		echo "<tr>";
		foreach ($record as $cell) {
			echo "<td>$cell</td>";
		}
		echo "</tr>";
	}
?>
		</table>
		<br /><br />
		<table>
			<tr>
				<th>Index</th>
				<th>Instructor Name</th>
				<th>Message</th>
				<th>Time Created</th>
			</tr>
<?php
	$sql = "SELECT m.id, CONCAT(inst.firstname,' ',inst.lastname) AS instructor_name, 
				m.smallmessage, FROM_UNIXTIME(m.timecreated) AS time_sent 
			FROM {message} m 
				JOIN {user} inst ON m.useridfrom = inst.id 
			WHERE inst.firstname LIKE 'Chef %' 
				AND m.useridto = $userid 
				AND m.notification = 0 ";
	$message = $DB->get_records_sql($sql);
	
	$sql = "SELECT m.id, CONCAT(inst.firstname,' ',inst.lastname) AS instructor_name, 
				m.smallmessage, FROM_UNIXTIME(m.timecreated) AS time_sent 
			FROM {message_read} m 
				JOIN {user} inst ON m.useridfrom = inst.id 
			WHERE inst.firstname LIKE 'Chef %' 
				AND m.useridto = $userid 
				AND m.notification = 0 ";
	$message_read = $DB->get_records_sql($sql);
	
	$all_message = array_merge($message,$message_read);
	
	foreach ($all_message as $record) {
		echo "<tr>";
		foreach ($record as $cell) {
			echo "<td>".strip_tags($cell)."</td>";
		}
		echo "</tr>";
	}
?>
		</table></dd>
<?php
	$sql = "SELECT ra.id, ra.roleid, c.id AS courseid, c.shortname, c.startdate, ROUND(gg.finalgrade,2) AS finalgrade, ula.timeaccess 
			FROM {role_assignments} ra 
				JOIN {context} cx ON ra.contextid = cx.id
				JOIN {course} c ON cx.instanceid = c.id AND cx.contextlevel = '50' 
				JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemtype = 'course' 
				LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id AND gg.userid = ra.userid 
				LEFT JOIN {user_lastaccess} ula ON ula.userid = ra.userid AND ula.courseid = c.id 
			WHERE ra.userid = $userid 
				AND c.id > 3 ";
	$records = $DB->get_records_sql($sql);
	//print_r($records);
	
	foreach ($records as $record) {
		$coursecontext = context_course::instance($record->courseid);
		if (has_capability('moodle/grade:viewall', $coursecontext)) {
			$params = "id=$userid&mode=complete&course=$record->courseid";
			
			//print_r($record->startdate);
			//print_r(time());
			
			$course = $DB->get_record('course', array('id' => $record->courseid), 'id,shortname');
			//$user = $DB->get_record('user', array('id' => $userid), 'id');
			$dm = new block_dedication_manager($course, $record->startdate, time(), 1080);
			$dedicationtime = round($dm->get_user_dedication($user, true)/3600,1); //minutes
			//$dedicationtime /= 3600;//hours
			?>
			<dt>
				<h3>
					<div class="course"><?php echo $record->shortname; ?></div>
					<div class="grade"><?php echo $record->finalgrade ? $record->finalgrade : '--' ; ?>%</div>
					<div class="access">Last access on <?php echo $record->timeaccess ? date('l, n/j/y', $record->timeaccess) : 'NEVER'; ?></div>
					<div class="dedication"><?php echo $dedicationtime; ?> hours in course since start</div>
				</h3>
			</dt>
			<dd>
				<div id="div-<?php echo $record->id; ?>"></div>
				<iframe id="frame-<?php echo $record->id; ?>" onload="getFrameContents(<?php echo $record->id; ?>)" 
					src="http://my.escoffieronline.com/report/outline/user.php?<?php echo $params; ?>">Loading...</iframe>
			</dd>
			<?php
		}
	}
?>
	</dl>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://my.escoffieronline.com/escoffierfiles/js/accordion.min.js"></script>
	<script type="text/javascript">
		$(function () {
			$('.accordion').accordion({
				open: false, // First List Open, Default Value: false
				autoStart: false, // Auto Start, Default Value: false
				onHoverActive: false, // On Hover Active, Default Value: false
				slideInterval: 3000, // Expression at specified intervals (in milliseconds) Default Value: 3000
				duration: 'slow', // The default duration is slow. The strings 'fast' and 'slow' can be supplied to indicate durations of 200 and 600 milliseconds, respectively.
				easing: 'swing', //  An easing function specifies the speed at which the animation progresses at different points within the animation.
				complete: function () { console.log('Complete Event'); } //If supplied, the complete callback function is fired once the accordion is complete.
			});
		});
	</script>
	<script type="text/javascript">
		function getFrameContents(frameId) {
			var frame = document.getElementById("frame-" + frameId);
			//if (frame.contentDocument.getElementById("block-region-side-pre")) {
				frame.contentDocument.getElementsByTagName("header")[0].style.display = "none";
				frame.contentDocument.getElementById("page-navbar").style.display = "none";
				frame.contentDocument.getElementById("region-main").getElementsByTagName("h1")[0].style.display = "none";
				frame.contentDocument.getElementById("block-region-side-pre").style.display = "none";
				frame.contentDocument.getElementsByTagName("footer")[0].style.display = "none";
				//var pageContent = frame.contentDocument.getElementById("region-main");
				//var targetDiv = document.getElementById("div-" + frameId);
				//targetDiv.appendChild(pageContent);
				//frame.style.display = "none";
			//} else {
			//	console.log("wait");
			//	window.setTimeout(function(){ alertFunc(frameId); }, 1000);
			//}
		}
	</script>
</body>
</html>
<?php
}
