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
 * Block for creating an automatic faculty information HTML block
 *
 * @package   block_faculty_tbird
 * @copyright 2013 onwards Johan Reinalda (http://www.thunderbird.edu)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/user/lib.php');

class block_faculty_tbird extends block_base {

    function init() {
    	// this is the initial title for the block
    	// this will be used on the settings page for this block
    	// instances in courses will have the settings value of $CFG->block_faculty_tbird/configtitle
    	// as this will be overwritten on specialization() method.
		$this->title = get_string('pluginname', 'block_faculty_tbird');
    }

    // make sure header is shown, with global admin title, set in language file
    function hide_header(){
    	// if no title set, don't show header
    	// custom title allowed and set ?
    	if(get_config('block_faculty_tbird','configallownewtitle') && !empty($this->config->title)) {
    		return false;
    	}
    	// system title set ?
    	$newtitle = get_config('block_faculty_tbird','configtitle');
    	if(empty($newtitle)) {
    		return true;
    	}
    	return false;
    }
    
    // we have global config/settings data
	function has_config() {
		return true;
	}

	// only show in courses
    function applicable_formats() {
        return array('course-view' => true);
    }

    // this class is called immediately after object is instantiated.
    // here we can override the title for this instance
	function specialization() {
		//override title to come from global configuration if set
		if(get_config('block_faculty_tbird','configallownewtitle') && !empty($this->config->title)) {
			$this->title = $this->config->title;
		} else {
			$newtitle = get_config('block_faculty_tbird','configtitle');
			if(!empty($newtitle))
				$this->title = $newtitle;
		}
	}

    // we do NOT allow multiple instances of this block!
    function instance_allow_multiple() {
        return false;
    }

    // Instance configuration only if globally allowed.
    // In that case each block can have some custom info added to the global data
	function instance_allow_config() {
    	if(get_config('block_faculty_tbird','configallownewtitle')
    		or get_config('block_faculty_tbird','configallowcustom')) {
    		return true;
    	}
		return false;
	}
	
    function get_content() {
    	global $CFG, $DB, $COURSE, $USER;
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        $info = '';
         
        // find the database id's for our custom profile field extentions
        $fieldsql = "SELECT id,shortname FROM {user_info_field} WHERE shortname IN ('facultyheadertbird', 'officetbird', 'officehourstbird', 'biourltbird')";
        $fields = $DB->get_recordset_sql($fieldsql);
        $fieldidlist = '';
        $fieldname = Array();
        $idcount = 0;
        foreach($fields as $field) {
        	if($idcount > 0) $fieldidlist .= ',';
        	$fieldidlist .= $field->id;
        	$fieldname[$field->id] = $field->shortname;
        	$idcount++;
        }
        $fields->close();
        
        // $info .= "\n<!--\n" . print_r($fieldname,true);
        // $info .= "\n$fieldidlist\n-->\n";
        
        $facultyroles = get_config('block_faculty_tbird','facultyroles');
        // $info .= "\n<!--\n" . print_r($facultyroles,true) . "-->\n";
        
        if(!empty($COURSE)) {
        	// get all enrolled users with the globally selected roles (eg. 'teacher'(3)) at the course level (context 50) :
        	$facultyquery = "SELECT id,firstname,lastname,username,email,picture,phone1,phone2,skype FROM {user}
					WHERE id in (SELECT userid
								FROM {context} c
								JOIN {role_assignments} ra ON c.id = ra.contextid
								WHERE c.contextlevel = 50
								AND c.instanceid=" . $COURSE->id . " AND ra.roleid IN ($facultyroles) )";
        	$facultylist = $DB->get_recordset_sql($facultyquery);
        	
        	foreach ($facultylist as $faculty) {

        		// get this users extended profile fields, if set
        		// we don't care about dataformat, since we know we created all these fields as text!
        		$fieldq = "select fieldid,data from {user_info_data} where userid = " . $faculty->id . ' and fieldid in (' . $fieldidlist . ')';
        		$fieldinfo = $DB->get_recordset_sql($fieldq);
        		// and add to $faculty object
        		foreach($fieldinfo as $f) {
        			$shortname = $fieldname[$f->fieldid];
        			$faculty->$shortname = $f->data;
        		}
        		$fieldinfo->close();
        		
        		// get profile images, from /user/lib.php and /lib/weblib.php
        		if($faculty->picture > 0) { //they have uploaded a profile image
	        		// profile image looks something line:
	        		$facultycontext = context_user::instance($faculty->id, MUST_EXIST);
	        		$imageurl = moodle_url::make_pluginfile_url($facultycontext->id, 'user', 'icon', NULL, '/', 'f1');
	        		$faculty->profileimageurl = $imageurl->out(false);
	       			// $imageurl = moodle_url::make_pluginfile_url($facultycontext->id, 'user', 'icon', NULL, '/', 'f2');
	       			// $faculty->profileimageurlsmall = $imageurl->out(false);
	       			
	       			// $info .= "\n<!--\n" . print_r($faculty,true) . "-->\n";
       			}
       			
        		// build the block content
        		// if($info <> '') $info .= '<br/>'; //extra space for second faculty
        		
        		// header title for this faculty
        		if(isset($faculty->facultyheadertbird) && $faculty->facultyheadertbird <> '')
        			$fheader = s($faculty->facultyheadertbird);
        		else
        			$fheader = s($faculty->firstname . ' ' . $faculty->lastname);
        		
        		// start with link to profile
                $info .= '<a href="' . $CFG->wwwroot . '/user/view.php?id=' .$faculty->id . '&course=' . $COURSE->id .
                	'" title="' . get_string('profileclicktitle','block_faculty_tbird') . $fheader . '">';
        				
        		// if set, add image
        		if(!empty($faculty->profileimageurl))
        			$info .= '<br /><img src="' . $faculty->profileimageurl . '" alt="' . $fheader . '" />';
				// add header
        		$info .= '</br><strong>' . $fheader . '</strong></a></br>';
				
				//tk send a message button
				$messageUrl = new moodle_url('/message/index.php', array('id' => $faculty->id));
				$info .= '<a target="_blank" href="' . $messageUrl . '"><button class="btn btn-primary" type="button">Send a Message</button></a></br >';
				$info .= '<span style="font-size: x-small;">Messages will be responded to within 24 hours.</span></br></br>';
        		
        		// these fields may or may not be set!
        		if(isset($faculty->officetbird) && $faculty->officetbird <> '')
        			$info .= '<strong>' . get_string('officelabel','block_faculty_tbird') . ':</strong> ' . $faculty->officetbird; //tk replace custom text field with text area
				if(isset($faculty->officehourstbird) && $faculty->officehourstbird <> '')
        			$info .= '<strong>' . get_string('officehourslabel','block_faculty_tbird') . ':</strong> ' . s($faculty->officehourstbird) . '</br >';
				if(isset($faculty->phone1) && $faculty->phone1 <> '') {
					$phoneInt = str_replace(array('(',')','-',' ','.'), '',$faculty->phone1);
					$phoneInt = str_replace(array('(',')','-',' ','.'), '',$faculty->phone1);
					$phoneInt = str_replace(array('a','b','c','A','B','C'), '2',$phoneInt);
					$phoneInt = str_replace(array('d','e','f','D','E','F'), '3',$phoneInt);
					$phoneInt = str_replace(array('g','h','i','G','H','I'), '4',$phoneInt);
					$phoneInt = str_replace(array('j','k','l','J','K','L'), '5',$phoneInt);
					$phoneInt = str_replace(array('m','n','p','M','N','O'), '6',$phoneInt);
					$phoneInt = str_replace(array('p','q','r','s','P','Q','R','S'), '7',$phoneInt);
					$phoneInt = str_replace(array('t','u','v','T','U','V'), '8',$phoneInt);
					$phoneInt = str_replace(array('w','x','y','z','W','X','Y','Z'), '9',$phoneInt);
					$info .= '<strong>' . get_string('phonelabel','block_faculty_tbird') . ':</strong> <a href="tel:+1' . $phoneInt .  '">' . s($faculty->phone1) . '</a></br >';
				}

				// $info .= '<strong>' . get_string('emaillabel','block_faculty_tbird') . ':</strong> <a href="mailto:' . $faculty->email . '">' . $faculty->email . '</a></br >';
				$info .= '<strong>' . get_string('emaillabel','block_faculty_tbird') . ':</strong>  ' . obfuscate_mailto($faculty->email,'')  . '</br >';

				// check if skype is set and not globally hidden field!
	        	// code copied from /user/profile.php around line 301
	        	$hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
	        	if(isset($faculty->skype) && $faculty->skype <> '' && !isset($hiddenfields['skypeid'])) {
	        		if (strpos($CFG->httpswwwroot, 'https:') === 0) {
	        			// Bad luck, skype devs are lazy to set up SSL on their servers - see MDL-37233.
	        			$statusicon = '';
	        		} else {
	        			$statusicon = ' '.html_writer::empty_tag('img', array('src'=>'http://mystatus.skype.com/smallicon/'.urlencode($faculty->skype), 'alt'=>get_string('status')));
	        		}
					$info .= '<strong>' . get_string('skypelabel','block_faculty_tbird') . ':</strong> <a href="skype:' . 
						urlencode($faculty->skype).'?call">'.s($faculty->skype).$statusicon.'</a></br >';
				}
				// finally, add Biography link if set
	        	if(isset($faculty->biourltbird) && $faculty->biourltbird <> '')
					$info .= '<br /><a target="_blank" href="' . $faculty->biourltbird . '" title="' . get_string('biotitle','block_faculty_tbird') . '">' . $fheader . get_string('bio','block_faculty_tbird') . '</a></br>';
        	}
        	$facultylist->close();
        	     
        }
        
        // add instance content, if allowed and set
		if(get_config('block_faculty_tbird','configallowcustom') and isset($this->config->text) and $this->config->text != '') {
			// rewrite url
			$this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_faculty_tbird', 'content', NULL);
			// Default to FORMAT_HTML which is what will have been used before the
			// editor was properly implemented for the block.
			$format = FORMAT_HTML;
			// Check to see if the format has been properly set on the config
			if (isset($this->config->format)) {
				$format = $this->config->format;
			}
			$filteropt = new stdClass;
			$filteropt->overflowdiv = true;
			// fancy html allowed only on course, category and system blocks.
			$filteropt->noclean = true;
			// $info .= '<p>' . format_text($this->config->text, $format, $filteropt) . '</p>';
			$info .= '<hr>' . format_text($this->config->text, $format, $filteropt);
		}					
		
		if($info === '' and !get_config('block_faculty_tbird','configshowifempty')) {
        	// do not show empty block, ie. clear out text and footer!
        	$this->clear_content();
        } else {
	        // add to block content
	        $this->content->text = $info;
	        // add global footer
	        $this->add_footer();
        }
        
        return $this->content;
    }
    
    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
    	$config = clone($data);
    	if(get_config('block_faculty_tbird','configallowcustom')) {
    		// Move embedded files into a proper filearea and adjust HTML links to match
    		$config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_faculty_tbird', 'content', 0, array('subdirs'=>true), $data->text['text']);
    		$config->format = $data->text['format'];
    	}
    
    	parent::instance_config_save($config, $nolongerused);
    }
    
    function instance_delete() {
    	$fs = get_file_storage();
    	$fs->delete_area_files($this->context->id, 'block_faculty_tbird');
    	return true;
    }
    
	function add_footer() {
		// add footer if set
		$configfooter = get_config('block_faculty_tbird','configfooter');
		if(!empty($configfooter)) {
			$this->content->footer = $configfooter;
		}
	}
	
    function clear_content() {
		$this->content->text = '';
		$this->content->footer = '';
    }

}
