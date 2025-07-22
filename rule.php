<?php
// This file is part of Moodle - http://moodle.org/
// License: GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

use mod_quiz\local\access_rule_base;
use mod_quiz\quiz_settings;
use mod_quiz_mod_form;
use MoodleQuickForm;
require_once($CFG->libdir . '/externallib.php');

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
    public function description(): array
    {
        $messages = [
            get_string('screenshotdescription', 'quizaccess_screenmonitoring'),
            $this->get_download_config_button(),
        ];

        return $messages;
    }
    private function get_download_config_button(): string
    {
        global $OUTPUT, $USER;

        $context = context_module::instance($this->quiz->cmid, MUST_EXIST);

        if (has_capability('quizaccess/screenmonitoring:viewreport', $context, $USER->id)) {
            // Generate the link for the screen monitoring report with the required quizid parameter.
            $url = new moodle_url('/mod/quiz/accessrule/screenmonitoring/report.php', ['quizid' => $this->quiz->id]);
            
            return $OUTPUT->single_button($url, get_string('monitoringreport', 'quizaccess_screenmonitoring'), 'get');
        }

        // Return an empty string if the user lacks the required capability.
        return '';
    }
    public static function get_screenmonitoring_token($userid)
    {
        global $DB;

        $service = $DB->get_record('external_services', ['shortname' => 'ScreenMonitoringService'], '*', MUST_EXIST);

        $existing = $DB->get_record('external_tokens', [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ]);

        if ($existing) {
            return $existing->token;
        }

        // This returns the token string directly
        $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $userid, context_system::instance());
        return $token;
    }

    public function prevent_access(): bool
    {
        global $PAGE, $USER, $DB, $CFG;


        if (!$this->enabled) {
            return false;
        }

        $quizid = $this->quiz->id;
        $cmid = $this->quiz->cmid;
        $userid = $USER->id;
        $interval = 5000; // Screenshot interval in ms

        // Get current attempt ID (or 0 if none)
        $params = [
            'quiz' => $quizid,
            'userid' => $userid,
            'preview' => 0
        ];
        $attempt = $DB->get_record('quiz_attempts', $params, '*', IGNORE_MULTIPLE);
        $attemptid = $attempt ? $attempt->id : 0;

        // Moodle web service token for this user
        $token = self::get_screenmonitoring_token($userid);

        // Web service URL to receive screenshots
        $uploadurl = $CFG->wwwroot . '/webservice/rest/server.php' .
            '?wstoken=' . $token .
            '&wsfunction=quizaccess_screenmonitoring_upload_image' .
            '&moodlewsrestformat=json';

        // Pass parameters to AMD JS module
        $params = [
            'interval' => $interval,
            'quizid' => $quizid,
            'cmid' => $cmid,
            'userid' => $userid,
            'attemptid' => $attemptid,
            'uploadurl' => $uploadurl,
        ];

        $PAGE->requires->js_call_amd(
            'quizaccess_screenmonitoring/monitor',
            'init',
            [$params]
        );

        echo \html_writer::div('', 'quizaccess_screenmonitoring_init', ['style' => 'display:none']);

        return false; // allow quiz access, only inject monitoring JS
    }


    
}
