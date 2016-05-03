<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_classmates_timetosee', get_string('timetosee', 'block_classmates'),
                   get_string('configtimetosee', 'block_classmates'), 30, PARAM_INT));
}

