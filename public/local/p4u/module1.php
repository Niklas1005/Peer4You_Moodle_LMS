<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Interactive Modul 1 learning page.
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

$url = new moodle_url('/local/p4u/module1.php', ['courseid' => $course->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title('P4U Modul 1');
$PAGE->set_heading('Modul 1: Einfuehrung & Rollenverstaendnis');
$PAGE->requires->css(new moodle_url('/local/p4u/styles.css'));
$PAGE->requires->js(new moodle_url('/local/p4u/module1.js'));

echo $OUTPUT->header();
?>
<div class="p4u-shell">
    <header class="p4u-module-hero">
        <p class="p4u-label">Modul 1</p>
        <h1>Einf&uuml;hrung &amp; Rollenverst&auml;ndnis</h1>
        <p>Du verstehst deine Rolle als Peer, erkennst klare Grenzen und wendest das Gelernte in einem Fallbeispiel an.</p>
        <div class="p4u-journey" aria-label="Lernpfad">
            <span>Einstieg</span>
            <span>Grundlagen</span>
            <span>Rolle kl&auml;ren</span>
            <span>Zuordnung</span>
            <span>Praxisimpuls</span>
            <span>Fallbeispiel</span>
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
            <button data-target="flashcards">2. Grundlagen</button>
            <button data-target="roles">3. Rolle kl&auml;ren</button>
            <button data-target="exercise">4. Zuordnung</button>
            <button data-target="video">5. Praxisimpuls</button>
            <button data-target="casestudy">6. Fallbeispiel</button>
            <button data-target="quiz" id="p4u-quiz-nav" class="locked">7. Abschlussquiz</button>
        </nav>

        <main class="p4u-content">
            <section id="p4u-sec-intro" class="p4u-section active">
                <p class="p4u-label">Station 1 &middot; Einstieg</p>
                <h2>Willkommen bei Peers for You</h2>
                <p class="p4u-lead">Du lernst, was Peer-Beratung bedeutet und welche Aufgabe du dabei &uuml;bernimmst.</p>
                <p class="p4u-meta">Dauer: ca. 10 Minuten</p>
                <div class="p4u-card p4u-goals">
                    <h3>Nach diesem Modul kannst du</h3>
                    <ul>
                        <li>erkl&auml;ren, was Peer-Beratung bedeutet,</li>
                        <li>deine Rolle von professioneller Beratung abgrenzen,</li>
                        <li>typische Grenzen der Peer-Rolle sicher erkennen,</li>
                        <li>in einem Fallbeispiel passende n&auml;chste Schritte ausw&auml;hlen.</li>
                    </ul>
                </div>
                <div class="p4u-card">
                    <h3>Das Diakonische Werk Rastatt</h3>
                    <p>Das Diakonische Werk Baden-Baden / Rastatt bietet verschiedene Dienstleistungen an: <strong>Suchtberatung, Allgemeine Sozialberatung, Schuldenpr&auml;vention, Schuldnerberatung</strong> und <strong>Schwangerenberatung</strong>.</p>
                    <p>Du wirst als <strong>Peer-Berater:in</strong> eingesetzt. Das hei&szlig;t, du unterst&uuml;tzt andere junge Menschen auf Augenh&ouml;he, anonym und niedrigschwellig.</p>
                </div>
                <div class="p4u-card">
                    <h3>Das Projekt besteht aus drei Bausteinen</h3>
                    <ul>
                        <li><strong>Workshops:</strong> Grundlagenwissen zu Finanzen und digitalen Kostenfallen.</li>
                        <li><strong>Peer-Beratung:</strong> online und anonym durch geschulte Gleichaltrige.</li>
                        <li><strong>Einzelberatung:</strong> schnelle Hilfe, wenn bereits Schulden entstanden sind.</li>
                    </ul>
                </div>
                <div class="p4u-pillgrid">
                    <span>Auf Augenh&ouml;he</span>
                    <span>Vertraulich</span>
                    <span>Niedrigschwellig</span>
                </div>
                <button class="p4u-primary" data-next="flashcards">Weiter: Grundlagen</button>
            </section>

            <section id="p4u-sec-flashcards" class="p4u-section">
                <p class="p4u-label">Station 2 &middot; Grundlagen</p>
                <h2>Grundlagen verstehen</h2>
                <p class="p4u-lead">Gehe die vier Karten nacheinander durch. Jede Karte zeigt zuerst einen Begriff und danach die kurze Erkl&auml;rung.</p>
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
                                <small>Antwort</small>
                                <p id="p4u-flash-back"></p>
                            </div>
                        </div>
                    </div>
                    <div class="p4u-flash-actions" aria-label="Lernkarten steuern">
                        <button class="p4u-secondary" id="p4u-flash-prev">Zur&uuml;ck</button>
                        <button class="p4u-primary" id="p4u-flash-next">N&auml;chste Karte</button>
                    </div>
                </div>
                <p class="p4u-section-note" id="p4u-flash-note">Wenn du die Grundlagen gesehen hast, geht es als N&auml;chstes um deine konkrete Rolle.</p>
                <button class="p4u-primary" data-next="roles">Weiter: Rolle kl&auml;ren</button>
            </section>

            <section id="p4u-sec-roles" class="p4u-section">
                <p class="p4u-label">Station 3 &middot; Rolle kl&auml;ren</p>
                <h2>Meine Rolle als Peer</h2>
                <p class="p4u-lead">Dein Auftrag ist Begleitung und Orientierung. Du ersetzt keine professionelle Beratung.</p>
                <div class="p4u-role-summary">
                    <div class="p4u-role-card good">
                        <div class="p4u-role-card-head">
                            <span>✓</span>
                            <h3>Peers d&uuml;rfen</h3>
                        </div>
                        <ul>
                            <li>Zuh&ouml;ren</li>
                            <li>Informationen &uuml;ber Finanzthemen geben</li>
                            <li>Motivieren, ein Problem anzugehen</li>
                            <li>An Fachstellen weitervermitteln</li>
                        </ul>
                    </div>
                    <div class="p4u-role-card bad">
                        <div class="p4u-role-card-head">
                            <span>!</span>
                            <h3>Peers d&uuml;rfen nicht</h3>
                        </div>
                        <ul>
                            <li>Rechtliche Beratung geben</li>
                            <li>Finanzprodukte empfehlen</li>
                            <li>Professionelle Beratung ersetzen</li>
                        </ul>
                    </div>
                </div>
                <div class="p4u-rolecheck" aria-labelledby="p4u-rolecheck-title">
                    <div class="p4u-rolecheck-intro">
                        <p class="p4u-label">Kurzer Selbstcheck</p>
                        <h3 id="p4u-rolecheck-title">Was ist deine erste Aufgabe als Peer?</h3>
                        <p>Eine junge Person erz&auml;hlt dir, dass sie Schulden hat und nicht mehr weiterwei&szlig;. W&auml;hle die passendste Reaktion.</p>
                    </div>
                    <div class="p4u-role-options">
                        <button class="p4u-role-option" data-correct="false" data-feedback="Nicht deine Aufgabe: Einen Zahlungsplan zu erstellen w&auml;re fachliche Schuldnerberatung.">
                            Einen konkreten Zahlungsplan erstellen
                        </button>
                        <button class="p4u-role-option" data-correct="true" data-feedback="Richtig: Erst zuh&ouml;ren, ernst nehmen und Orientierung geben. Danach kannst du an eine Fachstelle weitervermitteln.">
                            Zuh&ouml;ren, ernst nehmen und Orientierung geben
                        </button>
                        <button class="p4u-role-option" data-correct="false" data-feedback="Nicht passend: Finanzprodukte zu empfehlen geh&ouml;rt nicht zur Peer-Rolle.">
                            Ein Finanzprodukt empfehlen
                        </button>
                        <button class="p4u-role-option" data-correct="false" data-feedback="Nicht deine Aufgabe: Rechtliche Schritte erkl&auml;rt eine professionelle Stelle.">
                            Rechtliche Schritte erkl&auml;ren
                        </button>
                    </div>
                    <p class="p4u-role-feedback" id="p4u-role-feedback" aria-live="polite"></p>
                </div>
                <button class="p4u-primary" data-next="exercise">Weiter: Zuordnung</button>
            </section>

            <section id="p4u-sec-exercise" class="p4u-section">
                <p class="p4u-label">Station 4 &middot; Zuordnung</p>
                <h2>Handlungen richtig zuordnen</h2>
                <p class="p4u-lead">Ziehe jede Aktivit&auml;t in das richtige Feld und pr&uuml;fe danach deine Zuordnung.</p>
                <div class="p4u-dropgrid">
                    <div>
                        <h3>Peers d&uuml;rfen</h3>
                        <div class="p4u-dropzone" data-accept="d1,d2,d3,d4"></div>
                    </div>
                    <div>
                        <h3>Peers d&uuml;rfen nicht</h3>
                        <div class="p4u-dropzone" data-accept="d5,d6"></div>
                    </div>
                </div>
                <div id="p4u-card-bank" class="p4u-bank">
                    <div class="p4u-draggable" draggable="true" data-id="d1">Zuh&ouml;ren</div>
                    <div class="p4u-draggable" draggable="true" data-id="d5">Rechtliche Beratung geben</div>
                    <div class="p4u-draggable" draggable="true" data-id="d2">&Uuml;ber Finanzthemen informieren</div>
                    <div class="p4u-draggable" draggable="true" data-id="d6">Finanzprodukte empfehlen</div>
                    <div class="p4u-draggable" draggable="true" data-id="d3">Motivieren, ein Problem anzugehen</div>
                    <div class="p4u-draggable" draggable="true" data-id="d4">An Fachstellen weitervermitteln</div>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-check-drag">Zuordnung pr&uuml;fen</button>
                    <button class="p4u-secondary" id="p4u-reset-drag">Zur&uuml;cksetzen</button>
                    <button class="p4u-secondary" id="p4u-drag-next" data-next="video" disabled>Weiter</button>
                </div>
            </section>

            <section id="p4u-sec-video" class="p4u-section">
                <p class="p4u-label">Station 5 &middot; Praxisimpuls</p>
                <h2>Peer-Beratung in der Praxis</h2>
                <p class="p4u-lead">W&auml;hle zuerst selbst eine Reaktion. Danach ordnest du die Situation mit dem 3-Schritte-Schema ein.</p>
                <div class="p4u-card p4u-practice-choice">
                    <p class="p4u-label">Mini-Anfrage</p>
                    <h3>Was w&uuml;rdest du zuerst antworten?</h3>
                    <blockquote>Ich habe mehrere Mahnungen bekommen und traue mich nicht, die Briefe zu &ouml;ffnen. Ich glaube, ich bekomme das nicht mehr hin.</blockquote>
                    <div class="p4u-practice-options">
                        <button data-correct="false" data-feedback="Nicht passend: Du w&uuml;rdest sofort fachlich entscheiden. Als Peer startest du nicht mit Zahlungspriorit&auml;ten.">Ich sage dir, welche Rechnung du zuerst zahlen solltest.</button>
                        <button data-correct="true" data-feedback="Richtig: Du nimmst die Sorge ernst, beginnst mit Sortieren und bleibst in deiner Peer-Rolle.">Danke, dass du das erz&auml;hlst. Wir k&ouml;nnen erst einmal sortieren, was dich gerade am meisten belastet.</button>
                        <button data-correct="false" data-feedback="Nicht hilfreich: Das nimmt die &Uuml;berforderung nicht ernst und kann Scham verst&auml;rken.">Das ist nicht so schlimm, mach die Briefe einfach auf.</button>
                        <button data-correct="false" data-feedback="Nicht passend: Einen Kredit oder ein Finanzprodukt zu empfehlen geh&ouml;rt nicht zur Peer-Rolle.">Vielleicht brauchst du einfach einen Kredit.</button>
                    </div>
                    <p class="p4u-practice-feedback" id="p4u-practice-feedback" aria-live="polite"></p>
                </div>
                <div class="p4u-practice-reveal p4u-hidden" id="p4u-practice-reveal">
                    <div class="p4u-steps" aria-label="Drei Schritte in der Peer-Beratung">
                        <div>
                            <span>1</span>
                            <strong>Zuh&ouml;ren</strong>
                            <p>Die Person ausreden lassen und ihre Situation ernst nehmen.</p>
                        </div>
                        <div>
                            <span>2</span>
                            <strong>Sortieren</strong>
                            <p>Gemeinsam kl&auml;ren, was gerade am meisten belastet.</p>
                        </div>
                        <div>
                            <span>3</span>
                            <strong>Weitervermitteln</strong>
                            <p>Eine passende Fachstelle oder n&auml;chste Anlaufstelle sichtbar machen.</p>
                        </div>
                    </div>
                    <div class="p4u-card p4u-practice">
                        <h3>Merksatz</h3>
                        <p><strong>Eine gute erste Reaktion:</strong> &bdquo;Danke, dass du das erz&auml;hlst. Wir schauen erstmal, was dich gerade am meisten belastet. Wenn du m&ouml;chtest, kann ich dir zeigen, wo du professionelle Unterst&uuml;tzung bekommst.&ldquo;</p>
                    </div>
                </div>
                <button class="p4u-primary" data-next="casestudy">Weiter: Fallbeispiel</button>
            </section>

            <section id="p4u-sec-casestudy" class="p4u-section">
                <p class="p4u-label">Station 6 &middot; Fallbeispiel</p>
                <h2>Fall Marko: richtig reagieren</h2>
                <blockquote>Ich habe mein Studium abgebrochen und jobbe jetzt auf Minijob-Basis. W&auml;hrend des Studiums habe ich mir Geld von verschiedenen Leuten geliehen, insgesamt ca. 4.500 EUR. Ich schaffe es einfach nicht, das zur&uuml;ckzuzahlen, und f&uuml;hle mich total &uuml;berfordert.</blockquote>
                <div class="p4u-card p4u-casechoice">
                    <h3>Was w&auml;re jetzt der beste n&auml;chste Schritt?</h3>
                    <div class="p4u-case-options">
                        <button data-correct="false" data-feedback="Nicht passend: Ein R&uuml;ckzahlungsplan geh&ouml;rt in die professionelle Schuldnerberatung.">Direkt einen R&uuml;ckzahlungsplan mit Marko erstellen</button>
                        <button data-correct="true" data-feedback="Richtig: Erst entlasten, zuh&ouml;ren und dann an eine passende Fachstelle weitervermitteln.">Marko zuh&ouml;ren, entlasten und die Schuldnerberatung vorschlagen</button>
                        <button data-correct="false" data-feedback="Nicht passend: Finanzprodukte zu empfehlen geh&ouml;rt nicht zur Peer-Rolle.">Ein Finanzprodukt empfehlen, damit er schneller Geld bekommt</button>
                    </div>
                    <p class="p4u-case-feedback" id="p4u-case-feedback" aria-live="polite"></p>
                </div>
                <div class="p4u-card p4u-recap">
                    <h3>Merksatz f&uuml;r die Peer-Rolle</h3>
                    <p>Zuh&ouml;ren, ernst nehmen und weitervermitteln. Du bist nicht daf&uuml;r verantwortlich, das Problem alleine zu l&ouml;sen.</p>
                </div>
                <button class="p4u-primary" data-next="quiz">Weiter: Abschlussquiz</button>
            </section>

            <section id="p4u-sec-quiz" class="p4u-section">
                <p class="p4u-label">Station 7 &middot; Abschlussquiz</p>
                <h2>Quiz - Modul 1</h2>
                <p class="p4u-lead">Pr&uuml;fe, ob du die wichtigsten Punkte f&uuml;r die Peer-Rolle sicher anwenden kannst. Mindestens 60 Prozent sind erforderlich.</p>
                <div class="p4u-attemptbar">
                    <span id="p4u-quiz-attempt">Versuch 1</span>
                    <span id="p4u-quiz-state">Noch nicht abgeschlossen</span>
                </div>
                <div class="p4u-quiz" data-multi="true">
                    <h3>1. Welche Dienstleistungen bietet das Diakonische Werk Rastatt an?</h3>
                    <p class="p4u-hint">Mehrere Antworten m&ouml;glich.</p>
                    <button data-correct="true">Suchtberatung</button>
                    <button data-correct="true">Allgemeine Sozialberatung</button>
                    <button data-correct="true">Schuldenpr&auml;vention</button>
                    <button data-correct="false">Rentenberatung</button>
                    <button data-correct="true">Schuldnerberatung</button>
                    <button data-correct="true">Schwangerenberatung</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Das Modul zeigt die relevanten Beratungsangebote der Diakonie und grenzt Rentenberatung bewusst aus." data-error="Schau dir die Einf&uuml;hrung nochmal an: Rentenberatung geh&ouml;rt hier nicht zu den genannten Angeboten."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>2. F&uuml;r welchen Bereich wirst du eingesetzt?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Einzelberatung</button>
                    <button data-correct="false">Workshops</button>
                    <button data-correct="true">Peerberatung</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Die Rolle ist Peerberatung, also Unterst&uuml;tzung auf Augenh&ouml;he." data-error="Nicht ganz: In diesem Modul geht es um Peerberatung, nicht um professionelle Einzelberatung oder Workshop-Leitung."></p>
                </div>
                <div class="p4u-quiz" data-multi="true">
                    <h3>3. Warum ist Beratung durch Gleichaltrige gut?</h3>
                    <p class="p4u-hint">Mehrere Antworten m&ouml;glich.</p>
                    <button data-correct="true">Sie baut Hemmschwellen ab</button>
                    <button data-correct="true">Peers k&ouml;nnen Vertrauenspersonen sein</button>
                    <button data-correct="true">Beratung passiert auf Augenh&ouml;he</button>
                    <button data-correct="false">Hilfe wird sp&auml;ter gesucht</button>
                    <button data-correct="true">Vertrauen gegen&uuml;ber Gleichaltrigen ist oft h&ouml;her</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Peer-Beratung senkt Hemmschwellen und kann Vertrauen aufbauen." data-error="Noch nicht ganz: Peer-Beratung soll Hilfe fr&uuml;her und leichter zug&auml;nglich machen, nicht sp&auml;ter."></p>
                </div>
                <div class="p4u-quiz" data-multi="true">
                    <h3>4. Welche Handlungen geh&ouml;ren zu deiner Peer-Rolle?</h3>
                    <p class="p4u-hint">Mehrere Antworten m&ouml;glich.</p>
                    <button data-correct="true">Zuh&ouml;ren und ernst nehmen</button>
                    <button data-correct="true">An Fachstellen weitervermitteln</button>
                    <button data-correct="false">Rechtliche Beratung geben</button>
                    <button data-correct="false">Finanzprodukte empfehlen</button>
                    <button data-correct="true">Motivieren, Hilfe anzunehmen</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Deine Rolle ist Unterst&uuml;tzung, Orientierung und Weitervermittlung." data-error="Pr&uuml;fe nochmal die Rollengrenzen: Rechtliche Beratung und Produkt-Empfehlungen geh&ouml;ren nicht zur Peer-Rolle."></p>
                </div>
                <div class="p4u-quiz">
                    <h3>5. Marko ist &uuml;berfordert und hat Schulden. Was ist der passendste n&auml;chste Schritt?</h3>
                    <p class="p4u-hint">Eine Antwort ausw&auml;hlen.</p>
                    <button data-correct="false">Ihm einen eigenen R&uuml;ckzahlungsplan erstellen</button>
                    <button data-correct="true">Zuh&ouml;ren, entlasten und an die Schuldnerberatung weitervermitteln</button>
                    <button data-correct="false">Ihm ein Finanzprodukt empfehlen</button>
                    <button data-correct="false">Das Thema vermeiden, damit er sich nicht sch&auml;mt</button>
                    <p class="p4u-question-feedback" data-success="Richtig: Du unterst&uuml;tzt Marko und leitest ihn an professionelle Hilfe weiter." data-error="Nicht passend: Im Fall Marko geht es um Zuh&ouml;ren, Entlastung und Weitervermittlung, nicht um eigene Beratung oder Produkt-Empfehlungen."></p>
                </div>
                <div class="p4u-controls">
                    <button class="p4u-primary" id="p4u-submit-quiz">Quiz abschlie&szlig;en</button>
                    <button class="p4u-secondary p4u-hidden" id="p4u-retry-quiz">Quiz neu versuchen</button>
                </div>
                <div class="p4u-result" id="p4u-quiz-result" aria-live="polite"></div>
                <div class="p4u-completion p4u-hidden" id="p4u-completion">
                    <p class="p4u-completion-kicker">Abschluss erreicht</p>
                    <h3>Modul 1 abgeschlossen</h3>
                    <p>Du hast das Abschlussquiz bestanden und kennst jetzt die Grundidee der Peer-Beratung, die wichtigsten Grenzen deiner Rolle und passende n&auml;chste Schritte in einem Fallbeispiel.</p>
                    <div class="p4u-reflection">
                        <strong>Kurze Reflexion</strong>
                        <p>Was nimmst du dir f&uuml;r deine erste Peer-Beratung mit?</p>
                    </div>
                    <p class="p4u-section-note">Wenn die Aktivit&auml;tsverfolgung im Kurs aktiviert ist, wird der Moodle-Link nach dem &Ouml;ffnen des Lernmoduls als erledigt gewertet.</p>
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
