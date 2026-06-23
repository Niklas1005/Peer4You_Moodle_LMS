<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Peer4You Modul 5 — Interaktive Lernseite (Krisen erkennen & weiterverweisen).
 *
 * Stationen: Warnsignale, Peer-Rolle, Weiterverweisung, Notfall, Fall, Quiz.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$context = $courseid == SITEID ? context_system::instance() : context_course::instance($course->id);

require_login($course);

$url = new moodle_url('/local/p4u/module5.php', ['courseid' => $course->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('P4U Modul 5');
$PAGE->set_heading('Modul 5: Krisen erkennen und weiterverweisen');
$PAGE->requires->css(new moodle_url('/local/p4u/styles.css'));
$PAGE->requires->js(new moodle_url('/local/p4u/module-progress.js'));
$PAGE->requires->js(new moodle_url('/local/p4u/module5.js'));

echo $OUTPUT->header();
?>
<div class="p4u-shell"
     data-user-id="<?php echo (int)$USER->id; ?>"
     data-progress-url="<?php echo (new moodle_url('/local/p4u/module_state.php', ['courseid' => $course->id, 'module' => 5, 'sesskey' => sesskey()]))->out(false); ?>"
     data-complete-url="<?php echo (new moodle_url('/local/p4u/complete_module.php', ['courseid' => $course->id, 'module' => 5, 'action' => 'complete', 'sesskey' => sesskey()]))->out(false); ?>"
     data-status-url="<?php echo (new moodle_url('/local/p4u/complete_module.php', ['courseid' => $course->id, 'module' => 5, 'action' => 'status']))->out(false); ?>">
    <header class="p4u-module-hero">
        <p class="p4u-label">Modul 5</p>
        <h1>Krisen erkennen &amp; weiterverweisen</h1>
        <p>Du erkennst Warnsignale, reagierst empathisch und leitest Ratsuchende an passende Fachstellen weiter.</p>
        <div class="p4u-status-card" aria-live="polite">
            <div>
                <span>Status</span>
                <strong id="p4u-status-label">Offen</strong>
            </div>
            <div>
                <span>Quizversuche</span>
                <strong id="p4u-status-attempts">0</strong>
            </div>
            <div>
                <span>Bestanden</span>
                <strong id="p4u-status-passed">0</strong>
            </div>
            <p id="p4u-status-note">Schlie&szlig;e das Quiz ab, um Modul 5 im Kurs als erledigt zu markieren.</p>
        </div>
        <div class="p4u-journey" aria-label="Lernpfad">
            <span>Einstieg</span>
            <span>Warnsignale</span>
            <span>Peer-Rolle</span>
            <span>Weiterverweisen</span>
            <span>Notfall</span>
            <span>Fall</span>
            <span>Quiz</span>
        </div>
    </header>

    <div class="p4u-progress" aria-label="Modulfortschritt">
        <span id="p4u-progress-label">Einstieg &middot; Station 1 von 7</span>
        <div class="p4u-progressbar"><span id="p4u-progress-fill" style="width:14%"></span></div>
        <strong id="p4u-progress-pct">14 %</strong>
    </div>

    <div class="p4u-layout">
        <nav class="p4u-nav" aria-label="Modulnavigation">
            <button class="active" data-target="intro">1. Einstieg</button>
            <button data-target="signals">2. Warnsignale</button>
            <button data-target="role">3. Peer-Rolle</button>
            <button data-target="referral">4. Weiterverweisen</button>
            <button data-target="emergency">5. Notfall</button>
            <button data-target="case">6. Fallbeispiel</button>
            <button data-target="quiz" id="p4u-quiz-nav" class="locked">7. Abschlussquiz</button>
        </nav>

        <main class="p4u-content">
            <section id="p4u-sec-intro" class="p4u-section active">
                <p class="p4u-label">Station 1 &middot; Einstieg</p>
                <h2>Warum dieses Modul wichtig ist</h2>
                <p class="p4u-lead">In Peer-Beratung k&ouml;nnen finanzielle, rechtliche oder psychische Krisen sichtbar werden. Deine Aufgabe ist nicht, alles zu l&ouml;sen, sondern richtig zu reagieren.</p>
                <span class="p4u-pill">Dauer: ca. 10 Minuten</span>
                <div class="p4u-card p4u-goals">
                    <h3>Nach diesem Modul kannst du</h3>
                    <ul>
                        <li>finanzielle, rechtliche und psychische Warnsignale erkennen,</li>
                        <li>deine Peer-Rolle in Krisensituationen klar abgrenzen,</li>
                        <li>ruhig und empathisch reagieren, ohne Diagnosen zu stellen,</li>
                        <li>passende Anlaufstellen zur Weiterverweisung nennen.</li>
                    </ul>
                </div>
                <button class="p4u-primary" data-next="signals">Weiter: Warnsignale</button>
            </section>

            <section id="p4u-sec-signals" class="p4u-section">
                <p class="p4u-label">Station 2 &middot; Warnsignale</p>
                <h2>Krisenanzeichen einordnen</h2>
                <p class="p4u-lead">Nicht jedes Problem ist ein Notfall. Aber bestimmte Hinweise zeigen, dass professionelle Hilfe wichtig wird.</p>
                <div class="p4u-dropgrid">
                    <div class="p4u-card">
                        <h3>Hinweise</h3>
                        <button class="p4u-drag" draggable="true" data-zone="finance">Mahnungen und Inkassoschreiben</button>
                        <button class="p4u-drag" draggable="true" data-zone="legal">Gerichtliches Schreiben oder Zwangsvollstreckung</button>
                        <button class="p4u-drag" draggable="true" data-zone="mental">R&uuml;ckzug, Verzweiflung, Schlafprobleme</button>
                        <button class="p4u-drag" draggable="true" data-zone="mental">Aussage: Ich sehe keinen Ausweg</button>
                        <button class="p4u-drag" draggable="true" data-zone="finance">Panik wegen offener Rechnungen</button>
                        <button class="p4u-drag" draggable="true" data-zone="legal">Mietk&uuml;ndigung</button>
                    </div>
                    <div class="p4u-card p4u-dropzone" data-zone="finance">
                        <h3>Finanziell</h3>
                        <p>Zahlungsr&uuml;ckst&auml;nde, Mahnungen, Inkasso, drohende Pf&auml;ndung.</p>
                    </div>
                    <div class="p4u-card p4u-dropzone" data-zone="legal">
                        <h3>Rechtlich</h3>
                        <p>K&uuml;ndigung, gerichtliche Schreiben, Vollstreckung, hoher Druck.</p>
                    </div>
                    <div class="p4u-card p4u-dropzone" data-zone="mental">
                        <h3>Psychisch</h3>
                        <p>Hoffnungslosigkeit, R&uuml;ckzug, Schlafprobleme, Suizidgedanken.</p>
                    </div>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-check-signals">Antwort pr&uuml;fen</button>
                    <button class="p4u-secondary" id="p4u-reset-signals">Zur&uuml;cksetzen</button>
                </div>
                <div class="p4u-result" id="p4u-signals-result" aria-live="polite"></div>
                <button class="p4u-primary" data-next="role">Weiter: Peer-Rolle</button>
            </section>

            <section id="p4u-sec-role" class="p4u-section">
                <p class="p4u-label">Station 3 &middot; Peer-Rolle</p>
                <h2>Was du tust und was nicht</h2>
                <p class="p4u-lead">Krisenarbeit braucht Klarheit. Peers geben Orientierung, ersetzen aber keine Fachberatung.</p>
                <div class="p4u-card p4u-casechoice">
                    <h3>Welche Reaktion passt zur Peer-Rolle?</h3>
                    <div class="p4u-role-options p4u-case-options">
                        <button data-correct="false" data-feedback="Nicht passend: Diagnosen geh&ouml;ren nicht zur Peer-Rolle.">Du wirkst depressiv, ich glaube du hast eine Diagnose.</button>
                        <button data-correct="true" data-feedback="Richtig: Ruhig bleiben, ernst nehmen und an passende Hilfe weiterleiten.">Das klingt sehr belastend. Ich h&ouml;re dir zu und wir schauen gemeinsam, welche Fachstelle jetzt passt.</button>
                        <button data-correct="false" data-feedback="Nicht passend: Keine falschen Versprechen machen.">Ich verspreche dir, dass ich das Problem f&uuml;r dich l&ouml;se.</button>
                    </div>
                    <p class="p4u-case-feedback" id="p4u-role-feedback" aria-live="polite"></p>
                </div>
                <details class="p4u-deep-dive">
                    <summary>Merksatz zur Rolle</summary>
                    <span class="p4u-deep-dive-label">Orientierung</span>
                    <p>Peers h&ouml;ren zu, stabilisieren kurz, sortieren die n&auml;chsten Schritte und verweisen weiter. Sie stellen keine Diagnosen und &uuml;bernehmen keine komplexe Problembearbeitung.</p>
                </details>
                <button class="p4u-primary" data-next="referral">Weiter: Weiterverweisen</button>
            </section>

            <section id="p4u-sec-referral" class="p4u-section">
                <p class="p4u-label">Station 4 &middot; Weiterverweisen</p>
                <h2>Passende Anlaufstellen w&auml;hlen</h2>
                <p class="p4u-lead">Gute Weiterverweisung ist konkret: Welche Stelle passt, wie erreicht man sie und was ist der n&auml;chste kleine Schritt?</p>
                <div class="p4u-referral-board">
                    <div class="p4u-tool-head">
                        <p class="p4u-label">&Uuml;bung</p>
                        <h3>Welche Weiterverweisung passt?</h3>
                        <p>W&auml;hle pro Situation die sinnvollste erste Anlaufstelle. Die R&uuml;ckmeldung erscheint direkt darunter.</p>
                    </div>
                    <div class="p4u-referral-case">
                        <div class="p4u-referral-scenario">
                            <span>Fall 1</span>
                            <strong>Viele Mahnungen, Inkasso und Angst vor Pf&auml;ndung</strong>
                        </div>
                        <div class="p4u-referral-options">
                            <button data-correct="true" data-feedback="Richtig: Schuldnerberatung oder Verbraucherzentrale sind hier passende Fachstellen.">Schuldnerberatung / Verbraucherzentrale</button>
                            <button data-correct="false" data-feedback="Nicht zuerst: Psychologische Hilfe kann erg&auml;nzen, aber die Mahnungen brauchen fachliche Schuldenberatung.">Psychologische Beratung</button>
                            <button data-correct="false" data-feedback="Nicht passend: Peers sollten keine rechtliche oder finanzielle Regulierung selbst &uuml;bernehmen.">Peer kl&auml;rt das selbst</button>
                        </div>
                        <p class="p4u-case-feedback" aria-live="polite"></p>
                    </div>
                    <div class="p4u-referral-case">
                        <div class="p4u-referral-scenario">
                            <span>Fall 2</span>
                            <strong>Person schreibt: Ich sehe keinen Ausweg mehr</strong>
                        </div>
                        <div class="p4u-referral-options">
                            <button data-correct="false" data-feedback="Nicht ausreichend: Finanzberatung allein passt nicht, wenn akute Hoffnungslosigkeit im Vordergrund steht.">Nur Schuldnerberatung</button>
                            <button data-correct="true" data-feedback="Richtig: Das ist ein dringendes Warnsignal. Krisendienst, Telefonseelsorge oder bei akuter Gefahr Notruf einbeziehen.">Krisendienst / Telefonseelsorge / Notfallhilfe</button>
                            <button data-correct="false" data-feedback="Nicht passend: Bei solchen Aussagen nicht abwarten oder bagatellisieren.">Abwarten bis morgen</button>
                        </div>
                        <p class="p4u-case-feedback" aria-live="polite"></p>
                    </div>
                    <div class="p4u-referral-case">
                        <div class="p4u-referral-scenario">
                            <span>Fall 3</span>
                            <strong>Gerichtliches Schreiben, Frist und gro&szlig;e Unsicherheit</strong>
                        </div>
                        <div class="p4u-referral-options">
                            <button data-correct="false" data-feedback="Nicht passend: Ignorieren kann Fristen und Folgen verschlimmern.">Erstmal ignorieren</button>
                            <button data-correct="true" data-feedback="Richtig: Verbraucherzentrale, rechtliche Beratung oder spezialisierte Fachberatung helfen bei Einordnung und n&auml;chsten Schritten.">Rechtliche Beratung / Verbraucherzentrale</button>
                            <button data-correct="false" data-feedback="Nicht passend: Peers sollten keine Rechtsberatung leisten.">Peer erkl&auml;rt die Rechtslage verbindlich</button>
                        </div>
                        <p class="p4u-case-feedback" aria-live="polite"></p>
                    </div>
                    <div class="p4u-result" id="p4u-referral-result" aria-live="polite"></div>
                </div>
                <div class="p4u-referral-map" aria-label="Orientierung f&uuml;r Weiterverweisung">
                    <div><span>Schulden</span><strong>Schuldnerberatung</strong></div>
                    <div><span>Krise</span><strong>Krisendienst</strong></div>
                    <div><span>Recht</span><strong>Fachberatung</strong></div>
                </div>
                <div class="p4u-card p4u-recap">
                    <h3>Regionale Hinweise aus der Pr&auml;sentation</h3>
                    <ul>
                        <li>Diakonisches Werk Baden-Baden/Rastatt: Schuldenpr&auml;vention und Schuldnerberatung.</li>
                        <li>Landratsamt Landkreis Rastatt: Schuldnerberatung f&uuml;r berechtigte Einwohner:innen.</li>
                        <li>Stadt Baden-Baden: Schuldnerberatung f&uuml;r berechtigte Einwohner:innen des Stadtkreises.</li>
                    </ul>
                </div>
                <button class="p4u-primary" data-next="emergency">Weiter: Notfall</button>
            </section>

            <section id="p4u-sec-emergency" class="p4u-section">
                <p class="p4u-label">Station 5 &middot; Notfall</p>
                <h2>Wenn es akut wird</h2>
                <p class="p4u-lead">Bei akuter Gefahr geht es nicht mehr nur um Beratung. Dann braucht es schnelle Hilfe.</p>
                <div class="p4u-process" aria-label="Vorgehen im akuten Fall">
                    <div><span>1</span><strong>Ruhig bleiben</strong><p>Die Person ernst nehmen und nicht allein lassen.</p></div>
                    <div><span>2</span><strong>Kl&auml;ren</strong><p>Geht es um akute Gefahr f&uuml;r sich oder andere?</p></div>
                    <div><span>3</span><strong>Hilfe holen</strong><p>Notruf, Krisendienst oder erwachsene Ansprechperson einbeziehen.</p></div>
                    <div><span>4</span><strong>Weitergeben</strong><p>Situation dokumentieren und an Fachstelle verweisen.</p></div>
                    <div><span>5</span><strong>Abgrenzen</strong><p>Eigene Verantwortung und Belastung beachten.</p></div>
                </div>
                <div class="p4u-card p4u-recap">
                    <h3>Wichtige Nummern</h3>
                    <ul>
                        <li>Polizei: 110</li>
                        <li>Feuerwehr/Rettungsdienst: 112</li>
                        <li>&Auml;rztlicher Bereitschaftsdienst: 116 117</li>
                        <li>Telefonseelsorge: 0800 111 0 111 oder 0800 111 0 222</li>
                    </ul>
                </div>
                <button class="p4u-primary" data-next="case">Weiter: Fallbeispiel</button>
            </section>

            <section id="p4u-sec-case" class="p4u-section">
                <p class="p4u-label">Station 6 &middot; Fallbeispiel</p>
                <h2>Was w&auml;re der n&auml;chste gute Schritt?</h2>
                <p class="p4u-lead">Eine ratsuchende Person schreibt: "Ich habe mehrere Mahnungen, schlafe kaum noch und wei&szlig; nicht mehr weiter."</p>
                <div class="p4u-card p4u-casechoice">
                    <h3>Wie reagierst du?</h3>
                    <div class="p4u-practice-options">
                        <button data-correct="false" data-feedback="Nicht passend: Das bagatellisiert die Belastung.">Das wird schon, ignorier die Briefe erstmal.</button>
                        <button data-correct="true" data-feedback="Richtig: Du nimmst die Krise ernst, sortierst kurz und verweist an professionelle Hilfe.">Ich h&ouml;re dir zu. Mahnungen und Schlafprobleme sind ernst. Lass uns schauen, welche Beratung du heute kontaktieren kannst.</button>
                        <button data-correct="false" data-feedback="Nicht passend: Peers verhandeln keine Schuldenregulierung.">Schick mir alle Gl&auml;ubiger, ich verhandele das f&uuml;r dich.</button>
                    </div>
                    <p class="p4u-case-feedback" id="p4u-case-feedback" aria-live="polite"></p>
                </div>
                <button class="p4u-primary" data-next="quiz">Weiter: Abschlussquiz</button>
            </section>

            <section id="p4u-sec-quiz" class="p4u-section">
                <p class="p4u-label">Station 7 &middot; Abschlussquiz</p>
                <h2>Quiz - Modul 5</h2>
                <p class="p4u-lead">Pr&uuml;fe, ob du Warnsignale und passende Weiterverweisung einordnen kannst. Mindestens 60 Prozent sind erforderlich.</p>
                <div class="p4u-attemptbar">
                    <span id="p4u-quiz-attempt">Versuch 1</span>
                    <span id="p4u-quiz-state">Noch nicht abgeschlossen</span>
                </div>
                <div class="p4u-quiz" data-multi="true">
                    <h3>1. Was k&ouml;nnen Anzeichen psychischer Krisen sein?</h3>
                    <p class="p4u-hint">Mehrere Antworten m&ouml;glich.</p>
                    <button data-correct="true">Verzweiflung</button>
                    <button data-correct="true">Schlafprobleme</button>
                    <button data-correct="false">Einmalige gute Laune</button>
                    <button data-correct="true">Aussagen wie: Ich sehe keinen Ausweg</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Das sind wichtige Warnsignale." data-error="Noch nicht ganz: Achte besonders auf Verzweiflung, Schlafprobleme und Hoffnungslosigkeit."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>2. Was ist bei Mahnungen und Inkassoschreiben passend?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Ignorieren, damit weniger Stress entsteht</button>
                    <button data-correct="true">Sensible Ansprache und Weiterverweisung an Schuldnerberatung</button>
                    <button data-correct="false">Selbst rechtliche Beratung geben</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Mahnungen und Inkasso sind Hinweise auf professionelle Hilfe." data-error="Nicht passend: Peers ersetzen keine Schuldnerberatung."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>3. Was geh&ouml;rt zur Peer-Rolle in Krisen?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="true">Zuh&ouml;ren, stabilisieren, Orientierung geben und weiterverweisen</button>
                    <button data-correct="false">Diagnosen stellen</button>
                    <button data-correct="false">Komplexe Probleme allein l&ouml;sen</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Das ist die passende Rollengrenze." data-error="Nicht ganz: Die Peer-Rolle bleibt unterst&uuml;tzend und klar abgegrenzt."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>4. Wo findet man Informationen zu Krisentelefonen?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="true">Online, zum Beispiel &uuml;ber telefonseelsorge.de</button>
                    <button data-correct="false">Nur &uuml;ber private Chatgruppen</button>
                    <button data-correct="false">Gar nicht, Krisentelefone gibt es nicht</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Telefonseelsorge und regionale Krisendienste sind online auffindbar." data-error="Nicht ganz: Krisentelefone sind online auffindbar, etwa &uuml;ber die Telefonseelsorge."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>5. Bietet das Diakonische Werk Baden-Baden/Rastatt Einzelberatung f&uuml;r junge Menschen in finanziellen Schwierigkeiten an?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="true">Ja</button>
                    <button data-correct="false">Nein</button>
                    <button data-correct="false">Nur f&uuml;r Unternehmen</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Die Pr&auml;sentation nennt Einzelberatung f&uuml;r junge Menschen." data-error="Nicht ganz: In den regionalen Angeboten wird Einzelberatung f&uuml;r junge Menschen genannt."></p>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-submit-quiz">Quiz abschlie&szlig;en</button>
                    <button class="p4u-secondary p4u-hidden" id="p4u-retry-quiz">Quiz neu versuchen</button>
                </div>
                <div class="p4u-result" id="p4u-quiz-result" aria-live="polite"></div>
                <div class="p4u-completion p4u-hidden" id="p4u-completion">
                    <p class="p4u-completion-kicker">Abschluss erreicht</p>
                    <h3>Modul 5 abgeschlossen</h3>
                    <p>Du hast das Abschlussquiz bestanden und kannst Krisenwarnsignale besser erkennen, deine Rolle abgrenzen und passend weiterverweisen.</p>
                    <div class="p4u-reflection">
                        <strong>Kurze Reflexion</strong>
                        <p>Welche Warnsignale w&uuml;rdest du in einem Chat besonders ernst nehmen?</p>
                    </div>
                    <div class="p4u-controls">
                        <a class="p4u-primary p4u-linkbutton" href="<?php echo (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false); ?>">Zur&uuml;ck zum Kurs</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
<?php
echo $OUTPUT->footer();
