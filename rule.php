<?php
// This file is part of Moodle - http://moodle.org/
// License: GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

use mod_quiz\local\access_rule_base;
use mod_quiz\quiz_settings;
use mod_quiz_mod_form;
use MoodleQuickForm;

class quizaccess_screenmonitoring extends access_rule_base
{

    /** @var bool Whether screen monitoring is enabled for this quiz */
    protected $enabled;

    /**
     * Constructor.
     */
    public function __construct(quiz_settings $quizobj, $timenow)
    {
        parent::__construct($quizobj, $timenow);
        $this->enabled = (bool) ($quizobj->get_quiz()->screencapture ?? false);
    }

    /**
     * Factory method to create rule instance.
     */
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits)
    {
        if (empty($quizobj->get_quiz()->screencapture)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    /**
     * Add setting to quiz form.
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform)
    {
        $mform->addElement(
            'select',
            'screencapture',
            get_string('enablelabel', 'quizaccess_screenmonitoring'),
            [
                0 => get_string('disabled', 'quizaccess_screenmonitoring'),
                1 => get_string('enabled', 'quizaccess_screenmonitoring'),
            ]
        );
        $mform->addHelpButton('screencapture', 'enablelabel', 'quizaccess_screenmonitoring');
        $mform->setDefault('screencapture', 0);
    }

    /**
     * Save setting when quiz is saved.
     */
    public static function save_settings($quiz)
    {
        global $DB;

        if (!isset($quiz->screencapture)) {
            return;
        }

        $record = $DB->get_record('quizaccess_screenmonitoring', ['quizid' => $quiz->id]);
        if ($record) {
            $record->enabled = (int) $quiz->screencapture;
            $DB->update_record('quizaccess_screenmonitoring', $record);
        } else {
            $record = (object) [
                'quizid' => $quiz->id,
                'enabled' => (int) $quiz->screencapture,
            ];
            $DB->insert_record('quizaccess_screenmonitoring', $record);
        }
    }

    /**
     * Delete setting when quiz is deleted.
     */
    public static function delete_settings($quiz)
    {
        global $DB;
        $DB->delete_records('quizaccess_screenmonitoring', ['quizid' => $quiz->id]);
    }

    /**
     * Load setting from DB when loading quiz settings.
     */
    public static function get_settings_sql($quizid)
    {
        return [
            'screenmon.enabled AS screencapture',
            'LEFT JOIN {quizaccess_screenmonitoring} screenmon ON screenmon.quizid = quiz.id',
            []
        ];
    }

    /**
     * Describe the restriction in the quiz settings UI.
     */
    public function description(): string
    {
        return get_string('screenshotdescription', 'quizaccess_screenmonitoring');
    }

    

    
}
