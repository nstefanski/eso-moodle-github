<?php 

require_once('../config.php');

require_login();

global $DB;

$u = $_GET["u"] ? $_GET["u"] : '';
$c = $_GET["c"] ? $_GET["c"] : '';
$total = 6;
$fill = 'black';

if(is_numeric($u) && is_numeric($c)){
	$sql="SELECT COUNT(*) AS ct 
		FROM {course_modules_completion} cmc 
			JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id 
		WHERE cmc.userid = $u 
			AND cm.course = $c 
			AND cmc.completionstate > 0 
			AND cm.idnumber LIKE '%prac%' ";
	$pracs = $DB->get_record_sql($sql)->ct;
	
	$sql = "SELECT ue.id, ue.status, ue.enrolid, ue.timestart, ue.timeend, e.expirythreshold 
			FROM {user_enrolments} ue 
				JOIN {enrol} e ON ue.enrolid = e.id 
			WHERE ue.userid = $u 
				AND e.courseid = $c 
				AND e.status = 0 ";
	$startdate = $DB->get_record_sql($sql)->timestart;

	if($pracs < $total) {
		if(!$pracs){
			$sql="SELECT COUNT(*) AS ct 
				FROM {course_modules_completion} cmc 
					JOIN {course_modules} cm ON cmc.coursemoduleid = cm.id 
					JOIN {modules} m ON cm.module = m.id 
				WHERE cmc.userid = $u 
					AND cm.course = $c 
					AND cmc.completionstate > 0 
					AND m.name = 'scorm' ";
			$scorms = $DB->get_record_sql($sql)->ct;
			$prog = ($scorms / 4) / $total * 100;
		} else {
			$prog = $pracs / $total * 100;
		}
		$topstring = round($prog,1)."% complete";
		$rem = $total - $pracs;
		$bottomstring = "$rem required practical activities remaining";
		//$startdate = $DB->get_record('course', array('id'=>$c))->startdate;
		//$enddate = $startdate + (6 * 7 * 24 * 60 * 60);
		//$ot = $enddate + (2 * 7 * 24 * 60 * 60);
		$expected = new DateTime();
		$expected->setTimestamp($startdate); 
		$expected->add(date_interval_create_from_date_string(($pracs+1).' months')); 
		$expected = $expected->getTimestamp();
		
		$now = time();
		
		$fill = '#6f1200';
		if($now < $expected) {
			$fill = '#678F18';
		} elseif($now < ($expected + (15*24*60*60))) {
			$fill = '#DDBD53';
		}
	} else {
		$prog = 100;
		$topstring = "Congratulations!";
		$bottomstring = "You've finished all the required practical activities!";
		$randemoji = array('bee','fish_cake','crown','rocket','moyai','birthday',);
		$i = rand(0,5);
		$bottomstring .= '<br/><span class="emojis"><i class="em em-tada"></i> <i class="em em-clap"></i> <i class="em em-'.$randemoji[$i].'"></i></span>';
		$fill = '#678F18';
	}
	
	?>
<html>
	<head>
		<link href="https://afeld.github.io/emoji-css/emoji.css" rel="stylesheet">
		<style>
		.extprogress {
			width:100%;
			height:25px;
			border:1px solid <?php echo $fill; ?>;
			position:relative;
		}
		.extprogress:after {
			content:'\A';
			position:absolute;
			background:<?php echo $fill; ?>;
			top:0; bottom:0;
			left:0; 
			width: <?php echo $prog; ?>%;
			-webkit-animation: filler 2s ease-in-out;
			-moz-animation: filler 2s ease-in-out;
			animation: filler 2s ease-in-out;
		}

		@-webkit-keyframes filler { 0% { width:0; } }
		@-moz-keyframes filler { 0% { width:0; } }
		@keyframes filler { 0% { width:0; } }
		
		.topstring { 
			font-size: large;
			text-transform: uppercase;
			text-align: center;
		}
		.bottomstring {
			text-align: center;
		}
		span.emojis {
			display: inline-block;
			height: 1.55em;
			min-width: 1px;
		}
		.extprogress .percent {
			left: <?php echo $prog; ?>%;
			position: absolute;
			margin-left: 5px;
		}
		</style>
	</head>
	<body>
		<div id="extblock">
			<p class="topstring"><?php echo $topstring; ?></p>
			<div class="extprogress">
				<span class="percent"><?php /*echo round($prog,1);*/ ?></span>
			</div>
			<p class="bottomstring"><?php echo $bottomstring; ?></p>
		</div>
	</body>
	<?php
}