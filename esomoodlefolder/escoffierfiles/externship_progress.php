<?php 

require_once('../config.php');

require_login();

global $DB;

$u = $_GET["u"] ? $_GET["u"] : 0;
$c = $_GET["c"] ? $_GET["c"] : 0;
$total = 150;
$fill = 'black';

if($u && $c){
	$sql="SELECT gg.finalgrade 
		FROM {grade_items} gi 
			JOIN {grade_grades} gg 
				ON gi.id = gg.itemid
		WHERE gi.courseid = $c 
			AND gg.userid = $u
			AND gi.idnumber = 'externship' ";
	$hours = $DB->get_record_sql($sql)->finalgrade;

	if($hours < $total) {
		$prog = $hours / $total * 100;
		$rem = $total - $hours;
		$topstring = "$rem hours remaining!";
		$startdate = $DB->get_record('course', array('id'=>$c))->startdate;
		$enddate = $startdate + (6 * 7 * 24 * 60 * 60);
		$ot = $enddate + (2 * 7 * 24 * 60 * 60);
		
		$now = time();
		if($now < $startdate) {
			$now = $startdate; 
		}
		
		if($now < $enddate) {
			$timeleft = $enddate - $now;
		} elseif ($now < $ot) {
			$timeleft = $ot - $now;
		}
		
		$fill = '#6f1200';
		
		if($timeleft) {
			$weeksleft = ceil($timeleft / (7 * 24 * 60 * 60));
			$remperweek = round($rem / $weeksleft, 1);
			$bottomstring = "That's $remperweek hours per week.";
			
			if($remperweek <= ($total/6)){
				$fill = '#678F18';
			} elseif($remperweek <= ($total/4)){
				$fill = '#DDBD53';
			}
			
			$daysleft = floor($timeleft / (24 * 60 * 60));
			$hoursleft = floor($timeleft / (60 * 60)) - ($daysleft * 24);
			
			if($daysleft > 1) {
				$bottomstring .= "<br/><br/>There are $daysleft days ";
				$bottomstring .= ($hoursleft > 1) ? "and $hoursleft hours " : "";
			} elseif($daysleft == 1) {
				$fill = '#6f1200';
				$bottomstring .= "<br/><br/>There is 1 day ";
				$bottomstring .= ($hoursleft > 1) ? "and $hoursleft hours " : "";
			} else {
				$fill = '#6f1200';
				if($hoursleft > 1) {
					$bottomstring .= "<br/><br/>There are $hoursleft hours ";
				} else {
					$bottomstring .= "<br/><br/>There is ";
					$bottomstring .= $hoursleft ? "" : "less than ";
					$bottomstring .= "1 hour ";
				}
			}
		
			if($now < $enddate) {
				$bottomstring .= "until the end of class.";
			} elseif ($now < $ot) {
				$bottomstring .= "left for late completion.";
			}
		}
	} else {
		$prog = 100;
		$topstring = "Congratulations!";
		$bottomstring = "You've finished all the required externship hours!";
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