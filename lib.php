<?php
// This file is part of Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License.

/**
 * Library functions for the screen monitoring access rule plugin.
 *
 * @package    quizaccess_screenmonitoring
 * @copyright  Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Injects custom CSS into pages using the screenmonitoring plugin.
 */
function quizaccess_screenmonitoring_before_http_headers() {
    global $PAGE;

    // Load CSS only for this plugin's view/report pages.
    if (strpos($PAGE->url->out(false), '/mod/quiz/accessrule/screenmonitoring/') !== false) {
        $PAGE->requires->css('/mod/quiz/accessrule/screenmonitoring/styles.css');
    }
}
