<?php 

require_once('../config.php');

require_login();

global $DB;

$u = $_GET["u"] ? $_GET["u"] : 0;
$c = $_GET["c"] ? $_GET["c"] : 0;
$fill = 'black';

if($u && $c){
	$sql="SELECT gg.finalgrade, 
				(SELECT COUNT(*) 
					FROM mdl_attendance_log al 
						JOIN mdl_attendance_statuses ast ON al.statusid = ast.id 
						JOIN mdl_attendance_sessions ase ON al.sessionid = ase.id 
							JOIN mdl_attendance a ON ase.attendanceid = a.id 
					WHERE al.studentid = gg.userid 
						AND ast.description = 'Late' 
						AND a.course = gi.courseid ) AS late, 
				(SELECT COUNT(*) 
					FROM mdl_attendance_log al 
						JOIN mdl_attendance_statuses ast ON al.statusid = ast.id 
						JOIN mdl_attendance_sessions ase ON al.sessionid = ase.id 
							JOIN mdl_attendance a ON ase.attendanceid = a.id 
					WHERE al.studentid = gg.userid 
						AND ast.description = 'Absent' 
						AND a.course = gi.courseid ) AS absent 
			FROM mdl_grade_grades gg 
				JOIN mdl_grade_items gi ON gg.itemid = gi.id 
			WHERE gg.userid = $u 
				AND gi.itemtype = 'course' 
				AND gi.courseid = $c ";
	try {			
		$stats = $DB->get_record_sql($sql);
	} catch(Exception $e) {
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	if($stats){
		if($stats->finalgrade < 60){
			$gpGrade = "#6f1200";
		}elseif($stats->finalgrade < 80){
			$gpGrade = "#bb8c15";
		}else{
			$gpGrade = "#547713";
		}
		
		if($stats->absent > 2){
			$gpAbsent = "#6f1200";
		}elseif($stats->absent > 0){
			$gpAbsent = "#bb8c15";
		}else{
			$gpAbsent = "#547713";
		}
		
		if($stats->late > 2){
			$gpLate = "#6f1200";
		}elseif($stats->late > 0){
			$gpLate = "#bb8c15";
		}else{
			$gpLate = "#547713";
		}
	?>
<html>
	<head>
		<link href="https://afeld.github.io/emoji-css/emoji.css" rel="stylesheet">
		<style>
		@-webkit-keyframes filler { 0% { width:0; } }
		@-moz-keyframes filler { 0% { width:0; } }
		@keyframes filler { 0% { width:0; } }
		
		.topstring { 
			font-size: large;
			/*text-transform: uppercase;
			text-align: center;*/
		}
		.bottomstring {
			text-align: center;
		}
		span.emojis {
			display: inline-block;
			height: 1.55em;
			min-width: 1px;
		}
		.gpGrade {
			color: <?php echo $gpGrade; ?>;
		}
		.gpAbsent {
			color: <?php echo $gpAbsent; ?>;
		}
		.gpLate {
			color: <?php echo $gpLate; ?>;
		}
		#ground_prog_warning {
			font-size: small;
		}
		</style>
	</head>
	<body>
		<div id="ground_prog">
			<ul class="topstring">
				<li class="gpGrade">My Grade: <strong><?php echo round($stats->finalgrade,1); ?>%</strong></li>
				<li class="gpAbsent">Absences: <strong><?php echo $stats->absent; ?></strong></li>
				<li class="gpLate">Late: <strong><?php echo $stats->late; ?></strong></li>
			</ul>
			<div id="ground_prog_warning"><?php 
			if($stats->late > 0 || $stats->absent > 0){
				echo 'This is an attendance indicator.  Be aware that your attendance level is measured by total minutes missed each term. See the Registrar for a true accounting of your attendance level and corresponding standing.';
			}
			?></div>
		</div>
	</body>
	<?php
	}
}