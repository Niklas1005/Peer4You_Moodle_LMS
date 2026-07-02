<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Placeholder pages for upcoming P4U modules.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$module = optional_param('module', 2, PARAM_INT);

$modules = [
    2 => [
        'title' => 'Finanzwissen',
        'lead' => 'Grundlagen zu Geld, Konsum, digitalen Kostenfallen und Schuldenrisiken.',
        'goals' => [
            'wichtige Finanzbegriffe einordnen',
            'typische Schuldenrisiken bei jungen Menschen erkennen',
            'bei Bedarf an passende Beratung weitervermitteln',
        ],
        'stations' => ['Einstieg', 'Finanzbasics', 'Kostenfallen', 'Risiken', 'Praxisfall', 'Quiz'],
    ],
    3 => [
        'title' => 'Kommunikation & Gespr&auml;chsf&uuml;hrung',
        'lead' => 'Aktives Zuh&ouml;ren, gute Fragen und ein sicherer Gespr&auml;chsrahmen.',
        'goals' => [
            'Gespr&auml;che wertsch&auml;tzend beginnen',
            'offene Fragen stellen und aktiv zuh&ouml;ren',
            'ein Gespr&auml;ch sinnvoll strukturieren',
        ],
        'stations' => ['Einstieg', 'Zuh&ouml;ren', 'Fragen', 'Struktur', 'Praxisfall', 'Quiz'],
    ],
    4 => [
        'title' => 'Digitale Sicherheit & Datenschutz',
        'lead' => 'Vertraulichkeit, sichere Kommunikation und verantwortlicher Umgang mit Daten.',
        'goals' => [
            'vertrauliche Informationen richtig einordnen',
            'digitale Beratungsanfragen sicher bearbeiten',
            'Grenzen und Verantwortlichkeiten erkennen',
        ],
        'stations' => ['Einstieg', 'Datenschutz', 'Sicherheit', 'Grenzen', 'Praxisfall', 'Quiz'],
    ],
    5 => [
        'title' => 'Krisen erkennen & weiterverweisen',
        'lead' => 'Warnsignale wahrnehmen, Verantwortung kl&auml;ren und passende Fachhilfe einbinden.',
        'goals' => [
            'kritische Situationen erkennen',
            'die eigene Peer-Rolle in Krisen abgrenzen',
            'sicher und schnell weitervermitteln',
        ],
        'stations' => ['Einstieg', 'Warnsignale', 'Grenzen', 'Weiterverweisen', 'Praxisfall', 'Quiz'],
    ],
    6 => [
        'title' => 'Abschluss & Zertifikat',
        'lead' => 'Wissen b&uuml;ndeln, die Peer-Rolle reflektieren und die Ausbildung abschlie&szlig;en.',
        'goals' => [
            'zentrale Inhalte der Ausbildung wiederholen',
            'die eigene Rolle reflektieren',
            'den Abschluss der Ausbildung vorbereiten',
        ],
        'stations' => ['R&uuml;ckblick', 'Wiederholung', 'Reflexion', 'Abschlussquiz', 'Ausblick'],
    ],
];

if (!isset($modules[$module])) {
    throw new moodle_exception('invalidmodule', 'local_p4u');
}

$course = get_course($courseid);
$context = $courseid == SITEID ? context_system::instance() : context_course::instance($course->id);
$data = $modules[$module];

require_login($course);

$url = new moodle_url('/local/p4u/module_placeholder.php', ['courseid' => $course->id, 'module' => $module]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('P4U Modul ' . $module);
$PAGE->set_heading('Modul ' . $module . ': ' . strip_tags(html_entity_decode($data['title'])));
$PAGE->requires->css(new moodle_url('/local/p4u/styles.css'));

echo $OUTPUT->header();
?>
<div class="p4u-shell">
    <header class="p4u-module-hero">
        <p class="p4u-label">Modul <?php echo $module; ?></p>
        <h1><?php echo $data['title']; ?></h1>
        <p><?php echo $data['lead']; ?></p>
        <div class="p4u-journey" aria-label="Geplanter Lernpfad">
            <?php foreach ($data['stations'] as $station): ?>
                <span><?php echo $station; ?></span>
            <?php endforeach; ?>
        </div>
    </header>

    <div class="p4u-card p4u-goals">
        <p class="p4u-label">In Vorbereitung</p>
        <h2>Dieses Modul wird vorbereitet</h2>
        <p class="p4u-lead">Die Struktur steht bereits, die konkreten Lerninhalte werden sp&auml;ter erg&auml;nzt.</p>
    </div>

    <div class="p4u-columns">
        <div class="p4u-card">
            <h3>Geplante Lernziele</h3>
            <ul>
                <?php foreach ($data['goals'] as $goal): ?>
                    <li><?php echo $goal; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="p4u-card">
            <h3>Geplanter Aufbau</h3>
            <ul>
                <?php foreach ($data['stations'] as $station): ?>
                    <li><?php echo $station; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="p4u-card p4u-recap">
        <h3>N&auml;chster Schritt</h3>
        <p>F&uuml;r dieses Modul werden noch konkrete Beispiele, Interaktionen und Quizfragen ausgearbeitet.</p>
        <div class="p4u-controls">
            <a class="p4u-primary p4u-linkbutton" href="<?php echo (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false); ?>">Zur&uuml;ck zum Kurs</a>
        </div>
    </div>
</div>
<?php
echo $OUTPUT->footer();
