<?php 

require_once('../config.php');

require_login();

global $DB;

$c = $_GET["c"] ? $_GET["c"] : 0;

if($c){
	$startdate = $DB->get_record('course', array('id'=>$c))->startdate;
	$enddate = $startdate + (5 * 7 * 24 * 60 * 60);//tk edited to week five end
	
	$now = time();
	
	if($now < $enddate) {
		$timeleft = $enddate - $now;
		
		$bottomstring = "";
		
		$daysleft = floor($timeleft / (24 * 60 * 60));
		$hoursleft = floor($timeleft / (60 * 60)) - ($daysleft * 24);
		
		if($daysleft > 1) {
			$bottomstring .= "<br/><br/>There are $daysleft days ";
			$bottomstring .= ($hoursleft > 1) ? "and $hoursleft hours " : "";
		} elseif($daysleft == 1) {
			$bottomstring .= "<br/><br/>There is 1 day ";
			$bottomstring .= ($hoursleft > 1) ? "and $hoursleft hours " : "";
		} else {
			if($hoursleft > 1) {
				$bottomstring .= "<br/><br/>There are $hoursleft hours ";
			} else {
				$bottomstring .= "<br/><br/>There is ";
				$bottomstring .= $hoursleft ? "" : "less than ";
				$bottomstring .= "1 hour ";
			}
		}
		
		$bottomstring .= "until externship paperwork is due.";//tk edited to paperwork due
	} else {
		$bottomstring = "Externship paperwork is due!";
	}
	$bottomstring .= "<br/><br/>You cannot begin earning externship hours until your externship course begins.";//tk added
		
	
	
	?>
<html>
	<head>
	</head>
	<body>
		<div id="extblock">
			<p class="bottomstring" style="text-align: center;"><?php echo $bottomstring; ?></p>
		</div>
	</body>
	<?php
}