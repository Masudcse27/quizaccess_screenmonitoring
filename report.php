<?php
require_once(__DIR__ . '/../../../../config.php');
require_login();

$quizid = required_param('quizid', PARAM_INT);

$cm = get_coursemodule_from_instance('quiz', $quizid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$context = context_module::instance($cm->id);
require_capability('mod/quiz:viewreports', $context);

$PAGE->set_cm($cm, $course);
$PAGE->set_url('/mod/quiz/accessrule/screenmonitoring/report.php', ['quizid' => $quizid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('screenmonitoringreport', 'quizaccess_screenmonitoring'));
$PAGE->set_heading(get_string('screenmonitoringreport', 'quizaccess_screenmonitoring'));
$PAGE->set_pagelayout('report');

global $DB, $OUTPUT;

// Get list of users with logs
$sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
        FROM {quiz_attempts} qa
        JOIN {user} u ON qa.userid = u.id
        WHERE qa.quiz = :quizid
        ORDER BY u.lastname, u.firstname";
$users = $DB->get_records_sql($sql, ['quizid' => $quizid]);

// Build data for Mustache
$data = [
    'quizid' => $quizid,
    'hasusers' => !empty($users),
    'users' => []
];

foreach ($users as $user) {
    $reporturl = new moodle_url('/mod/quiz/accessrule/screenmonitoring/view.php', [
        'quizid' => $quizid,
        'userid' => $user->id
    ]);
    $data['users'][] = [
        'fullname' => fullname($user),
        'email' => $user->email,
        'reporturl' => $reporturl->out(false)
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('quizaccess_screenmonitoring/report', $data);
echo $OUTPUT->footer();
