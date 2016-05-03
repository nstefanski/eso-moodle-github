<?php

/**
 * This block needs to be reworked.
 * The new roles system does away with the concepts of rigid student and
 * teacher roles.
 */
class block_classmates extends block_base {
    function init() {
        $this->title = get_string('pluginname','block_classmates');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $USER, $CFG, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $numshowusers = 35; //Class size default tk
        if (isset($CFG->block_classmates_timetosee)) {
            $numshowusers = $CFG->block_classmates_timetosee;
        }

        //Calculate if we are in separate groups
        $isseparategroups = ($this->page->course->groupmode == SEPARATEGROUPS
                             && $this->page->course->groupmodeforce
                             && !has_capability('moodle/site:accessallgroups', $this->page->context));

        //Get the user current group
        $currentgroup = $isseparategroups ? groups_get_course_group($this->page->course) : NULL;

        $groupmembers = "";
        $groupselect  = "";
        $params = array();

        //Add this to the SQL to show only group users
        if ($currentgroup !== NULL) {
            $groupmembers = ", {groups_members} gm";
            $groupselect = "AND u.id = gm.userid AND gm.groupid = :currentgroup";
            $params['currentgroup'] = $currentgroup;
        }

        $userfields = user_picture::fields('u', array('username'));
        if ($this->page->course->id == SITEID or $this->page->context->contextlevel < CONTEXT_COURSE) {  // Site-level
            //do nothing
        } else {
            // Course level - show only enrolled users for now
            // TODO: add a new capability for viewing of all users (guests+enrolled+viewing)

            list($esqljoin, $eparams) = get_enrolled_sql($this->page->context);
            $params = array_merge($params, $eparams);
			
			$withdrawsql = "SELECT GROUP_CONCAT(r.shortname)
					          FROM {role_assignments} ra
							  JOIN {role} r ON ra.roleid = r.id 
							  JOIN {context} cx ON ra.contextid = cx.id
							 WHERE ra.userid = u.id 
							   AND cx.instanceid = :courseid
							   AND cx.contextlevel = 50";

			//tk changed SQL queries: removed all reference to user_logins, removed suspended users and users with "withdraw" role assignment
            $sql = "SELECT $userfields 
                      FROM {user} u $groupmembers 
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE u.deleted = 0
					   AND u.suspended = 0
					   AND ($withdrawsql) NOT LIKE '%withdraw%'
                           $groupselect
                  GROUP BY $userfields
                  ORDER BY firstname ASC";
				  
				  //print_r($sql);

           $csql = "SELECT COUNT(u.id)
                      FROM {user} u $groupmembers, {user} u
                      JOIN ($esqljoin) euj ON euj.id = u.id
                     WHERE u.deleted = 0
					   AND u.suspended = 0
					   AND ($withdrawsql) NOT LIKE '%withdraw%'
                           $groupselect";

            $params['courseid'] = $this->page->course->id;
        }

        //Calculate minutes
        //$minutes  = floor($timetoshowusers/60);

        // Verify if we can see the list of users, if not just print number of users
        if (!has_capability('block/classmates:viewlist', $this->page->context)) {
            if (!$usercount = $DB->count_records_sql($csql, $params)) {
                $usercount = get_string("none");
            }
            //$this->content->text = "<div class=\"info\"></div>";
            return $this->content;
        }

        if ($users = $DB->get_records_sql($sql, $params, 0, $numshowusers)) {   // Only show list up to max class size (link to additional users)
            foreach ($users as $user) {
				//if (has_capability('mod/assign:view', $this->page->context, $user)) { //tk added so withdraws don't show //tkTK no longer necessary, removed withdraws above
				$users[$user->id]->fullname = fullname($user);
				//}
            }
        } else {
            $users = array();
        }

        if (count($users) < $numshowusers) {
            $usercount = '';
        } else {
            $usercount = $DB->count_records_sql($csql, $params);
            $usercount = 'Total Classmates: '.$usercount.'<br><a href="'.$CFG->wwwroot.'/user/index.php?id='.$this->page->course->id.'">See All Classmates</a>';
        }

        $this->content->text = "<div class=\"info\">$usercount</div>";

        //Now, we have in users, the list of users to show
        if (!empty($users)) {
            //Accessibility: Don't want 'Alt' text for the user picture; DO want it for the envelope/message link (existing lang string).
            //Accessibility: Converted <div> to <ul>, inherit existing classes & styles.
            $this->content->text .= "<ul class='list'>\n";
            if (isloggedin() && has_capability('moodle/site:sendmessage', $this->page->context)
                           && !empty($CFG->messaging) && !isguestuser()) {
                $canshowicon = true;
            } else {
                $canshowicon = false;
            }
			if (has_capability('mod/assign:grade', $this->page->context) && !isguestuser()) { //tk added 8-7
				$canshownotes = true;
            } else {
                $canshownotes = false;
			}
            foreach ($users as $user) {
                $this->content->text .= '<li class="listentry">';

                if (isguestuser($user)) {
                    $this->content->text .= '<div class="user">'.$OUTPUT->user_picture($user, array('size'=>35, 'alttext'=>false));
                    $this->content->text .= get_string('guestuser').'</div>';

                } else {
                    $this->content->text .= '<div class="user">';
                    $this->content->text .= '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->page->course->id.'">';
                    //$employee_role = $DB->get_record('user_info_data', array('userid'=>$user->id,'fieldid'=>'2'));
					$user_pic_class = 'userpicture'; //tk added line
					// change user pic class based on variables
					if (has_capability('mod/assign:grade', $this->page->context, $user)) {
						$user_pic_class .= ' userpictureRed';
					}
					$this->content->text .= $OUTPUT->user_picture($user, array('size'=>35, 'alttext'=>false, 'link'=>false, 'class'=>$user_pic_class)) .$user->fullname.' </a></div>';
                }
                if ($canshowicon and ($USER->id != $user->id) and !isguestuser($user)) {  // Only when logged in and messaging active etc
                    $anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message') . '" alt="'. get_string('messageselectadd') .'" />';
                    $anchortag = '<a href="'.$CFG->wwwroot.'/message/index.php?id='.$user->id.'" title="'.get_string('messageselectadd').'">'.$anchortagcontents .'</a>';

                    $this->content->text .= '<div class="message">'.$anchortag.'</div>';
                }
				if ($canshownotes and !isguestuser($user)) { //tk added 8-7
					//$notecount = '#'; //add in sql call to count user's notes
					
					$where = 'userid = :userid AND publishstate <> :publishstate';
					$selectparams = array(
						'userid' => $user->id,
						'publishstate' => "draft"
					);
					$notecount = $DB->count_records_select('post', $where, $selectparams);
					
					if ($notecount == 1){
						$anchortagcontents = '{'.$notecount.' note}';
					} else {
						$anchortagcontents = '{'.$notecount.' notes}';
					}
					$anchortag = '<a href="'.$CFG->wwwroot.'/notes/index.php?user='.$user->id.'&amp;course='.$this->page->course->id.'" title="notes">'.$anchortagcontents .'</a>';
					$this->content->text .= '<div class="message" style="margin-right: 10px;">'.$anchortag.'</div>';
				}
                $this->content->text .= "</li>\n";
            }
            $this->content->text .= '</ul><div class="clearer"><!-- --></div>';
        } else {
            $this->content->text .= "<div class=\"info\">".get_string("none")."</div>";
        }

        return $this->content;
    }
}


