#!/usr/bin/env php
<?php
/**
 * Peer4You CLI — Kursstruktur für 6 Schulungsmodule anlegen oder aktualisieren.
 *
 * Legt pro Modul einen Kursabschnitt und eine URL-Aktivität an (moduleN.php).
 * Wendet danach die Availability-Kette M1 → M2 → M3 → M4 → M5 an (Completion nach Quiz).
 * Läuft gegen die Datenbank — nach Deploy auf jedem Server erneut ausführen.
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
    echo "  --courseid=ID       Target course id. If omitted, course shortname P4U-TEST is created or reused.\n";
    echo "  --shortname=NAME    Course shortname if no courseid is given (default: P4U-TEST).\n";
    exit(0);
}

$modules = [
    1 => [
        'sectionname' => 'Modul 1: Einführung & Rollenverständnis',
        'activityname' => 'Interaktives Lernmodul starten',
        'summary' => '<p>Rolle verstehen, Grenzen erkennen und das Wissen im Abschlussquiz prüfen.</p>',
        'legacy' => ['Modul 1: Einfuehrung & Rollenverstaendnis'],
        'url' => '/local/p4u/module1.php',
        'completion' => 'manual',
    ],
    2 => [
        'sectionname' => 'Modul 2: Finanzwissen',
        'activityname' => 'Interaktives Lernmodul Finanzwissen starten',
        'summary' => '<p>Finanzgrundlagen, Zinsen, Kreditrisiken, Budgetplan und Schuldnerberatung interaktiv erarbeiten.</p>',
        'legacy' => ['Modul 2: Finanzwissen', 'Finanzwissen vorbereiten'],
        'url' => '/local/p4u/module2.php',
        'completion' => 'manual',
    ],
    3 => [
        'sectionname' => 'Modul 3: Kommunikation und Gesprächsführung',
        'activityname' => 'Interaktives Lernmodul Kommunikation starten',
        'summary' => '<p>Online-Kommunikation, wertschätzende Sprache, Grenzen und einen sicheren Erstkontakt interaktiv üben.</p>',
        'legacy' => ['Kommunikation vorbereiten', 'Modul 3: Kommunikation & Gespraechsfuehrung', 'Modul 3: Kommunikation und Gespraechsfuehrung'],
        'url' => '/local/p4u/module3.php',
        'completion' => 'manual',
    ],
    4 => [
        'sectionname' => 'Modul 4: Digitale Sicherheit und Datenschutz',
        'activityname' => 'Interaktives Lernmodul Digitale Sicherheit starten',
        'summary' => '<p>Anonymität, Datenminimierung, sichere Plattformen und Datenschutz interaktiv erarbeiten.</p>',
        'legacy' => ['Digitale Sicherheit vorbereiten', 'Modul 4: Digitale Sicherheit & Datenschutz'],
        'url' => '/local/p4u/module4.php',
        'completion' => 'manual',
    ],
    5 => [
        'sectionname' => 'Modul 5: Krisen erkennen und weiterverweisen',
        'activityname' => 'Interaktives Lernmodul Krisen erkennen starten',
        'summary' => '<p>Warnsignale erkennen, Peer-Rolle abgrenzen und passende Weiterverweisung üben.</p>',
        'legacy' => ['Modul 5: Krisen erkennen & weiterverweisen', 'Krisenmodul vorbereiten'],
        'url' => '/local/p4u/module5.php',
        'completion' => 'manual',
    ],
    6 => [
        'sectionname' => 'Modul 6: Abschluss und Zertifikat',
        'activityname' => 'Abschluss vorbereiten',
        'summary' => '<p>Geplant: Wiederholung, Reflexion und Abschluss der Ausbildung.</p>',
        'legacy' => ['Modul 6: Abschluss & Zertifikat'],
        'url' => '/local/p4u/module_placeholder.php?module=6',
    ],
];

function p4u_training_get_or_create_course(int $courseid, string $shortname): stdClass {
    global $DB;

    $shortname = clean_param($shortname, PARAM_ALPHANUMEXT);

    if ($courseid > 0) {
        if (!$DB->record_exists('course', ['id' => $courseid])) {
            cli_error("Course id {$courseid} not found. Omit --courseid to create/reuse shortname {$shortname}, or check Site administration → Courses.");
        }
        return get_course($courseid);
    }

    if ($course = $DB->get_record('course', ['shortname' => $shortname])) {
        $updates = [];
        if ($course->fullname !== 'Peers for You - Ausbildung') {
            $updates['fullname'] = 'Peers for You - Ausbildung';
        }
        $expectedsummary = '<p>Ausbildungskurs für Peer-Berater:innen.</p>';
        if ($course->summary !== $expectedsummary) {
            $updates['summary'] = $expectedsummary;
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
    mtrace("Created course: {$course->fullname} (id={$course->id}, shortname={$shortname})");
    return get_course($course->id);
}

function p4u_training_enrol_admin_user(stdClass $course, stdClass $admin): void {
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
    }

    if ($roleid > 0 && !user_has_role_assignment($admin->id, $roleid, $context->id)) {
        role_assign($roleid, $admin->id, $context->id);
    }
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

/**
 * Apply sequential module availability chain.
 *
 * Chain: M1 → M2 → M3 → M4 → M5.
 * M6 remains a placeholder and is excluded from the chain.
 *
 * TODO (production): Once M6 receives real content, update the chain to
 * 1 → 2 → 3 → 4 → 5 → 6 and re-run this script on the server.
 * See: Notizen/Entwicklung To-Do.md
 *
 * @param stdClass              $course
 * @param array<int,array>      $modules  The global $modules definition array
 */
