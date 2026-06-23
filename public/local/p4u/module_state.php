<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Peer4You — AJAX-API für Lernfortschritt und Quiz-Statistiken.
 *
 * Endpunkte (eingeloggt, Kurskontext):
 *   GET  ?action=get  — lädt progress + quizStats + completed aus der DB
 *   POST ?action=save — speichert JSON-Body in User Preferences (sesskey Pflicht)
 *
 * Wird von module-progress.js aufgerufen; autoritative Quelle ist MariaDB.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');

use local_p4u\local\progress_store;

$courseid = required_param('courseid', PARAM_INT);
$module = optional_param('module', 1, PARAM_INT);
$action = optional_param('action', 'get', PARAM_ALPHA);

if ($module < 1 || $module > 6) {
    throw new invalid_parameter_exception('module');
}

$course = get_course($courseid);
require_login($course);

if ($action === 'save') {
    require_sesskey();
}

$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_url(new moodle_url('/local/p4u/module_state.php', [
    'courseid' => $course->id,
    'module' => $module,
    'action' => $action,
]));

/**
 * @param array $payload
 */
function local_p4u_module_state_json(array $payload): void {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$userid = (int)$USER->id;
$completed = progress_store::is_module_completed($course, $module, $userid);

if ($action === 'get') {
    local_p4u_module_state_json([
        'ok' => true,
        'completed' => $completed,
        'progress' => progress_store::get_progress($module, $userid),
        'quizStats' => progress_store::get_quiz_stats($module, $userid),
    ]);
}

if ($action !== 'save') {
    local_p4u_module_state_json(['ok' => false, 'message' => 'invalidaction']);
}

$rawbody = file_get_contents('php://input');
$payload = json_decode($rawbody ?: '', true);
if (!is_array($payload)) {
    local_p4u_module_state_json(['ok' => false, 'message' => 'invalidpayload']);
}

if (isset($payload['progress']) && is_array($payload['progress'])) {
    progress_store::save_progress($module, $userid, $payload['progress']);
}

if (isset($payload['quizStats']) && is_array($payload['quizStats'])) {
    progress_store::save_quiz_stats($module, $userid, $payload['quizStats']);
}

local_p4u_module_state_json([
    'ok' => true,
    'completed' => $completed,
]);
