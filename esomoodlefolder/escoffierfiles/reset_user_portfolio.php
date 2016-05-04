<?php

/*
 *	RESET USER PORTFOLIO
 *	When a student first uploads a picture to the flickr portfolio from Moodle, the flickr account is saved and
 *	cannot be easily changed. This can cause potential problems, e.g. if a student is accidentally logged into the
 *	wrong flickr account.  The portfolio_instance_user table on the DB that seems to store the account information.
 *	For each userid, there are up to three records.  "['name'] => 'visible'" is created for every user and does not
 *	need to be altered. "['name'] => 'nsid'" is the id of the flickr account, used in flickr's urls.  The final
 *	record, "['name'] => 'authtoken'", corresponds to something else.  If we could obtain this value for a user's
 *	flickr account, we could simply change the values on this table, but since we don't know where to grab the
 *	authtoken, the easiest way is to simply delete this and the nsid, at which point the user will be able to
 *	register a new flickr account on their next upload.
 *	
 *	Only the admins listed in the array $adminIDs may run this script.  To reset a given user's portfolio, load the
 *	page followed by the proper get value, for instance "../reset_user_portfolio.php?userid=6".  Afterwords, have
 *	the user upload a photo to the correct flickr account.  The user may need to try 2 or 3 uploads before the
 *	process runs smoothly, deleting failed attempts using the portfolio log.
 */

	require_once('../config.php');
	global $DB;
	global $USER;

	// ID numbers of admin users
	$adminIDs = array(3,	//Triumph Admin
					4,	//Randall
					5,	//Daniel
					6,	//Nick (old acct)
					7,	//Nick
				);
	
	$allowReset = FALSE;
	
	foreach ($adminIDs as $adminID) {
		if ($USER->id == $adminID) {
			$allowReset = TRUE;
			echo 'Admin user verified<br>';
		}
	}
	
	if ($allowReset) {
	
		if ($_GET) {
	
			$userid = $_GET['userid'];

			$DB->delete_records('portfolio_instance_user', array('userid' => $userid,'name' => 'nsid'));
			$DB->delete_records('portfolio_instance_user', array('userid' => $userid,'name' => 'authtoken'));
	
			echo 'Portfolio reset for user no. ' . $userid;
	
/*			$records = $DB->get_records('portfolio_instance_user', array('userid' => $userid,'name' => 'nsid'));
	
			foreach ($records as $record) {
				print_r($record);
				echo '<br>';
			}
	
			$records = $DB->get_records('portfolio_instance_user', array('userid' => $userid,'name' => 'authtoken'));
		
			foreach ($records as $record) {
				print_r($record);
				echo '<br>';
			}*/
		}
	} else {
		echo 'Page unavailable.';
	}