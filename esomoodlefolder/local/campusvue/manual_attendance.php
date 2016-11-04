<?php

require_once('../../config.php');

require_login();

if( is_siteadmin() ) {

global $DB, $CFG;
require_once($CFG->dirroot.'/local/campusvue/lib.php');

$mindate = (empty($_GET['mindate'])) ? date_format_string(time()-24*60*60, '%Y-%m-%d') : $_GET['mindate'];
$maxdate = (empty($_GET['maxdate'])) ? date_format_string(time(), '%Y-%m-%d') : $_GET['maxdate'];
$action = (empty($_GET['action'])) ? '' : $_GET['action'];
$minTime = (empty($_GET['minTime'])) ? NULL : $_GET['minTime'];
$maxTime = (empty($_GET['maxTime'])) ? NULL : $_GET['maxTime'];
$course = (empty($_GET['course'])) ? NULL : $_GET['course'];

if ($mindate && !$minTime) {
	list($y, $m, $d) = explode("-", $mindate);
	$minTime = make_timestamp($y, $m, $d);
}
if ($maxdate && !$maxTime) {
	list($y, $m, $d) = explode("-", $maxdate);
	$maxTime = make_timestamp($y, $m, $d);
}
if (($minTime >= $maxTime) || ( ($maxTime - $minTime) > (60*60*24*7) )) {
	$validRange = FALSE;
} else {
	$validRange = TRUE;
}
?>
<h1>Manual Attendance</h1>
<?php
if ($action == 'Run') {
	if ($validRange) {
		echo "<p>Ran attendance for period $minTime to $maxTime ...</p>";
		$token = cvGetToken();
		$ua = updateAttendance($maxTime, $minTime, $token); //tk un-comment this to run
		if ($ua) {
			if ($ua->Attendances) {
				$msgArray = $ua->Attendances->PostAttendanceOutMsg;
				$msgs = count($msgArray);
				$errs = 0;
				$exErr = 0;
				$vaErr = 0;
				foreach ($msgArray as $outMsg) {
					if ($outMsg->MessageStatus == 'FailedExecution') {
						$errs++;
						$exErr++;
					} elseif ($outMsg->MessageStatus == 'FailedValidation') {
						$errs++;
						$vaErr++;
					} elseif (!empty($outMsg->MessageErrorCode)) {
						$errs++;
					}
				}
				echo "<p>... sent $msgs Attendance Messages with $errs errors ...</p>";
				if ($errs) {
					echo "<p>... $exErr Failed Execution ...</p>";
					echo "<p>... $vaErr Failed Validation ...</p>";
					$otErr = $errs - $exErr - $vaErr;
					echo "<p>... $otErr Unknown Errors ...</p>";
				}
			} else {
				echo "<p>... no Attendance Messages to send ...</p>";
			}
		} else {
			echo "<p>... could not send Attendance Messages ...</p>";
		}
		echo "<p>... run again?</p>";
	} else {
		echo "<p>Could not run attendance for period $minTime to $maxTime ...</p>";
		?>
		<p>Invalid range: "To" date must be after "From" date, and range must be no greater than seven days.</p>
		<p>Fix range and try again.</p>
		<?php
	}
}
?>
<form action="manual_attendance.php" method="get" id="instructorreport">
	<label for="mindate">From:</label>
	<input type="date" id="mindate" name="mindate" value="<?php echo $mindate; ?>">
	<label for="maxdate">To:</label>
	<input type="date" id="maxdate" name="maxdate" value="<?php echo $maxdate; ?>">
	<input type="submit" name="action" value="Run">
</form>
<?php
}