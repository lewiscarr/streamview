<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Maximum number of activities per row
    $settings->add(new admin_setting_configselect(
        'format_streamview/maxactivitiesperrow',
        get_string('maxactivitiesperrow', 'format_streamview'),
        get_string('maxactivitiesperrow_desc', 'format_streamview'),
        5, // default value
        array(3 => '3', 4 => '4', 5 => '5', 6 => '6')
    ));
}