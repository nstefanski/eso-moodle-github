<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("{$CFG->libdir}/formslib.php");

class contact_student_form extends moodleform {

    function definition() {

        $mform = $this->_form;
        $mform->addElement('text', 'id', get_string('studentid', 'block_contact_student'));
        $mform->hardFreeze('id');
        
        $mform->addElement('text', 'studentcvueid', get_string('studentcvueid', 'block_contact_student'));
        $mform->hardFreeze('studentcvueid');

        /*$mform->addElement('text', 'studentfirstname', get_string('studentfirstname', 'block_contact_student'));
        $mform->hardFreeze('studentfirstname');

        $mform->addElement('text', 'studentlastname', get_string('studentlastname', 'block_contact_student'));
        $mform->hardFreeze('studentlastname');

        $mform->addElement('text', 'studentemail', get_string('studentemail', 'block_contact_student'));
        $mform->hardFreeze('studentemail');*/

        $mform->addElement('text', 'studentstartdate', get_string('studentstartdate', 'block_contact_student'));
        $mform->hardFreeze('studentstartdate');

        /*$mform->addElement('text', 'instructorname', get_string('instructorname', 'block_contact_student'));
        $mform->hardFreeze('instructorname');

        $mform->addElement('text', 'instructoremail', get_string('instructoremail', 'block_contact_student'));
        $mform->hardFreeze('instructoremail');*/

        $mform->addElement('text', 'displaystudentname', get_string('displaystudentname', 'block_contact_student'));
        $mform->hardFreeze('displaystudentname');

        $contactoptions = array('moodlemessage' => '',
            'phone' => get_string('phone', 'block_contact_student'),
            'text' => get_string('text', 'block_contact_student'),
            'email' => get_string('email', 'block_contact_student'),
            'inperson' => get_string('inperson', 'block_contact_student'));

        $mform->addElement('select', 'contacttype', get_string('contacttype', 'block_contact_student'), $contactoptions);
        $mform->addHelpButton('contacttype', 'contacttype', 'block_contact_student');
        
        $callreasonoptions = array('' => '',
            get_string('24hourcall', 'block_contact_student') => get_string('24hourcall', 'block_contact_student'),
            get_string('studenttostudentcall', 'block_contact_student') => get_string('studenttostudentcall', 'block_contact_student'),
            get_string('atriskcall', 'block_contact_student') => get_string('atriskcall', 'block_contact_student'),
            get_string('other', 'block_contact_student') => get_string('other', 'block_contact_student'));

        $mform->addElement('select', 'callreason', get_string('callreason', 'block_contact_student'), $callreasonoptions);

        $mform->addElement('checkbox', 'redflagsoption1', get_string('redflags', 'block_contact_student'), get_string('redflagsoption1', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption2', '', get_string('redflagsoption2', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption3', '', get_string('redflagsoption3', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption4', '', get_string('redflagsoption4', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption5', '', get_string('redflagsoption5', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption6', '', get_string('redflagsoption6', 'block_contact_student', array('class' => 'redflag')));
        $mform->addElement('checkbox', 'redflagsoption7', '', get_string('redflagsoption7', 'block_contact_student', array('class' => 'redflag')));
		$mform->addElement('checkbox', 'redflagsoption8', '', get_string('redflagsoption8', 'block_contact_student', array('class' => 'redflag')));
		$mform->addElement('checkbox', 'redflagsoption9', '', get_string('redflagsoption9', 'block_contact_student', array('class' => 'redflag')));

        $mform->addElement('textarea', 'notes', get_string('notes', 'block_contact_student'), 'wrap="virtual" rows="10" cols="25"');

        $this->add_action_buttons(false, 'Submit');
    }

}
