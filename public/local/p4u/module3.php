<?php
/** Peer4You Modul 3 — Kommunikation und Gesprächsführung. @package local_p4u */
require(__DIR__ . '/../../config.php');
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$course = get_course($courseid);
$context = $courseid == SITEID ? context_system::instance() : context_course::instance($course->id);
require_login($course);
$PAGE->set_url(new moodle_url('/local/p4u/module3.php', ['courseid' => $course->id]));
$PAGE->set_context($context); $PAGE->set_course($course); $PAGE->set_pagelayout('incourse');
$PAGE->set_title('P4U Modul 3'); $PAGE->set_heading('Modul 3: Kommunikation und Gesprächsführung');
$PAGE->requires->css(new moodle_url('/local/p4u/styles.css'));
$PAGE->requires->js(new moodle_url('/local/p4u/module-progress.js'));
$PAGE->requires->js(new moodle_url('/local/p4u/module34.js'));
echo $OUTPUT->header();
$sections = ['intro', 'online', 'language', 'relationship', 'boundaries', 'firstcontact', 'quiz'];
?>
<div class="p4u-shell" data-module-number="3"
 data-sections='<?php echo json_encode($sections); ?>' data-gated-sections='["relationship","firstcontact"]'
 data-user-id="<?php echo (int)$USER->id; ?>"
 data-progress-url="<?php echo (new moodle_url('/local/p4u/module_state.php', ['courseid'=>$course->id,'module'=>3,'sesskey'=>sesskey()]))->out(false); ?>"
 data-complete-url="<?php echo (new moodle_url('/local/p4u/complete_module.php', ['courseid'=>$course->id,'module'=>3,'action'=>'complete','sesskey'=>sesskey()]))->out(false); ?>">
 <header class="p4u-module-hero"><p class="p4u-label">Modul 3</p><h1>Kommunikation und Gesprächsführung</h1>
  <p>Du führst wertschätzende, strukturierte und zielorientierte Beratungsgespräche im digitalen Raum.</p>
  <div class="p4u-status-card"><div><span>Status</span><strong id="p4u-status-label">Offen</strong></div><div><span>Quizversuche</span><strong id="p4u-status-attempts">0</strong></div><div><span>Bestanden</span><strong id="p4u-status-passed">0</strong></div></div>
  <div class="p4u-journey"><span>Einstieg</span><span>Online</span><span>Sprache</span><span>Beziehung</span><span>Grenzen</span><span>Erstkontakt</span><span>Quiz</span></div>
 </header>
 <div class="p4u-progress"><span id="p4u-progress-label">Station 1 von 7</span><div class="p4u-progressbar"><span id="p4u-progress-fill" style="width:14%"></span></div><strong id="p4u-progress-pct">14 %</strong></div>
 <p id="p4u-live-notice" class="p4u-practice-feedback" aria-live="polite"></p>
 <div class="p4u-layout">
  <nav class="p4u-nav" aria-label="Modulnavigation">
   <button class="active" data-target="intro">1. Einstieg</button><button data-target="online">2. Online-Kommunikation</button><button data-target="language">3. Sprache</button><button data-target="relationship">4. Beziehung gestalten</button><button data-target="boundaries">5. Grenzen und Krisen</button><button data-target="firstcontact">6. Erstkontakt</button><button data-target="quiz">7. Abschlussquiz</button>
  </nav>
  <main class="p4u-content">
   <section id="p4u-sec-intro" class="p4u-section active"><p class="p4u-label">Station 1 · Einstieg</p><h2>Deine Rolle transparent machen</h2>
    <p class="p4u-lead">Peer-Beratung ist Beratung auf Augenhöhe. Sie gibt Erfahrung und Orientierung weiter, ersetzt aber weder Fach- noch Rechtsberatung.</p><p class="p4u-meta">Dauer: ca. 20 Minuten</p>
    <div class="p4u-card p4u-goals"><h3>Nach diesem Modul kannst du</h3><ul><li>Besonderheiten digitaler Kommunikation berücksichtigen,</li><li>klar, wertschätzend und empathisch schreiben,</li><li>eigene Grenzen setzen und bei Krisen weiterverweisen,</li><li>einen ersten Beratungskontakt sinnvoll strukturieren.</li></ul></div>
    <div class="p4u-card p4u-recap"><h3>Formulierungsbeispiel</h3><p>„Ich antworte dir als Peerberater:in und teile Erfahrungen und Orientierung. Ich leiste keine professionelle oder rechtliche Beratung.“</p></div>
    <button class="p4u-primary" data-next="online">Weiter: Online-Kommunikation</button>
   </section>
   <section id="p4u-sec-online" class="p4u-section"><p class="p4u-label">Station 2 · Digitaler Raum</p><h2>Was online anders ist</h2><p class="p4u-lead">Gestik, Mimik und Tonfall fehlen. Deshalb müssen Klarheit, Rückfragen und Verlässlichkeit sichtbar werden.</p>
    <div class="p4u-dropgrid"><div class="p4u-card"><h3>Fehlende Körpersprache</h3><p>Gefühle benennen und gezielt nachfragen.</p></div><div class="p4u-card"><h3>Zeitversatz</h3><p>Transparent sagen, wann eine Antwort zu erwarten ist.</p></div><div class="p4u-card"><h3>Anonymität</h3><p>Sie senkt Hemmschwellen, verlangt aber bewussten Vertrauensaufbau.</p></div></div>
    <div class="p4u-card"><h3>Missverständnisse vermeiden</h3><ul><li>Kurze, eindeutige Sätze statt Fachsprache.</li><li>Ironie und Mehrdeutigkeit vermeiden.</li><li>Zusammenfassen: „Habe ich dich richtig verstanden, dass …?“</li><li>Bewusst freundlich formulieren.</li></ul></div>
    <button class="p4u-primary" data-next="language">Weiter: Sprache</button>
   </section>
   <section id="p4u-sec-language" class="p4u-section"><p class="p4u-label">Station 3 · Schreibstil</p><h2>Sprache, die Druck herausnimmt</h2>
    <div class="p4u-card"><h3>Hilfreich formulieren</h3><p><strong>Statt:</strong> „Du solltest dringend …“<br><strong>Besser:</strong> „Ein möglicher nächster Schritt könnte sein …“</p><p><strong>Statt:</strong> „Das ist wichtig!“<br><strong>Besser:</strong> „Das kann hilfreich sein, wenn du so weit bist.“</p></div>
    <div class="p4u-card"><h3>Ressourcen sichtbar machen</h3><p>„Du bist den ersten Schritt gegangen, hast das Problem erkannt und Kontakt aufgenommen. Das ist ein wichtiger Schritt.“</p><p>Eigene Erfahrungen nur sparsam und hilfreich einsetzen – nie als Maßstab.</p></div>
    <button class="p4u-primary" data-next="relationship">Weiter: Beziehung gestalten</button>
   </section>
   <section id="p4u-sec-relationship" class="p4u-section"><p class="p4u-label">Station 4 · Praxis</p><h2>Empathisch reagieren – trotz Distanz</h2>
    <div class="p4u-card"><h3>Samira schreibt: „Ich habe seit Tagen Angst, meine Briefe zu öffnen. Ich schäme mich total.“ Was ist die beste erste Reaktion?</h3>
     <div class="p4u-practice-options" data-gate="relationship"><button data-correct="false" data-feedback="Zu direkt: Anweisungen können den Druck erhöhen.">Du musst die Briefe sofort öffnen und alles auflisten.</button><button data-correct="true" data-feedback="Richtig: Du spiegelst das Gefühl, würdigst den Kontakt und bietest einen kleinen Schritt an.">Das klingt sehr belastend. Gut, dass du dich meldest. Wenn du möchtest, schauen wir gemeinsam nach einem kleinen nächsten Schritt.</button><button data-correct="false" data-feedback="Nicht hilfreich: Die Scham wird relativiert statt ernst genommen.">Das ist doch kein Grund, sich zu schämen – anderen geht es schlimmer.</button></div><p class="p4u-practice-feedback" aria-live="polite"></p>
    </div><button class="p4u-primary" data-next="boundaries" data-next-from="relationship" disabled>Weiter: Grenzen</button>
   </section>
   <section id="p4u-sec-boundaries" class="p4u-section"><p class="p4u-label">Station 5 · Selbstschutz</p><h2>Grenzen wahrnehmen und setzen</h2>
    <div class="p4u-card"><ul><li>Keine dauerhafte Erreichbarkeit versprechen und keine Mails spätabends beantworten.</li><li>Bei Überforderung oder Unsicherheit Jürgen oder Ulrike einbeziehen.</li><li>Keine Verträge bewerten, Rechtsauskünfte geben oder Finanzprodukte empfehlen.</li><li>Bei Hoffnungslosigkeit oder Suizidgedanken ruhig bleiben, nach akuter Gefahr fragen und professionelle Krisenhilfe einbinden.</li></ul></div>
    <div class="p4u-card p4u-recap"><h3>Merksatz</h3><p>Du gibst Orientierung und Hoffnung – nicht Lösungen und Anweisungen. Peer-Beratung lebt von Echtheit, Klarheit und guten Grenzen.</p></div>
    <button class="p4u-primary" data-next="firstcontact">Weiter: Erstkontakt</button>
   </section>
   <section id="p4u-sec-firstcontact" class="p4u-section"><p class="p4u-label">Station 6 · Leitfaden</p><h2>Checkliste für den ersten Kontakt</h2><p class="p4u-lead">Markiere alle Schritte, die du vor dem Absenden geprüft hast.</p>
    <div class="p4u-card p4u-checklist" data-gate="firstcontact"><label><input type="checkbox"> Rolle und eigene Belastbarkeit geprüft</label><label><input type="checkbox"> Wertschätzend begrüßt, Situation anerkannt und Rolle erklärt</label><label><input type="checkbox"> Gefühle gespiegelt und nicht bewertet</label><label><input type="checkbox"> Nur notwendige Informationen erfragt</label><label><input type="checkbox"> Stärken und bereits gegangene Schritte benannt</label><label><input type="checkbox"> Optionen angeboten und Fachstellen nicht ersetzt</label><label><input type="checkbox"> Krisenhinweise geprüft</label><label><input type="checkbox"> Kernaussage zusammengefasst und ohne Druck verabschiedet</label></div>
    <button class="p4u-primary" data-next="quiz" data-next-from="firstcontact" disabled>Weiter: Abschlussquiz</button>
   </section>
   <section id="p4u-sec-quiz" class="p4u-section"><p class="p4u-label">Station 7 · Abschlussquiz</p><h2>Quiz – Modul 3</h2><p class="p4u-lead">Mindestens 60 Prozent sind erforderlich.</p><div class="p4u-attemptbar"><span id="p4u-quiz-attempt">Versuch 1</span></div>
    <div class="p4u-quiz"><h3>1. Was ist deine Rolle?</h3><button data-correct="false">Rechtsberatung leisten</button><button data-correct="true">Auf Augenhöhe Orientierung geben</button><button data-correct="false">Verträge verbindlich bewerten</button></div>
    <div class="p4u-quiz"><h3>2. Warum sind Rückfragen online besonders wichtig?</h3><button data-correct="true">Weil Körpersprache und Tonfall fehlen</button><button data-correct="false">Weil jede Nachricht sofort beantwortet werden muss</button><button data-correct="false">Weil Pseudonyme verboten sind</button></div>
    <div class="p4u-quiz"><h3>3. Welche Formulierung nimmt Druck heraus?</h3><button data-correct="false">Du musst dringend handeln.</button><button data-correct="true">Ein möglicher nächster Schritt könnte sein …</button><button data-correct="false">Wer nichts tut, ist selbst schuld.</button></div>
    <div class="p4u-quiz"><h3>4. Was tust du bei deutlichen Krisensignalen?</h3><button data-correct="false">Allein Verantwortung übernehmen</button><button data-correct="true">Ruhig reagieren und professionelle Hilfe einbinden</button><button data-correct="false">Das Thema vermeiden</button></div>
    <div class="p4u-quiz"><h3>5. Wie setzt du eigene Erfahrungen ein?</h3><button data-correct="true">Sparsam, reflektiert und hilfreich</button><button data-correct="false">Als allgemeingültige Lösung</button><button data-correct="false">Gar nicht, auch wenn sie entlasten könnten</button></div>
    <div class="p4u-controls"><button id="p4u-submit-quiz" class="p4u-primary">Quiz auswerten</button><button id="p4u-retry-quiz" class="p4u-secondary p4u-hidden">Erneut versuchen</button></div><p id="p4u-quiz-result" aria-live="polite"></p>
   </section>
  </main>
 </div>
</div>
<?php echo $OUTPUT->footer(); ?>
