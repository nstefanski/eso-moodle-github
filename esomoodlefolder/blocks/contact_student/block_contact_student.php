<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The contact_student block
 *
 * @package    block_contact_student
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// Obviously required
require_once('contact_student_form.php');

class block_contact_student extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_contact_student');
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        if (has_capability('moodle/site:config', context_system::instance())) {
            return array('all' => true);
        } else {
            return array('site' => true);
        }
    }

    function instance_allow_multiple() {
        return false;
    }

    function get_content() {
        global $CFG, $PAGE, $DB, $USER;
        if ($this->content !== NULL) {
            return $this->content;
        }
        $context = context_system::instance();
        if (!has_capability('block/contact_student:viewinstance', $context)) {
            return $this->content;
        }

        $userid = optional_param('id', 0, PARAM_INT);

        //get user details
        if (!empty($userid)) {
            $user = $DB->get_record('user', array('id' => $userid));
        }

        if (isset($user) && isset($USER)) {
            require_once($CFG->dirroot . '/user/profile/lib.php');
            profile_load_custom_fields($user);
            /*if (array_key_exists('cvueid', $user->profile)) {
                $cvueid = $user->profile['cvueid'];
            }*/
			$cvueid = $user->idnumber; //tk added 8/8/16
            /*$firstname = $user->firstname;
            $lastname = $user->lastname;
            $email = $user->email;*/
            if (array_key_exists('startdate', $user->profile)) {
                $timecreated = date('Y-m-d', $user->profile['startdate']);
            }
            /*$instructorname = fullname($USER);
            $instructoremail = $USER->email;*/
            $studentfullname = fullname($user);
        }
        $contact_student_form = new contact_student_form();

        if ($data = $contact_student_form->get_data()) {

            $record = new stdClass();
            $record->student_id = $userid;
            $record->cvueid = $cvueid;
            $record->instructor_id = $USER->id;
            $record->contact_type = $data->contacttype;
            $record->call_reason = $data->callreason;
            $record->notes = $data->notes;
            $record->time_created = time();
            $recordid = $DB->insert_record('block_contact_student', $record);

            $flag_records = array();
			$stringman = get_string_manager();
            for ($i = 1; $i < 99; $i++) { 
                $option = 'redflagsoption' . $i;
				if ($stringman->string_exists($option, 'block_contact_student')) {
					if (isset($data->$option)) {
						$flag_record = new stdClass();
						$flag_record->contact_id = $recordid;
						$flag_record->red_flag = get_string($option, 'block_contact_student');
						$flag_records[] = $flag_record;
					}
				} else {
					break;
				}
            }
            $DB->insert_records('block_contact_student_flags', $flag_records);

            $userprofile_url = new moodle_url('/user/profile.php', array('id' => $userid));
            redirect($userprofile_url);
        } else {
            $contact_student_form->set_data(
                    array(
                        'id' => $userid,
                        'studentcvueid' => $cvueid,
                        /*'studentfirstname' => $firstname,
                        'studentlastname' => $lastname,
                        'studentemail' => $email,*/
                        'studentstartdate' => $timecreated,
                        /*'instructorname' => $instructorname,
                        'instructoremail' => $instructoremail,*/
                        'displaystudentname' => $studentfullname
            ));
            $form_html = $contact_student_form->render();

            $this->content = new stdClass();
            $this->content->text = $form_html;
            $this->content->footer = '';
            return $this->content;
        }
    }

}
