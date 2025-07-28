<?php
$functions = [
    'quizaccess_screenmonitoring_upload_image' => [
        'classname'   => 'quizaccess_screenmonitoring_external',
        'methodname'  => 'upload_image',
        'classpath'   => 'mod/quiz/accessrule/screenmonitoring/externallib.php',
        'description' => 'Uploads screenshot from Chrome extension',
        'type'        => 'write',
        'ajax'        => true,
    ],
];

$services = [
    'Quiz Screen Monitoring API' => [
        'functions' => ['quizaccess_screenmonitoring_upload_image'],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'ScreenMonitoringService',
    ],
];