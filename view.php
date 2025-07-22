<?php
require_once(__DIR__ . '/../../../../config.php');
require_login();

$quizid = required_param('quizid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

// Get quiz + course info
$cm = get_coursemodule_from_instance('quiz', $quizid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_capability('mod/quiz:viewreports', $context);

$PAGE->set_cm($cm, $course);
$PAGE->set_url('/mod/quiz/accessrule/screenmonitoring/view.php', ['quizid' => $quizid, 'userid' => $userid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('studentmonitoringreport', 'quizaccess_screenmonitoring'));
$PAGE->set_heading(get_string('studentmonitoringreport', 'quizaccess_screenmonitoring'));
$PAGE->set_pagelayout('report');

// Get user info
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
$fullname = fullname($user);

// Get all screenshots for this user in this quiz
$logs = $DB->get_records('quizaccess_screenmonitoring_logs', [
    'quizid' => $quizid,
    'userid' => $userid
], 'timecreated ASC');

// Prepare template data
$contextid = $context->id;
$data = [
    'fullname' => $fullname,
    'haslogs' => !empty($logs),
    'screenshots' => []
];

foreach ($logs as $log) {
    $data['screenshots'][] = [
        'time' => userdate($log->timecreated),
        'imageurl' => $log->image // this should already be the pluginfile URL
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('quizaccess_screenmonitoring/view', $data);
echo $OUTPUT->footer();
