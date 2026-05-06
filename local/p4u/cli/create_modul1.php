#!/usr/bin/env php
<?php
/**
 * CLI script: Create "Modul 1: Einführung & Rollenverständnis" in an existing Moodle course.
 *
 * Usage:
 *   php local/p4u/cli/create_modul1.php
 *   php local/p4u/cli/create_modul1.php --courseid=5
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

global $DB;

$defaultcourseid = 2;
$defaultsection = 1;
list($options) = cli_get_params(
    ['help' => false, 'courseid' => $defaultcourseid, 'section' => $defaultsection],
    ['h' => 'help']
);

if (!empty($options['help'])) {
    $help = <<<HELP
Create P4U Modul 1 in section 1.

Options:
  --courseid=ID   Target course id (default: {$defaultcourseid})
  --section=NUM   Target section number in the course (default: {$defaultsection})
  -h, --help      Show help

Example:
  php local/p4u/cli/create_modul1.php --courseid=7

HELP;
    echo $help . PHP_EOL;
    exit(0);
}

$courseid = (int)$options['courseid'];
$targetsection = (int)$options['section'];
if ($courseid <= 0) {
    cli_error("Invalid courseid.");
}
if ($targetsection < 0) {
    cli_error("Invalid section number.");
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
    $mi->completion = COMPLETION_DISABLED;
    $mi->completionview = 0;
    $mi->completionexpected = 0;
    $mi->availabilityconditionsjson = null;
    $mi->showdescription = 0;

    return $mi;
}

/**
 * Add module and return resulting cm info.
 */
function p4u_add_module(stdClass $course, stdClass $moduleinfo): stdClass {
    $result = add_moduleinfo($moduleinfo, $course);
    mtrace("  + Added {$moduleinfo->modulename}: {$moduleinfo->name} (cmid={$result->coursemodule})");
    return $result;
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

try {
    $admin = get_admin();
    if (!$admin) {
        throw new moodle_exception('noadmin');
    }
    \core\session\manager::set_user($admin);

    $course = get_course($courseid);
    if (!$course) {
        throw new moodle_exception('invalidcourseid', '', '', $courseid);
    }

    course_create_sections_if_missing($course, [$targetsection]);

    $sectionrecord = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $targetsection], '*', MUST_EXIST);
    $sectionsummary = <<<HTML
<h2>Modul 1: Einführung &amp; Rollenverständnis</h2>
<p><strong>Dauer:</strong> ca. 10 Minuten</p>
<p>Was ist Peer-Beratung und welche Rolle/Grenzen hat ein Peer?</p>
HTML;

    course_update_section($course, $sectionrecord, [
        'name' => 'Modul 1: Einführung',
        'summary' => $sectionsummary,
        'summaryformat' => FORMAT_HTML,
    ]);

    mtrace("Section {$targetsection} prepared: 'Modul 1: Einführung'");

    // Activity 1: Label.
    $introhtml = <<<HTML
<h1>Willkommen bei Peers for You</h1>
<h2>Was ist Peer-Beratung und warum bist du hier genau richtig?</h2>
<p>
Das Diakonische Werk Baden-Baden / Rastatt bietet verschiedene Dienstleistungen an:
Suchtberatung, Allgemeine Sozialberatung, Schuldenprävention, Schuldnerberatung
und Schwangerenberatung.
</p>
<div style="border:1px solid #d9d9d9; border-radius:8px; padding:12px; background:#f9fbfd; margin:12px 0;">
    <p><strong>Das Diakonische Werk Rastatt</strong></p>
    <p>
    Du wirst als Peer-Berater:in eingesetzt — das heißt, du berätst andere junge Menschen
    auf Augenhöhe, anonym und niederschwellig.
    </p>
</div>
<h2>Lernziele in diesem Modul</h2>
<ul>
    <li>Peer-Beratung als niedrigschwellige Unterstützung auf Augenhöhe verstehen</li>
    <li>Kernprinzipien benennen: auf Augenhöhe, vertraulich, niedrigschwellig</li>
    <li>Rolle von Peer-Berater:innen vom professionellen Beratungsauftrag abgrenzen</li>
    <li>Erlaubte vs. nicht erlaubte Handlungen in der Peer-Rolle sicher unterscheiden</li>
    <li>In einem Fallbeispiel angemessene Peer-Schritte auswählen</li>
    <li>Wissen im Abschlussquiz anwenden (Bestehensgrenze: 60%)</li>
</ul>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Einführung')) {
        $mi = p4u_build_moduleinfo($course, $targetsection, 'label', 'Einführung', $introhtml);
        p4u_add_module($course, $mi);
    } else {
        mtrace("  = Skip label: Einführung (already exists)");
    }

    // Activity 2: H5P Lernkarten.
    $h5p1intro = <<<HTML
<p><strong>Placeholder:</strong> Lernkarten (Flashcards)</p>
<p>Geplante Karten: Was ist Peer-Beratung? · Warum wirksam? · Rolle als Peer · Was Peers nicht tun</p>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Lernkarten')) {
        $fallback = p4u_build_moduleinfo(
            $course,
            $targetsection,
            'label',
            'Lernkarten',
            '<p><strong>Platzhalter:</strong> H5P Flashcards folgen später.</p>'
        );
        p4u_add_module($course, $fallback);
    } else {
        mtrace("  = Skip h5pactivity/label: Lernkarten (already exists)");
    }

    // Activity 3: Lesson Rollenverständnis.
    $lessonintro = <<<HTML
