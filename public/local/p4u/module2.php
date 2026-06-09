<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Interactive Modul 2 learning page.
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

$url = new moodle_url('/local/p4u/module2.php', ['courseid' => $course->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('P4U Modul 2');
$PAGE->set_heading('Modul 2: Finanzwissen');
$PAGE->requires->css(new moodle_url('/local/p4u/styles.css'));
$PAGE->requires->js(new moodle_url('/local/p4u/module2.js'));

echo $OUTPUT->header();
?>
<div class="p4u-shell"
     data-complete-url="<?php echo (new moodle_url('/local/p4u/complete_module.php', ['courseid' => $course->id, 'module' => 2, 'action' => 'complete', 'sesskey' => sesskey()]))->out(false); ?>"
     data-status-url="<?php echo (new moodle_url('/local/p4u/complete_module.php', ['courseid' => $course->id, 'module' => 2, 'action' => 'status']))->out(false); ?>">
    <header class="p4u-module-hero">
        <p class="p4u-label">Modul 2</p>
        <h1>Finanzwissen kompakt</h1>
        <p>Du verstehst wichtige Finanzentscheidungen im Alltag, erkennst Kreditrisiken und kannst einfache Budgetfragen einordnen.</p>
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
            <p id="p4u-status-note">Schlie&szlig;e das Quiz ab, um Modul 2 im Kurs als erledigt zu markieren.</p>
        </div>
        <div class="p4u-journey" aria-label="Lernpfad">
            <span>Einstieg</span>
            <span>Alltag</span>
            <span>Zinsen</span>
            <span>Kredite</span>
            <span>Budgetplan</span>
            <span>Beratung</span>
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
            <button data-target="everyday">2. Alltag</button>
            <button data-target="interest">3. Zinsen</button>
            <button data-target="credit">4. Kredite</button>
            <button data-target="budget">5. Budgetplan</button>
            <button data-target="counseling">6. Beratung</button>
            <button data-target="quiz" id="p4u-quiz-nav" class="locked">7. Abschlussquiz</button>
        </nav>

        <main class="p4u-content">
            <section id="p4u-sec-intro" class="p4u-section active">
                <p class="p4u-label">Station 1 &middot; Einstieg</p>
                <h2>Geld betrifft uns jeden Tag</h2>
                <p class="p4u-lead">Finanzwissen hilft, im Alltag gute Entscheidungen zu treffen und Schuldenrisiken fr&uuml;h zu erkennen.</p>
                <p class="p4u-meta">Dauer: ca. 15 Minuten</p>
                <div class="p4u-card p4u-goals">
                    <h3>Nach diesem Modul kannst du</h3>
                    <ul>
                        <li>kleine Finanzentscheidungen und ihre langfristige Wirkung erkl&auml;ren,</li>
                        <li>Haben- und Soll-Seite unterscheiden,</li>
                        <li>Zinsen, Dispo und Ratenkauf grob einordnen,</li>
                        <li>Budgetbestandteile sortieren und bei Schulden an Beratung weiterverweisen.</li>
                    </ul>
                </div>
                <div class="p4u-card">
                    <h3>Warum das f&uuml;r Peer-Beratung wichtig ist</h3>
                    <p>Junge Menschen treffen fr&uuml;h finanzielle Entscheidungen: Handyvertrag, Streaming, Gaming, Kleidung, Technik, Reisen oder F&uuml;hrerschein. Viele Betr&auml;ge wirken klein, k&ouml;nnen sich aber durch Wiederholung summieren.</p>
                    <p><strong>Merksatz:</strong> Kleine Gewohnheiten haben gro&szlig;e Wirkung.</p>
                </div>
                <button class="p4u-primary" data-next="everyday">Weiter: Alltag</button>
            </section>

            <section id="p4u-sec-everyday" class="p4u-section">
                <p class="p4u-label">Station 2 &middot; Alltag</p>
                <h2>Kleine Entscheidungen, gro&szlig;e Wirkung</h2>
                <p class="p4u-lead">Rechne aus, was aus einem w&ouml;chentlichen Spontankauf &uuml;ber einen l&auml;ngeren Zeitraum wird.</p>
                <div class="p4u-card p4u-calculator">
                    <div class="p4u-tool-head">
                        <p class="p4u-label">Rechentool</p>
                        <h3>Kleine Betr&auml;ge sichtbar machen</h3>
                    </div>
                    <label for="p4u-weekly-cost"><strong>Spontankauf pro Woche</strong></label>
                    <div class="p4u-calculator-row">
                        <input id="p4u-weekly-cost" type="number" min="0" step="1" value="10">
                        <span>EUR pro Woche</span>
                    </div>
                    <div class="p4u-calc-results" aria-live="polite">
                        <div><span>Im Monat</span><strong id="p4u-monthly-cost">40 EUR</strong></div>
                        <div><span>Im Jahr</span><strong id="p4u-yearly-cost">480 EUR</strong></div>
                    </div>
                </div>
                <div class="p4u-card p4u-recap">
                    <h3>Haben und Soll</h3>
                    <p><strong>Haben-Seite:</strong> Sparen und finanzielle Spielr&auml;ume geben R&uuml;ckenwind.</p>
                    <p><strong>Soll-Seite:</strong> Kredite und Verpflichtungen erzeugen Gegenwind und k&ouml;nnen sp&auml;tere Entscheidungen einschr&auml;nken.</p>
                </div>
                <details class="p4u-deep-dive">
                    <summary>Mehr wissen: 50-30-20-Regel</summary>
                    <span class="p4u-deep-dive-label">Vertiefung</span>
                    <p>Eine einfache Orientierung: 50 Prozent f&uuml;r Grundausgaben und Fixkosten, 30 Prozent f&uuml;r pers&ouml;nliche Bed&uuml;rfnisse und 20 Prozent f&uuml;r Sparen, Schuldentilgung oder Verm&ouml;gensaufbau. Die Regel ist kein Muss, hilft aber beim ersten Sortieren.</p>
                </details>
                <button class="p4u-primary" data-next="interest">Weiter: Zinsen</button>
            </section>

            <section id="p4u-sec-interest" class="p4u-section">
                <p class="p4u-label">Station 3 &middot; Zinsen</p>
                <h2>Zinsen verstehen</h2>
                <p class="p4u-lead">Gehe die Karten durch. Sie zeigen, wann Zinsen helfen und wann sie belasten.</p>
                <div class="p4u-flashwrap">
                    <div class="p4u-flash-topbar">
                        <span id="p4u-flash-count">Karte 1 von 4</span>
                        <span>Zum Umdrehen auf die Karte klicken</span>
                    </div>
                    <div class="p4u-flashcard" id="p4u-flashcard" role="button" tabindex="0">
                        <div class="p4u-flashcard-inner">
                            <div class="p4u-flashcard-front">
                                <small>Begriff</small>
                                <strong id="p4u-flash-front"></strong>
                                <span>Klicken zum Umdrehen</span>
                            </div>
                            <div class="p4u-flashcard-back">
                                <small>Erkl&auml;rung</small>
                                <p id="p4u-flash-back"></p>
                            </div>
                        </div>
                    </div>
                    <div class="p4u-flash-actions" aria-label="Lernkarten steuern">
                        <button class="p4u-secondary" id="p4u-flash-prev">Zur&uuml;ck</button>
                        <button class="p4u-primary" id="p4u-flash-next">N&auml;chste Karte</button>
                    </div>
                </div>
                <button class="p4u-primary" data-next="credit">Weiter: Kredite</button>
            </section>

            <section id="p4u-sec-credit" class="p4u-section">
                <p class="p4u-label">Station 4 &middot; Kredite</p>
                <h2>Kreditangebote kritisch pr&uuml;fen</h2>
                <p class="p4u-lead">Kredite, Ratenkauf und Buy Now Pay Later wirken oft harmlos. Entscheidend ist der Gesamtpreis.</p>
                <div class="p4u-card p4u-practice-choice">
                    <p class="p4u-label">Mini-Situation</p>
                    <h3>Eine Person m&ouml;chte Sneaker f&uuml;r 150 EUR kaufen und erst in 30 Tagen zahlen. Was ist der beste Peer-Impuls?</h3>
                    <div class="p4u-practice-options">
                        <button data-correct="false" data-feedback="Nicht passend: Du verharmlost m&ouml;gliche Mahnkosten und mehrere parallele Verpflichtungen.">Das ist praktisch, mach das einfach.</button>
                        <button data-correct="true" data-feedback="Richtig: Du hilfst, Gesamtpreis, F&auml;lligkeit und Alternativen zu pr&uuml;fen, ohne zu bevormunden.">Frag dich: Brauche ich das jetzt, was kostet es insgesamt und k&ouml;nnte ich warten?</button>
                        <button data-correct="false" data-feedback="Nicht deine Rolle: Du sollst keine Finanzprodukte empfehlen.">Nimm lieber eine Kreditkarte mit Teilzahlung.</button>
                        <button data-correct="false" data-feedback="Zu hart: Peer-Beratung soll Orientierung geben, nicht besch&auml;men.">Wer sp&auml;ter zahlt, macht immer etwas falsch.</button>
                    </div>
                    <p class="p4u-practice-feedback" id="p4u-practice-feedback" aria-live="polite"></p>
                </div>
                <div class="p4u-practice-reveal p4u-hidden" id="p4u-practice-reveal">
                    <div class="p4u-card p4u-recap">
                        <h3>Merksatz</h3>
                        <p>Sp&auml;ter zahlen f&uuml;hlt sich leicht an, bis mehrere sp&auml;ter zusammenkommen. 0 Prozent Zinsen hei&szlig;t nicht automatisch 0 Prozent Mehrkosten.</p>
                    </div>
                </div>
                <details class="p4u-deep-dive">
                    <summary>Mehr wissen: Kredit-Selbstcheck</summary>
                    <span class="p4u-deep-dive-label">Vertiefung</span>
                    <ul>
                        <li>Brauche ich das wirklich jetzt?</li>
                        <li>K&ouml;nnte ich warten und sparen?</li>
                        <li>Was kostet mich das insgesamt?</li>
                        <li>Habe ich bereits andere laufende Verpflichtungen?</li>
                    </ul>
                </details>
                <button class="p4u-primary" data-next="budget">Weiter: Budgetplan</button>
            </section>

            <section id="p4u-sec-budget" class="p4u-section">
                <p class="p4u-label">Station 5 &middot; Budgetplan</p>
                <h2>Einnahmen und Ausgaben sortieren</h2>
                <p class="p4u-lead">Ordne die Beispiele den richtigen Budgetbereichen zu.</p>
                <div class="p4u-dropgrid">
                    <div>
                        <h3>Feste Einnahmen</h3>
                        <div class="p4u-dropzone" data-accept="b1,b2"></div>
                    </div>
                    <div>
                        <h3>Feste Ausgaben</h3>
                        <div class="p4u-dropzone" data-accept="b3,b4,b5"></div>
                    </div>
                    <div>
                        <h3>Ver&auml;nderliche Ausgaben</h3>
                        <div class="p4u-dropzone" data-accept="b6,b7,b8"></div>
                    </div>
                </div>
                <div id="p4u-card-bank" class="p4u-bank">
                    <div class="p4u-draggable" draggable="true" data-id="b1">Monatliches Taschengeld</div>
                    <div class="p4u-draggable" draggable="true" data-id="b6">In-App-K&auml;ufe</div>
                    <div class="p4u-draggable" draggable="true" data-id="b3">Handyvertrags-Grundgeb&uuml;hr</div>
                    <div class="p4u-draggable" draggable="true" data-id="b7">Kino oder Freizeit</div>
                    <div class="p4u-draggable" draggable="true" data-id="b4">Miete</div>
                    <div class="p4u-draggable" draggable="true" data-id="b2">Regelm&auml;&szlig;iger Nebenjob</div>
                    <div class="p4u-draggable" draggable="true" data-id="b5">Monatskarte f&uuml;r Bus oder Bahn</div>
                    <div class="p4u-draggable" draggable="true" data-id="b8">Kleidung oder Kosmetik</div>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-check-drag">Budget pr&uuml;fen</button>
                    <button class="p4u-secondary" id="p4u-reset-drag">Zur&uuml;cksetzen</button>
                    <button class="p4u-secondary" id="p4u-drag-next" data-next="counseling" disabled>Weiter</button>
                </div>
            </section>

            <section id="p4u-sec-counseling" class="p4u-section">
                <p class="p4u-label">Station 6 &middot; Schuldnerberatung</p>
                <h2>Wann professionelle Hilfe wichtig ist</h2>
                <p class="p4u-lead">Peers erstellen keine Schuldenregulierung. Sie k&ouml;nnen entlasten, sortieren und an Schuldnerberatung weitervermitteln.</p>
                <div class="p4u-card p4u-casechoice">
                    <h3>Was geh&ouml;rt typischerweise zur Schuldnerberatung?</h3>
                    <div class="p4u-case-options">
                        <button data-correct="false" data-feedback="Nicht passend: Peers oder Ratsuchende handeln das nicht allein aus.">Alle Gl&auml;ubiger selbst ignorieren, bis Ruhe ist</button>
                        <button data-correct="true" data-feedback="Richtig: Bestandsaufnahme, Haushaltsplanung und Schuldenregulierung sind typische Schritte professioneller Beratung.">Bestandsaufnahme, Haushaltsplan und m&ouml;gliche Regulierung kl&auml;ren</button>
                        <button data-correct="false" data-feedback="Nicht passend: Ein neuer Kredit kann die Situation verschlimmern.">Einen neuen Kredit aufnehmen, um alte Forderungen zu bezahlen</button>
                    </div>
                    <p class="p4u-case-feedback" id="p4u-case-feedback" aria-live="polite"></p>
                </div>
                <div class="p4u-card p4u-recap">
                    <h3>Handlungsleitfaden</h3>
                    <ul>
                        <li>Erst sparen, dann kaufen.</li>
                        <li>Fixkosten niedrig halten.</li>
                        <li>Vor Finanzierungen den Gesamtpreis berechnen.</li>
                        <li>Bei &Uuml;berforderung professionelle Hilfe sichtbar machen.</li>
                    </ul>
                </div>
                <details class="p4u-deep-dive">
                    <summary>Mehr wissen: Ablauf einer Schuldnerberatung</summary>
                    <span class="p4u-deep-dive-label">Vertiefung</span>
                    <div class="p4u-process" aria-label="Ablauf einer Schuldnerberatung">
                        <div><span>1</span><strong>Termin</strong><p>Kontakt aufnehmen und Unterlagen vorbereiten.</p></div>
                        <div><span>2</span><strong>Erstgespr&auml;ch</strong><p>Situation und Verschuldung gemeinsam erfassen.</p></div>
                        <div><span>3</span><strong>Haushaltsplan</strong><p>Einnahmen, Ausgaben und Spielr&auml;ume pr&uuml;fen.</p></div>
                        <div><span>4</span><strong>Regulierung</strong><p>M&ouml;gliche Vereinbarungen mit Gl&auml;ubigern kl&auml;ren.</p></div>
                        <div><span>5</span><strong>Weiterer Weg</strong><p>Bei Bedarf Insolvenzberatung einbinden.</p></div>
                    </div>
                </details>
                <button class="p4u-primary" data-next="quiz">Weiter: Abschlussquiz</button>
            </section>

            <section id="p4u-sec-quiz" class="p4u-section">
                <p class="p4u-label">Station 7 &middot; Abschlussquiz</p>
                <h2>Quiz - Modul 2</h2>
                <p class="p4u-lead">Pr&uuml;fe, ob du zentrale Finanzbegriffe und Risiken einordnen kannst. Mindestens 60 Prozent sind erforderlich.</p>
                <div class="p4u-attemptbar">
                    <span id="p4u-quiz-attempt">Versuch 1</span>
                    <span id="p4u-quiz-state">Noch nicht abgeschlossen</span>
                </div>
                <div class="p4u-quiz">
                    <h3>1. Wenn du ein Konto er&ouml;ffnest, kannst du dich oft f&uuml;r welchen Kreditrahmen entscheiden?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="true">Dispositionskredit</button>
                    <button data-correct="false">Sparbrief</button>
                    <button data-correct="false">Budgetplan</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Der Dispo ist ein Kreditrahmen auf dem Girokonto." data-error="Nicht ganz: Gemeint ist der Dispositionskredit, kurz Dispo."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>2. Sind Zinsen bei einer Konto&uuml;berziehung meistens niedrig oder hoch?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Niedrig</button>
                    <button data-correct="true">Hoch</button>
                    <button data-correct="false">Immer null</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Dispo- und &Uuml;berziehungszinsen sind oft hoch." data-error="Pr&uuml;fe nochmal die Zinsarten: Bei &Uuml;berziehung fallen meist hohe Zinsen an."></p>
                </div>
                <div class="p4u-quiz" data-multi="true">
                    <h3>3. Was sind Beispiele f&uuml;r feste Ausgaben?</h3>
                    <p class="p4u-hint">Mehrere Antworten m&ouml;glich.</p>
                    <button data-correct="true">Grundgeb&uuml;hr des Handyvertrags</button>
                    <button data-correct="true">Miete</button>
                    <button data-correct="true">Monatskarte f&uuml;r Bus oder Bahn</button>
                    <button data-correct="false">Spontaner Kinobesuch</button>
                    <button data-correct="false">Einmaliger In-App-Kauf</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Feste Ausgaben fallen regelm&auml;&szlig;ig an." data-error="Noch nicht ganz: Feste Ausgaben sind regelm&auml;&szlig;ige Verpflichtungen."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>4. Was ist ein guter Kurzcheck vor einer gr&ouml;&szlig;eren Ausgabe?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="true">Brauche ich das jetzt, was kostet es insgesamt und k&ouml;nnte ich warten?</button>
                    <button data-correct="false">Wenn die Rate klein ist, muss ich nicht weiter rechnen.</button>
                    <button data-correct="false">Buy Now Pay Later ist immer kostenlos.</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Gesamtpreis, Bedarf und Alternativen geh&ouml;ren zum Kurzcheck." data-error="Nicht ganz: Entscheidend ist der Gesamtpreis und ob die Ausgabe wirklich jetzt n&ouml;tig ist."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>5. Was bedeutet 0-%-Finanzierung im Alltag?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Sie ist immer kostenlos und hat nie Folgekosten.</button>
                    <button data-correct="true">Sie kann trotzdem zu h&ouml;herem Konsum, Zusatzvertr&auml;gen oder einem h&ouml;heren Gesamtpreis f&uuml;hren.</button>
                    <button data-correct="false">Sie ist dasselbe wie Sparen.</button>
                    <p class="p4u-question-feedback" data-success="Richtig: 0 Prozent Zinsen hei&szlig;t nicht automatisch 0 Prozent Mehrkosten." data-error="Nicht ganz: Auch bei 0-%-Finanzierung lohnt sich der Blick auf Gesamtpreis, Zusatzvertr&auml;ge und Konsumwirkung."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>6. Was ist bei &Uuml;berforderung mit Schulden der passende Peer-Schritt?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Selbst eine Schuldenregulierung verhandeln</button>
                    <button data-correct="true">Zuh&ouml;ren, sortieren und an Schuldnerberatung weitervermitteln</button>
                    <button data-correct="false">Ein Finanzprodukt empfehlen</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Professionelle Schuldnerberatung ist dann der passende n&auml;chste Schritt." data-error="Nicht passend: Peers ersetzen keine professionelle Schuldnerberatung."></p>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-submit-quiz">Quiz abschlie&szlig;en</button>
                    <button class="p4u-secondary p4u-hidden" id="p4u-retry-quiz">Quiz neu versuchen</button>
                </div>
                <div class="p4u-result" id="p4u-quiz-result" aria-live="polite"></div>
                <div class="p4u-completion p4u-hidden" id="p4u-completion">
                    <p class="p4u-completion-kicker">Abschluss erreicht</p>
                    <h3>Modul 2 abgeschlossen</h3>
                    <p>Du hast das Abschlussquiz bestanden und kannst wichtige Finanzentscheidungen, Kreditrisiken und Budgetgrundlagen besser einordnen.</p>
                    <div class="p4u-reflection">
                        <strong>Kurze Reflexion</strong>
                        <p>Welche Finanzentscheidung w&uuml;rdest du k&uuml;nftig bewusster pr&uuml;fen?</p>
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