function p4u_training_apply_availability_chain(stdClass $course, array $modules): void {
    global $DB;

    // Nächstes Modul erst nach Activity Completion des Vorgängers (Quiz ≥ 60 %).
    $chain = [
        2 => 1, // Modul 2 erst nach Abschluss Modul 1
        3 => 2, // Modul 3 erst nach Abschluss Modul 2
        4 => 3, // Modul 4 erst nach Abschluss Modul 3
        5 => 4, // Modul 5 erst nach Abschluss Modul 4
    ];

    // Flush fast-modinfo cache so we get up-to-date cmids after add/update.
    get_fast_modinfo($course->id, 0, true);

    foreach ($chain as $targetSection => $prereqSection) {
        $prereqNames = array_merge(
            [$modules[$prereqSection]['activityname']],
            $modules[$prereqSection]['legacy'] ?? []
        );
        $targetNames = array_merge(
            [$modules[$targetSection]['activityname']],
            $modules[$targetSection]['legacy'] ?? []
        );

        $prereqCm = p4u_training_find_activity($course->id, $prereqSection, $prereqNames);
        $targetCm = p4u_training_find_activity($course->id, $targetSection, $targetNames);

        if (!$prereqCm || !$targetCm) {
            mtrace("  ! Availability chain: activity not found for section {$prereqSection} or {$targetSection}, skipping.");
            continue;
        }

        $availability = json_encode([
            'op'    => '&',
            'c'     => [['type' => 'completion', 'cm' => (int)$prereqCm->id, 'e' => 1]],
            'showc' => [true],
        ]);
        $DB->set_field('course_modules', 'availability', $availability, ['id' => $targetCm->id]);
        mtrace("  = Modul {$targetSection} (cm={$targetCm->id}): gesperrt bis Abschluss Modul {$prereqSection} (cm={$prereqCm->id}).");
    }
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

    $course = p4u_training_get_or_create_course((int)$options['courseid'], (string)$options['shortname']);
    p4u_training_enrol_admin_user($course, $admin);

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

    // Set sequential availability: M1 → M2 → M3 → M4 → M5 (M6 remains a placeholder).
    p4u_training_apply_availability_chain($course, $modules);

    rebuild_course_cache($course->id, true);
    mtrace("Done: P4U training modules created/updated in course {$course->id}.");
    mtrace("Open: {$CFG->wwwroot}/course/view.php?id={$course->id}");
    exit(0);
} catch (Throwable $e) {
    mtrace('ERROR: ' . $e->getMessage());
    mtrace($e->getTraceAsString());
    exit(1);
}
