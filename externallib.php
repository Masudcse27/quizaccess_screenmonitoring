<?php
// File: mod/quiz/accessrule/screenmonitoring/externallib.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/accesslib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use context_module;
use moodle_exception;
use invalid_parameter_exception;

class quizaccess_screenmonitoring_external extends external_api {

    public static function upload_image_parameters(): external_function_parameters {
        return new external_function_parameters([
            'image'     => new external_value(PARAM_RAW, 'Base64 encoded image string with data URI prefix'),
            'quizid'    => new external_value(PARAM_INT, 'Quiz ID'),
            'userid'    => new external_value(PARAM_INT, 'User ID'),
            'attemptid' => new external_value(PARAM_INT, 'Quiz attempt ID', VALUE_DEFAULT, 0), // optional, default 0
        ]);
    }

    public static function upload_image(string $image, int $quizid, int $userid, int $attemptid = 0): array {
        global $USER, $DB;

        // Allow only the user themselves or site admin
        if ($USER->id !== $userid) {
            throw new moodle_exception('unauthorized', 'quizaccess_screenmonitoring');
        }

        // Get quiz context and validate
        $cm = get_coursemodule_from_instance('quiz', $quizid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);
        self::validate_context($context);

        // Check permission to attempt quiz
        if (!has_capability('mod/quiz:attempt', $context, $USER->id)) {
            throw new moodle_exception('nopermissions', 'error', '', null, 'mod/quiz');
        }

        // Validate image data URI format
        if (!preg_match('/^data:image\/(\w+);base64,/', $image, $matches)) {
            throw new invalid_parameter_exception('Invalid image data format. Expected data URI.');
        }

        // Insert record in DB
        $record = new stdClass();
        $record->quizid = $quizid;
        $record->userid = $userid;
        $record->attemptid = $attemptid;
        $record->image = $image;
        $record->timecreated = time();

        $DB->insert_record('quizaccess_screenmonitoring_logs', $record);

        return ['status' => 'success', 'message' => 'Screenshot saved successfully'];
    }

    public static function upload_image_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the upload'),
            'message' => new external_value(PARAM_TEXT, 'Upload result message')
        ]);
    }
}
