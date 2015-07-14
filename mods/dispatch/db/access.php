<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'mod/dispatch:addinstance' => array(
    'riskbitmask' => RISK_XSS,

    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => array(
        'editingteacher' => CAP_ALLOW,
        'manager' => CAP_ALLOW
    ),
    'clonepermissionsfrom' => 'moodle/course:manageactivities'
)
);

?>