<p>Rollenverständnis: Was dürfen Peer-Berater:innen, wo sind die Grenzen?</p>
<ul>
    <li><strong>Peers dürfen:</strong> Zuhören, informieren, motivieren, weitervermitteln</li>
    <li><strong>Peers dürfen nicht:</strong> Rechtliche Beratung, Finanzprodukte empfehlen</li>
</ul>
<p>Die Lektion ist als Basis vorbereitet. Inhalts-/Fragenseiten können später programmatisch ergänzt werden.</p>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Rollenverständnis')) {
        $mi = p4u_build_moduleinfo($course, $targetsection, 'lesson', 'Rollenverständnis', $lessonintro);
        $mi->practice = 0;
        $mi->modattempts = 0;
        $mi->usepassword = 0;
        $mi->password = '';
        $mi->dependency = 0;
        $mi->timelimit = 0;
        $mi->retake = 1;
        $mi->progressbar = 1;
        $mi->displayleft = 0;
        $mi->grade = 0;
        $mi->maxanswers = 4;
        $mi->maxattempts = 5;
        p4u_add_module($course, $mi);
    } else {
        mtrace("  = Skip lesson: Rollenverständnis (already exists)");
    }

    // Activity 4: H5P Drag & Drop.
    $h5p2intro = <<<HTML
<p><strong>Placeholder:</strong> Drag &amp; Drop</p>
<p>Lernauftrag: Ordne Aktivitäten korrekt zu: „Peers dürfen“ vs. „Peers dürfen nicht“.</p>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Drag & Drop')) {
        $fallback = p4u_build_moduleinfo(
            $course,
            $targetsection,
            'label',
            'Drag & Drop',
            '<p><strong>Platzhalter:</strong> H5P Drag &amp; Drop folgt später.</p>'
        );
        p4u_add_module($course, $fallback);
    } else {
        mtrace("  = Skip h5pactivity/label: Drag & Drop (already exists)");
    }

    // Activity 5: URL Video.
    $videointro = <<<HTML
<p>Thema: Peer-Beratung in der Praxis</p>
<p>Ziel: Typischen Ablauf eines Peer-Gesprächs nachvollziehen.</p>
<p><em>Video-URL wird später ergänzt.</em></p>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Video')) {
        $mi = p4u_build_moduleinfo($course, $targetsection, 'url', 'Video', $videointro);
        $mi->externalurl = 'https://example.org/video-placeholder';
        $mi->display = 0;
        p4u_add_module($course, $mi);
    } else {
        mtrace("  = Skip url: Video (already exists)");
    }

    // Activity 6: Lesson Fallbeispiel.
    $fallintro = <<<HTML
<p><strong>Fall:</strong> Marko, 22 Jahre; Studienabbruch, Minijob, ca. 4.500 EUR Schulden, Überforderung/Isolation.</p>
<p><strong>Erwünschtes Vorgehen:</strong> Zuhören und validieren, professionelle Hilfe motivieren, an Schuldnerberatung weitervermitteln.</p>
<p><strong>Zu vermeiden:</strong> Eigene Schuldenregulierungsratschläge, rechtliche Tipps.</p>
<p><strong>Merksatz:</strong> Zuhören, Verständnis zeigen, weitervermitteln – nicht das Problem selbst lösen.</p>
<p>Diese Lektion ist als Grundlage für Branch-Table/entscheidungsorientierte Seiten vorbereitet.</p>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Fallbeispiel')) {
        $mi = p4u_build_moduleinfo($course, $targetsection, 'lesson', 'Fallbeispiel', $fallintro);
        $mi->practice = 0;
        $mi->modattempts = 0;
        $mi->usepassword = 0;
        $mi->password = '';
        $mi->dependency = 0;
        $mi->timelimit = 0;
        $mi->retake = 1;
        $mi->progressbar = 1;
        $mi->displayleft = 0;
        $mi->grade = 0;
        $mi->maxanswers = 4;
        $mi->maxattempts = 5;
        p4u_add_module($course, $mi);
    } else {
        mtrace("  = Skip lesson: Fallbeispiel (already exists)");
    }

    // Activity 7: Quiz.
    $quizintro = <<<HTML
<p>Abschlussquiz zu Modul 1.</p>
<ul>
    <li>Fragenfokus: Dienstleistungen der Diakonie, Einsatzbereich Peer-Beratung, Nutzen von Beratung durch Gleichaltrige</li>
    <li>Bestehensgrenze: 60%</li>
</ul>
HTML;
    if (!p4u_activity_exists($course->id, $targetsection, 'Quiz')) {
        $fallback = p4u_build_moduleinfo(
            $course,
            $targetsection,
            'label',
            'Quiz',
            '<p><strong>Platzhalter:</strong> Abschlussquiz wird später ergänzt. Bestehensgrenze: 60%.</p>'
        );
        p4u_add_module($course, $fallback);
    } else {
        mtrace("  = Skip quiz/label: Quiz (already exists)");
    }

    rebuild_course_cache($course->id, true);
    mtrace("Done: Modul 1 created successfully in course {$course->id}.");
    exit(0);

} catch (Throwable $e) {
    mtrace("ERROR: " . $e->getMessage());
    mtrace($e->getTraceAsString());
    exit(1);
}
