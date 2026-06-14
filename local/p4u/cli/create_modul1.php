#!/usr/bin/env php
<?php
/**
 * CLI script: Create/update the P4U "Modul 1" course entry in Moodle.
 *
 * Usage:
 *   php local/p4u/cli/create_modul1.php
 *   php local/p4u/cli/create_modul1.php --courseid=5
 *   php local/p4u/cli/create_modul1.php --section=2
 *
 * If no courseid is provided, a reusable test course with shortname P4U-TEST is
 * created or reused.
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

global $DB, $CFG;

$defaultsection = 1;
list($options) = cli_get_params(
    [
        'help' => false,
        'courseid' => 0,
        'section' => $defaultsection,
        'shortname' => 'P4U-TEST',
    ],
    ['h' => 'help']
);

if (!empty($options['help'])) {
    $help = <<<HELP
Create P4U Modul 1 in Moodle.

Options:
  --courseid=ID       Target course id. If omitted, a test course is created/reused.
  --section=NUM       Target section number in the course (default: {$defaultsection})
  --shortname=NAME    Test course shortname if no courseid is given (default: P4U-TEST)
  -h, --help          Show help

Examples:
  php local/p4u/cli/create_modul1.php
  php local/p4u/cli/create_modul1.php --courseid=7

HELP;
    echo $help . PHP_EOL;
    exit(0);
}

$courseid = (int)$options['courseid'];
$targetsection = (int)$options['section'];
$testshortname = clean_param((string)$options['shortname'], PARAM_ALPHANUMEXT);

if ($targetsection < 0) {
    cli_error('Invalid section number.');
}

/**
 * Return a reusable test course, creating it when it does not exist.
 */
function p4u_get_or_create_course(int $courseid, string $shortname): stdClass {
    global $DB;

    if ($courseid > 0) {
        return get_course($courseid);
    }

    if ($course = $DB->get_record('course', ['shortname' => $shortname])) {
        $updates = [];
        if ($course->fullname !== 'Peers for You - Ausbildung') {
            $updates['fullname'] = 'Peers for You - Ausbildung';
        }
        if ($course->summary !== '<p>Ausbildungskurs für Peer-Berater:innen.</p>') {
            $updates['summary'] = '<p>Ausbildungskurs für Peer-Berater:innen.</p>';
            $updates['summaryformat'] = FORMAT_HTML;
        }
        if ($updates) {
            $updates['id'] = $course->id;
            update_course((object)$updates);
            $course = $DB->get_record('course', ['id' => $course->id], '*', MUST_EXIST);
        }
        mtrace("Using existing course: {$course->fullname} (id={$course->id})");
        return get_course($course->id);
    }

    $categoryid = 1;
    if (!$DB->record_exists('course_categories', ['id' => $categoryid])) {
        $categoryid = (int)$DB->get_field('course_categories', 'id', [], IGNORE_MULTIPLE);
    }
    if ($categoryid <= 0) {
        cli_error('No course category exists. Create a category in Moodle first.');
    }

    $data = (object)[
        'fullname' => 'Peers for You - Ausbildung',
        'shortname' => $shortname,
        'category' => $categoryid,
        'summary' => '<p>Ausbildungskurs für Peer-Berater:innen.</p>',
        'summaryformat' => FORMAT_HTML,
        'format' => 'topics',
        'numsections' => 6,
        'visible' => 1,
        'enablecompletion' => 1,
        'startdate' => time(),
    ];

    $course = create_course($data);
    mtrace("Created test course: {$course->fullname} (id={$course->id})");
    return get_course($course->id);
}

/**
 * Enrol the site admin into the course so it appears on the Moodle dashboard.
 */
