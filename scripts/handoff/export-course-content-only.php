#!/usr/bin/env php
<?php
/**
 * Export one course as .mbz without user accounts or enrolments (content only).
 *
 * Usage (inside web container, from Moodle root):
 *   php scripts/handoff/export-course-content-only.php --courseshortname=P4U-TEST --destination=/var/moodledata/handoff/export-test
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

list($options,) = cli_get_params(
    [
        'courseshortname' => '',
        'courseid' => 0,
        'destination' => '',
        'help' => false,
    ],
    ['h' => 'help']
);

if ($options['help'] || (empty($options['courseshortname']) && empty($options['courseid']))) {
    echo <<<HELP
Export a single course backup without users/enrolments.

Options:
  --courseshortname=NAME   Course shortname (e.g. P4U-TEST)
  --courseid=ID            Or course id
  --destination=PATH       Directory for the .mbz file (required)
  -h, --help               This help

Example:
  php scripts/handoff/export-course-content-only.php \\
    --courseshortname=P4U-TEST \\
    --destination=/var/moodledata/handoff/export-manual

HELP;
    exit($options['help'] ? 0 : 1);
}

$dest = rtrim($options['destination'], '/');
if ($dest === '' || !is_dir($dest) || !is_writable($dest)) {
    cli_error('Destination must be an existing writable directory.');
}

$admin = get_admin();
if (!$admin) {
    cli_error('No admin user found.');
}
\core\session\manager::set_user($admin);

if (!empty($options['courseid'])) {
    $course = $DB->get_record('course', ['id' => (int)$options['courseid']], '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', ['shortname' => $options['courseshortname']], '*', MUST_EXIST);
}

$bc = new backup_controller(
    backup::TYPE_1COURSE,
    $course->id,
    backup::FORMAT_MOODLE,
    backup::INTERACTIVE_NO,
    backup::MODE_GENERAL,
    $admin->id
);

foreach (['users', 'enrolments', 'role_assignments', 'groups', 'comments', 'userscompletion', 'logs', 'grade_histories'] as $setting) {
    $s = $bc->get_plan()->get_setting($setting);
    if ($s) {
        $s->set_status(backup_setting::NOT_LOCKED);
        $s->set_value(0);
    }
}

$filename = backup_plan_dbops::get_default_backup_filename(
    $bc->get_format(),
    $bc->get_type(),
    $bc->get_id(),
    0,
    0
);
$bc->get_plan()->get_setting('filename')->set_value($filename);

$bc->execute_plan();
$results = $bc->get_results();
$file = $results['backup_destination'] ?? null;
$target = $dest . '/' . $course->shortname . '-content-only.mbz';

if ($file && $file->copy_content_to($target)) {
    $file->delete();
    mtrace("Wrote: {$target}");
} else {
    cli_error('Backup file could not be written.');
}

$bc->destroy();
exit(0);
