<?php
defined('MOODLE_INTERNAL') || die();

$definitions = array(
    'activityimages' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'staticacceleration' => true,
        'invalidationevents' => array(
            'contentupdated'
        )
    )
); 