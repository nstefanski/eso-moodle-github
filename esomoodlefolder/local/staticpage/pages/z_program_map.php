<!DOCTYPE html>
<html>

<link href="http://my.escoffieronline.com/local/staticpage/pages/program_map.css" rel="stylesheet" type="text/css" />
<style>
	.wtf {
		display: none;
	}
	.programmap .active {
		background-color: #7EB31D;
	}
	.programmap .complete {
		background-color: #547713;
	}
</style>

<?php
require_once('../../../config.php');
require_login();
//echo $USER->profile['cvueid'];

$programCourses = array(
	'core1' => 'CE115',
	'core2' => 'CE125',
	'core3' => 'CE185',
	'core4' => 'CE165',
	'core5' => 'CE155',
	'core6' => 'CE225',
);

if (($USER->profile['campus'] == 'Boulder') || ($USER->profile['campus'] == 'Austin')) { 
	echo '<style>
		.programmap {
			display: none;
		}
	</style>';
} elseif ($USER->profile['campus'] == 'Boulder - Online') {
	$pracCourses = array(
		'prac1' => 'CA102',
		'prac2' => 'CA121',
		'prac3' => 'CA141',
		'prac4' => 'CA181',
		'prac5' => 'CA202',
		'ext' => 'EXT200',
	);
} elseif ($USER->profile['programtype'] == 'Certificate Program') {
	$pracCourses = array(
		'prac1' => 'BK101',
		'prac2' => 'BK121',
		'prac3' => 'BK161',
		'prac4' => 'BK141',
		'prac5' => 'BK201',
		'ext' => 'EXT201',
	);
} 

$programCourses += $pracCourses;
//print_r($programCourses);

$sql = "SELECT ra.id, ra.userid, CONCAT(u.firstname,' ',u.lastname) AS user, gi.courseid, c.shortname, cc.path, 
		FROM_UNIXTIME(ccom.timecompleted) AS course_complete, FROM_UNIXTIME(crcom.timecompleted) AS instructor_verified, gg.finalgrade AS final_grade 
		FROM {role_assignments} ra 
		JOIN {context} cx ON cx.id = ra.contextid 
		JOIN {course} c ON c.id = cx.instanceid 
		JOIN {course_categories} cc ON cc.id = c.category 
		JOIN {user} u ON u.id = ra.userid
		LEFT JOIN {course_completions} ccom ON c.id = ccom.course AND u.id = ccom.userid 
		JOIN {grade_items} gi ON gi.courseid = c.id AND gi.itemtype = 'course' 
		LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND ccom.userid = gg.userid 
		LEFT JOIN {course_completion_criteria} cr ON cr.course = ccom.course AND cr.criteriatype = 7 
		LEFT JOIN {course_completion_crit_compl} crcom ON crcom.criteriaid = cr.id AND crcom.userid = ccom.userid
		WHERE ccom.userid = $USER->id ";
$courseCompletions = $DB->get_records_sql($sql);

//print_r($courseCompletions);

foreach ($programCourses as $key => $courseCode) {
	foreach ($courseCompletions as $completion ) {
		if ( rtrim(substr($completion->shortname, 0, 6)) == $courseCode ) {
			if (($completion->course_complete || $completion->instructor_verified) && $completion->final_grade >= 60) {
				//echo "$completion->shortname is the same as $courseCode !!<br />";
				$$courseCode = 'complete';
				break;
			} elseif ( substr($completion->path, 0, 6) == '/42/43' ) {
				//echo "$completion->shortname c'est $courseCode !!<br />";
				$$courseCode = 'active';
			} elseif ( substr($completion->path, 0, 6) == '/42/44' && $completion->final_grade >= 60) {
				$$courseCode = 'complete';
			}
		}
	}
}

?>
<div class="programmap" id="program_map">
	<div class="term" id="term1">
		<div class="prac twelveweek <?php echo $$programCourses['prac1']; ?>" id="prac1">
			<p class="coursename"><?php echo $programCourses['prac1'] . '<br /> ' . $$programCourses['prac1']; ?></p>
		</div>
		<div class="corebiz <?php echo $$programCourses['core1']; ?>" id="core1">
			<p class="coursename"><?php echo $programCourses['core1'] . '<br /> ' . $$programCourses['core1']; ?></p>
		</div>
		<div class="spacer"></div>
		<div class="corebiz <?php echo $$programCourses['core2']; ?>" id="core2">
			<p class="coursename"><?php echo $programCourses['core2'] . '<br /> ' . $$programCourses['core2']; ?></p>
		</div>
	</div>
	<div class="term" id="term2">
		<div class="prac twelveweek <?php echo $$programCourses['prac2']; ?>" id="prac2">
			<p class="coursename"><?php echo $programCourses['prac2'] . '<br /> ' . $$programCourses['prac2']; ?></p>
		</div>
		<div class="corebiz <?php echo $$programCourses['core3']; ?>" id="core3">
			<p class="coursename"><?php echo $programCourses['core3'] . '<br /> ' . $$programCourses['core3']; ?></p>
		</div>
		<div class="spacer"></div>
		<div class="corebiz <?php echo $$programCourses['core4']; ?>" id="core4">
			<p class="coursename"><?php echo $programCourses['core4'] . '<br /> ' . $$programCourses['core4']; ?></p>
		</div>
	</div>
	<div class="term" id="term3">
		<div class="prac twelveweek <?php echo $$programCourses['prac3']; ?>" id="prac3">
			<p class="coursename"><?php echo $programCourses['prac3'] . '<br /> ' . $$programCourses['prac3']; ?></p>
		</div>
		<div class="corebiz <?php echo $$programCourses['core5']; ?>" id="core5">
			<p class="coursename"><?php echo $programCourses['core5'] . '<br /> ' . $$programCourses['core5']; ?></p>
		</div>
		<div class="spacer"></div>
		<div class="corebiz <?php echo $$programCourses['core6']; ?>" id="core6">
			<p class="coursename"><?php echo $programCourses['core6'] . '<br /> ' . $$programCourses['core6']; ?></p>
		</div>
	</div>
	<div class="courseblock" id="block7">
		<div class="prac sixweek <?php echo $$programCourses['prac4']; ?>" id="prac4">
			<p class="coursename"><?php echo $programCourses['prac4'] . '<br /> ' . $$programCourses['prac4']; ?></p>
		</div>
	</div>
	<div class="courseblock" id="block8">
		<div class="prac sixweek <?php echo $$programCourses['prac5']; ?>" id="prac5">
			<p class="coursename"><?php echo $programCourses['prac5'] . '<br /> ' . $$programCourses['prac5']; ?></p>
		</div>
	</div>
	<div class="courseblock" id="block9">
		<div class="prac sixweek <?php echo $$programCourses['ext']; ?>" id="ext">
			<p class="coursename"><?php echo $programCourses['ext'] . '<br /> ' . $$programCourses['ext']; ?></p>
		</div>
	</div>
</div>

</html>