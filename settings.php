<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('quizaccess_screenmonitoring', get_string('pluginname', 'quizaccess_screenmonitoring'));

    $plugindescription = get_string('plugin_description', 'quizaccess_screenmonitoring');

    // Add a heading inside the settings page.
    $settings->add(new admin_setting_heading('pluginnameheading', '', $plugindescription));

    // Add your interval config setting.
    $settings->add(new admin_setting_configtext(
        'quizaccess_screenmonitoring/interval',
        get_string('interval', 'quizaccess_screenmonitoring'),
        get_string('interval_desc', 'quizaccess_screenmonitoring'),
        30, // default 30 seconds
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'quizaccess_screenmonitoring/accesstoken',
        get_string('accesstoken', 'quizaccess_screenmonitoring'),
        get_string('accesstoken_desc', 'quizaccess_screenmonitoring'),
        '',
        PARAM_TEXT
    ));

    // Add the settings page to the quiz access rules category.
    $ADMIN->add('quizaccessrules', $settings);
}