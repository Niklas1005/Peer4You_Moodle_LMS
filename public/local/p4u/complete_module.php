<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Reads or marks completion for a P4U interactive module activity.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid = required_param('courseid', PARAM_INT);
$module = optional_param('module', 1, PARAM_INT);
$action = optional_param('action', 'complete', PARAM_ALPHA);

$course = get_course($courseid);
require_login($course);

if ($action === 'complete') {
    require_sesskey();
}

$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_url(new moodle_url('/local/p4u/complete_module.php', [
    'courseid' => $course->id,
    'module' => $module,
    'action' => $action,
]));

function local_p4u_json_response(array $payload): void {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function local_p4u_find_module_cm(stdClass $course, int $module): ?cm_info {
    global $DB;

    $modinfo = get_fast_modinfo($course);
    $modulepath = "/local/p4u/module{$module}.php";

    foreach ($modinfo->cms as $cm) {
        if ($cm->modname !== 'url') {
            continue;
        }

        $urlrecord = $DB->get_record('url', ['id' => $cm->instance], 'id, name, externalurl');
        if (!$urlrecord) {
            continue;
        }

        $name = trim($urlrecord->name);
        $matchesname = $module === 1 && $name === 'Interaktives Lernmodul starten';
        $matchesurl = strpos($urlrecord->externalurl, $modulepath) !== false;

        if ($matchesname || $matchesurl) {
            return $cm;
        }
    }

    return null;
}

$completion = new completion_info($course);
$target = local_p4u_find_module_cm($course, $module);

if (!$target) {
    local_p4u_json_response(['ok' => false, 'message' => 'activitynotfound']);
}

if (!$completion->is_enabled($target)) {
    local_p4u_json_response([
        'ok' => true,
        'completed' => false,
        'completionenabled' => false,
        'cmid' => $target->id,
    ]);
}

if ($action === 'status') {
    $data = $completion->get_data($target, false, $USER->id);
    local_p4u_json_response([
        'ok' => true,
        'completed' => in_array((int)$data->completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS], true),
        'completionenabled' => true,
        'cmid' => $target->id,
    ]);
}

$completion->update_state($target, COMPLETION_COMPLETE, $USER->id);

local_p4u_json_response([
    'ok' => true,
    'completed' => true,
    'completionenabled' => true,
    'cmid' => $target->id,
]);
