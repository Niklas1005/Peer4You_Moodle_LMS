(function() {
    const body = document.body;
    if (!body || body.id !== 'page-my-index') {
        return;
    }

    const main = document.querySelector('#region-main .card-body, #region-main-box, [role="main"]');
    if (!main || document.querySelector('.p4u-dashboard-hero')) {
        return;
    }

    const hero = document.createElement('section');
    hero.className = 'p4u-dashboard-hero';
    hero.innerHTML = [
        '<p class="p4u-dashboard-label">Peers for You</p>',
        '<h1>Deine Ausbildung zur Peer-Berater:in</h1>',
        '<p>Starte direkt mit Modul 1 in Moodle. Weitere Module k&ouml;nnen sp&auml;ter im gleichen Lernpfad erg&auml;nzt werden.</p>',
        '<div class="p4u-dashboard-actions">',
        '<a class="p4u-dashboard-primary" href="/local/p4u/module1.php?courseid=2">Modul 1 &ouml;ffnen</a>',
        '<a class="p4u-dashboard-secondary" href="/course/view.php?id=2">Zum Kurs</a>',
        '</div>'
    ].join('');

    const stats = document.createElement('section');
    stats.className = 'p4u-dashboard-grid';
    stats.innerHTML = [
        '<div class="p4u-dashboard-stat"><strong>1</strong><span>Modul angebunden</span></div>',
        '<div class="p4u-dashboard-stat"><strong>7</strong><span>Lernstationen in Modul 1</span></div>',
        '<div class="p4u-dashboard-stat"><strong>60 %</strong><span>Bestehensgrenze im Quiz</span></div>'
    ].join('');

    const plan = document.createElement('section');
    plan.className = 'p4u-dashboard-plan';
    plan.innerHTML = [
        '<h2>Ausbildungsplan</h2>',
        '<div class="p4u-dashboard-modules">',
        '<div class="p4u-dashboard-module is-active"><small>aktiv</small><strong>1. Einf&uuml;hrung &amp; Rollenverst&auml;ndnis</strong><span>Rolle, Grenzen, Fall und Quiz.</span></div>',
        '<div class="p4u-dashboard-module"><small>geplant</small><strong>2. Finanzwissen</strong><span>Grundlagen zu Geld, Konsum und Schuldenrisiken.</span></div>',
        '<div class="p4u-dashboard-module"><small>geplant</small><strong>3. Kommunikation &amp; Gespr&auml;chsf&uuml;hrung</strong><span>Aktives Zuh&ouml;ren und Gespr&auml;che strukturieren.</span></div>',
        '<div class="p4u-dashboard-module"><small>geplant</small><strong>4. Digitale Sicherheit &amp; Datenschutz</strong><span>Sicher beraten und vertraulich handeln.</span></div>',
        '<div class="p4u-dashboard-module"><small>geplant</small><strong>5. Krisen erkennen &amp; weiterverweisen</strong><span>Grenzen erkennen und passende Hilfe einbinden.</span></div>',
        '<div class="p4u-dashboard-module"><small>geplant</small><strong>6. Abschluss &amp; Zertifikat</strong><span>Wissen b&uuml;ndeln und Ausbildung abschlie&szlig;en.</span></div>',
        '</div>'
    ].join('');

    main.prepend(plan);
    main.prepend(stats);
    main.prepend(hero);

    document.querySelectorAll('button, a').forEach(control => {
        const label = (control.textContent || '').trim();
        if (label.includes('Standard wiederherstellen') || label.includes('Block hinzufügen')) {
            control.classList.add('p4u-dashboard-hidden-edit-control');
        }
    });
})();