function p4u_enrol_admin_user(stdClass $course, stdClass $admin): void {
    global $DB;

    $manual = enrol_get_plugin('manual');
    if (!$manual) {
        mtrace('  ! Manual enrolment plugin is not available; dashboard course listing may stay empty.');
        return;
    }

    $instances = enrol_get_instances($course->id, true);
    $manualinstance = null;
    foreach ($instances as $instance) {
        if ($instance->enrol === 'manual') {
            $manualinstance = $instance;
            break;
        }
    }

    if (!$manualinstance) {
        $manual->add_default_instance($course);
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            if ($instance->enrol === 'manual') {
                $manualinstance = $instance;
                break;
            }
        }
    }

    if (!$manualinstance) {
        mtrace('  ! Could not create a manual enrolment instance.');
        return;
    }

    $context = context_course::instance($course->id);
    $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'student']);
    if ($roleid <= 0) {
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'manager']);
    }

    if (!$DB->record_exists('user_enrolments', ['enrolid' => $manualinstance->id, 'userid' => $admin->id])) {
        $manual->enrol_user($manualinstance, $admin->id, $roleid);
        mtrace("  + Enrolled admin user {$admin->username} into course {$course->id}");
    } else {
        mtrace("  = Admin user {$admin->username} already enrolled in course {$course->id}");
    }

    if ($roleid > 0 && !user_has_role_assignment($admin->id, $roleid, $context->id)) {
        role_assign($roleid, $admin->id, $context->id);
    }
}

/**
 * Build baseline moduleinfo object for add_moduleinfo().
 */
function p4u_build_moduleinfo(stdClass $course, int $sectionnum, string $modname, string $name, string $introhtml = ''): stdClass {
    global $DB;

    $moduleid = $DB->get_field('modules', 'id', ['name' => $modname], MUST_EXIST);

    $mi = new stdClass();
    $mi->course = $course->id;
    $mi->section = $sectionnum;
    $mi->module = $moduleid;
    $mi->modulename = $modname;
    $mi->name = $name;
    $mi->introeditor = [
        'text' => $introhtml,
        'format' => FORMAT_HTML,
        'itemid' => 0,
    ];
    $mi->intro = $introhtml;
    $mi->introformat = FORMAT_HTML;
    $mi->visible = 1;
    $mi->visibleoncoursepage = 1;
    $mi->groupmode = NOGROUPS;
    $mi->groupingid = 0;
    $mi->completion = COMPLETION_TRACKING_AUTOMATIC;
    $mi->completionview = 1;
    $mi->completionexpected = 0;
    $mi->availabilityconditionsjson = null;
    $mi->showdescription = 1;

    return $mi;
}

/**
 * Check if an activity with a given name already exists in a section.
 */
function p4u_activity_exists(int $courseid, int $sectionnum, string $activityname): bool {
    $modinfo = get_fast_modinfo($courseid);
    $sections = $modinfo->get_sections();
    if (empty($sections[$sectionnum])) {
        return false;
    }
    foreach ($sections[$sectionnum] as $cmid) {
        $cm = $modinfo->get_cm($cmid);
        if (trim($cm->name) === trim($activityname)) {
            return true;
        }
    }
    return false;
}

/**
 * Find a course module by visible activity name in a section.
 */
function p4u_find_activity(int $courseid, int $sectionnum, string $activityname): ?cm_info {
    $modinfo = get_fast_modinfo($courseid);
    $sections = $modinfo->get_sections();
    if (empty($sections[$sectionnum])) {
        return null;
    }
    foreach ($sections[$sectionnum] as $cmid) {
        $cm = $modinfo->get_cm($cmid);
        if (trim($cm->name) === trim($activityname)) {
            return $cm;
        }
    }
    return null;
}

/**
 * Remove legacy static page activities from earlier Modul 1 iterations.
 */
function p4u_remove_static_module_pages(stdClass $course, int $sectionnum): void {
    $names = [
        '1. Einfuehrung',
        '2. Lernkarten',
        '3. Rollenverstaendnis',
        '4. Zuordnung: Peer-Rolle',
        '5. Video-Platzhalter',
        '6. Fallbeispiel Marko',
        '7. Abschlussfragen',
    ];

    foreach ($names as $name) {
        $cm = p4u_find_activity($course->id, $sectionnum, $name);
        if (!$cm) {
            continue;
        }
        course_delete_module($cm->id);
        mtrace("  - Removed static page: {$name} (cmid={$cm->id})");
        rebuild_course_cache($course->id, true);
    }
}

