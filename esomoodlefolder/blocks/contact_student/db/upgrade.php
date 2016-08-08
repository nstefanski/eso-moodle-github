<?php

function xmldb_block_contact_student_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $result = TRUE;

    if ($oldversion < 2016021501) {

        // Define field id to be added to block_contact_student.
        $table = new xmldb_table('block_contact_student');
        $field = new xmldb_field('time_created', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'notes');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Contact_student savepoint reached.
        upgrade_block_savepoint(true, 2016021501, 'contact_student');
    }

    if ($oldversion < 2016021600) {

        // Define field cvueid to be added to block_contact_student.
        $table = new xmldb_table('block_contact_student');
        $field = new xmldb_field('cvueid', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'notes');

        // Conditionally launch add field cvueid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Contact_student savepoint reached.
        upgrade_block_savepoint(true, 2016021600, 'contact_student');
    }


    return $result;
}
