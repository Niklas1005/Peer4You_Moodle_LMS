#!/usr/bin/env php
<?php
/**
 * CLI script: Create/update the P4U training module structure in Moodle.
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/resourcelib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

global $DB, $CFG;

list($options) = cli_get_params(
    [
        'help' => false,
        'courseid' => 0,
        'shortname' => 'P4U-TEST',
    ],
    ['h' => 'help']
);

if (!empty($options['help'])) {
    echo "Create/update P4U modules 1-6 in Moodle.\n\n";
    echo "Options:\n";
    echo "  --courseid=ID       Target course id. If omitted, course shortname P4U-TEST is reused.\n";
    echo "  --shortname=NAME    Course shortname if no courseid is given.\n";
    exit(0);
}

$modules = [
    1 => [
        'sectionname' => 'Modul 1: Einfuehrung & Rollenverstaendnis',
        'activityname' => 'Interaktives Lernmodul starten',
        'summary' => '<p>Rolle verstehen, Grenzen erkennen und das Wissen im Abschlussquiz pruefen.</p>',
        'legacy' => ['Modul 1: Einfuehrung & Rollenverstaendnis'],
        'url' => '/local/p4u/module1.php',
        'completion' => 'view',
    ],
    2 => [
        'sectionname' => 'Modul 2: Finanzwissen',
        'activityname' => 'Finanzwissen vorbereiten',
        'summary' => '<p>Geplant: Finanzgrundlagen, Konsum, digitale Kostenfallen und Schuldenrisiken.</p>',
        'legacy' => ['Modul 2: Finanzwissen'],
        'url' => '/local/p4u/module_placeholder.php?module=2',
    ],
    3 => [
        'sectionname' => 'Modul 3: Kommunikation und Gespraechsfuehrung',
        'activityname' => 'Kommunikation vorbereiten',
        'summary' => '<p>Geplant: aktives Zuhoeren, gute Fragen und Gespraechsstruktur.</p>',
        'legacy' => ['Modul 3: Kommunikation & Gespraechsfuehrung'],
        'url' => '/local/p4u/module_placeholder.php?module=3',
    ],
    4 => [
        'sectionname' => 'Modul 4: Digitale Sicherheit und Datenschutz',
        'activityname' => 'Digitale Sicherheit vorbereiten',
        'summary' => '<p>Geplant: vertrauliche und sichere digitale Peer-Beratung.</p>',
        'legacy' => ['Modul 4: Digitale Sicherheit & Datenschutz'],
        'url' => '/local/p4u/module_placeholder.php?module=4',
    ],
    5 => [
        'sectionname' => 'Modul 5: Krisen erkennen und weiterverweisen',
        'activityname' => 'Krisenmodul vorbereiten',
        'summary' => '<p>Geplant: Warnsignale, Rollengrenzen und Weitervermittlung.</p>',
        'legacy' => ['Modul 5: Krisen erkennen & weiterverweisen'],
        'url' => '/local/p4u/module_placeholder.php?module=5',
    ],
    6 => [
        'sectionname' => 'Modul 6: Abschluss und Zertifikat',
        'activityname' => 'Abschluss vorbereiten',
        'summary' => '<p>Geplant: Wiederholung, Reflexion und Abschluss der Ausbildung.</p>',
        'legacy' => ['Modul 6: Abschluss & Zertifikat'],
        'url' => '/local/p4u/module_placeholder.php?module=6',
    ],
];

function p4u_training_get_course(int $courseid, string $shortname): stdClass {
    global $DB;

    if ($courseid > 0) {
        return get_course($courseid);
    }

    $course = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
    return get_course($course->id);
}

function p4u_training_moduleinfo(stdClass $course, int $sectionnum, string $name, string $introhtml, string $completion = 'manual'): stdClass {
    global $DB;

    $moduleid = $DB->get_field('modules', 'id', ['name' => 'url'], MUST_EXIST);

    $mi = new stdClass();
    $mi->course = $course->id;
    $mi->section = $sectionnum;
    $mi->module = $moduleid;
    $mi->modulename = 'url';
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
    $mi->completion = $completion === 'view' ? COMPLETION_TRACKING_AUTOMATIC : COMPLETION_TRACKING_MANUAL;
    $mi->completionview = $completion === 'view' ? 1 : 0;
    $mi->completionexpected = 0;
    $mi->availabilityconditionsjson = null;
    $mi->showdescription = 1;
    $mi->display = RESOURCELIB_DISPLAY_OPEN;

    return $mi;
}

function p4u_training_find_activity(int $courseid, int $sectionnum, array $activitynames): ?cm_info {
    $modinfo = get_fast_modinfo($courseid);
    $sections = $modinfo->get_sections();
    if (empty($sections[$sectionnum])) {
        return null;
    }
    foreach ($sections[$sectionnum] as $cmid) {
        $cm = $modinfo->get_cm($cmid);
        if (in_array(trim($cm->name), array_map('trim', $activitynames), true)) {
            return $cm;
        }
    }
    return null;
}

function p4u_training_update_or_add_url(stdClass $course, int $sectionnum, array $module): void {
    global $CFG, $DB;

    $externalurl = $CFG->wwwroot . $module['url'];
    $externalurl .= str_contains($externalurl, '?') ? '&courseid=' . $course->id : '?courseid=' . $course->id;

    $names = array_merge([$module['activityname']], $module['legacy'] ?? [], ['Modul vorbereiten']);
    if ($cm = p4u_training_find_activity($course->id, $sectionnum, $names)) {
        $DB->set_field('url', 'name', $module['activityname'], ['id' => $cm->instance]);
        $DB->set_field('url', 'externalurl', $externalurl, ['id' => $cm->instance]);
        $DB->set_field('url', 'intro', $module['summary'], ['id' => $cm->instance]);
        $DB->set_field('url', 'introformat', FORMAT_HTML, ['id' => $cm->instance]);
        $completion = ($module['completion'] ?? 'manual') === 'view' ? COMPLETION_TRACKING_AUTOMATIC : COMPLETION_TRACKING_MANUAL;
        $DB->set_field('course_modules', 'completion', $completion, ['id' => $cm->id]);
        $DB->set_field('course_modules', 'completionview', $completion === COMPLETION_TRACKING_AUTOMATIC ? 1 : 0, ['id' => $cm->id]);
        mtrace("  = Updated {$module['activityname']}");
        return;
    }

    $mi = p4u_training_moduleinfo($course, $sectionnum, $module['activityname'], $module['summary'], $module['completion'] ?? 'manual');
    $mi->externalurl = $externalurl;

    $result = add_moduleinfo($mi, $course);
    mtrace("  + Added {$module['activityname']} (cmid={$result->coursemodule})");
}

try {
    $admin = get_admin();
    \core\session\manager::set_user($admin);

    $course = p4u_training_get_course((int)$options['courseid'], (string)$options['shortname']);

    course_create_sections_if_missing($course, array_keys($modules));

    foreach ($modules as $sectionnum => $module) {
        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $sectionnum], '*', MUST_EXIST);
        course_update_section($course, $section, [
            'name' => $module['sectionname'],
            'summary' => $module['summary'],
            'summaryformat' => FORMAT_HTML,
        ]);
        p4u_training_update_or_add_url($course, $sectionnum, $module);
    }

    rebuild_course_cache($course->id, true);
    mtrace("Done: P4U training modules created/updated in course {$course->id}.");
    mtrace("Open: {$CFG->wwwroot}/course/view.php?id={$course->id}");
    exit(0);
} catch (Throwable $e) {
    mtrace('ERROR: ' . $e->getMessage());
    mtrace($e->getTraceAsString());
    exit(1);
}
