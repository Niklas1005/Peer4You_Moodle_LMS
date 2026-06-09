<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Marks a P4U interactive module activity as completed for the current user.
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

require_sesskey();

$course = get_course($courseid);
require_login($course);

$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_url(new moodle_url('/local/p4u/complete_module.php', ['courseid' => $course->id, 'module' => $module]));

$completion = new completion_info($course);
$modinfo = get_fast_modinfo($course);
$target = null;
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
        $target = $cm;
        break;
    }
}

if (!$target) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'message' => 'activitynotfound']);
    exit;
}

if (!$completion->is_enabled($target)) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'message' => 'completiondisabled']);
    exit;
}

$completion->update_state($target, COMPLETION_COMPLETE, $USER->id);

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'cmid' => $target->id]);