/**
 * Remove the default announcements forum for a focused one-module course view.
 */
function p4u_remove_default_forums(stdClass $course): void {
    $modinfo = get_fast_modinfo($course->id);
    foreach ($modinfo->get_cms() as $cm) {
        if ($cm->modname !== 'forum') {
            continue;
        }
        course_delete_module($cm->id);
        mtrace("  - Removed default forum: {$cm->name} (cmid={$cm->id})");
        rebuild_course_cache($course->id, true);
    }
}

/**
 * Add the interactive Moodle page as a URL activity in the course.
 */
function p4u_add_interactive_link(stdClass $course, int $sectionnum): void {
    global $CFG, $DB;

    $name = 'Modul 1: Einführung & Rollenverständnis';
    $legacynames = [
        'Interaktives Modul 1',
        'Modul 1: Einfuehrung & Rollenverstaendnis',
    ];
    foreach ($legacynames as $legacyname) {
        if ($legacycm = p4u_find_activity($course->id, $sectionnum, $legacyname)) {
            $DB->set_field('url', 'name', $name, ['id' => $legacycm->instance]);
            mtrace("  ~ Renamed url: {$legacyname} -> {$name}");
            rebuild_course_cache($course->id, true);
            return;
        }
    }
    if (p4u_activity_exists($course->id, $sectionnum, $name)) {
        mtrace("  = Skip url: {$name} (already exists)");
        return;
    }

    $mi = p4u_build_moduleinfo(
        $course,
        $sectionnum,
        'url',
        $name,
        '<p>Interaktives Lernmodul mit Lernkarten, Zuordnungsaufgabe, Fallbeispiel und Quiz.</p>'
    );
    $mi->externalurl = $CFG->wwwroot . '/local/p4u/module1.php?courseid=' . $course->id;
    $mi->display = RESOURCELIB_DISPLAY_OPEN;

    $result = add_moduleinfo($mi, $course);
    mtrace("  + Added url: {$name} (cmid={$result->coursemodule})");
}

try {
    $admin = get_admin();
    if (!$admin) {
        throw new moodle_exception('noadmin');
    }
    \core\session\manager::set_user($admin);

    $course = p4u_get_or_create_course($courseid, $testshortname);
    p4u_enrol_admin_user($course, $admin);
    course_create_sections_if_missing($course, [$targetsection]);

    $sectionrecord = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $targetsection], '*', MUST_EXIST);
$sectionsummary = <<<HTML
<h2>Modul 1: Einf&uuml;hrung &amp; Rollenverst&auml;ndnis</h2>
<p><strong>Dauer:</strong> ca. 10 Minuten</p>
<p>Interaktives Lernmodul f&uuml;r die Ausbildung von Peer-Berater:innen.</p>
HTML;

    course_update_section($course, $sectionrecord, [
        'name' => 'Modul 1: Einführung',
        'summary' => $sectionsummary,
        'summaryformat' => FORMAT_HTML,
    ]);
    mtrace("Section {$targetsection} prepared: 'Modul 1: Einführung'");

    p4u_remove_default_forums($course);
    p4u_remove_static_module_pages($course, $targetsection);
    p4u_add_interactive_link($course, $targetsection);

    rebuild_course_cache($course->id, true);
    mtrace("Done: Modul 1 created successfully in course {$course->id}.");
    mtrace("Open: {$CFG->wwwroot}/course/view.php?id={$course->id}");
    exit(0);

} catch (Throwable $e) {
    mtrace('ERROR: ' . $e->getMessage());
    mtrace($e->getTraceAsString());
    exit(1);
}
