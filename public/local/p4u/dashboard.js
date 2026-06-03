(function() {
    const body = document.body;
    if (!body || body.id !== 'page-my-index') {
        return;
    }

    const main = document.querySelector('#region-main .card-body, #region-main-box, [role="main"]');
    if (!main || document.querySelector('.p4u-dashboard-shell')) {
        return;
    }

    const fallbackCourseId = 2;
    const courseLink = document.querySelector('.block_myoverview a[href*="/course/view.php?id="], a[href*="/course/view.php?id="]');
    const courseIdMatch = courseLink ? courseLink.href.match(/[?&]id=(\d+)/) : null;
    const courseId = courseIdMatch ? courseIdMatch[1] : fallbackCourseId;
    const courseUrl = '/course/view.php?id=' + courseId;
    const moduleUrl = '/local/p4u/module1.php?courseid=' + courseId;

    const courseTitle = (courseLink && courseLink.textContent.trim()) || 'Peers for You - Ausbildung';
    const favouriteKey = 'p4u-dashboard-favourite-' + courseId;
    const isFavourite = localStorage.getItem(favouriteKey) === '1';

    const shell = document.createElement('section');
    shell.className = 'p4u-dashboard-shell';
    shell.innerHTML = [
        '<h1>Dashboard</h1>',
        '<section class="p4u-dashboard-panel p4u-dashboard-timeline">',
        '<div class="p4u-dashboard-panel-head">',
        '<h2>Zeitliste</h2>',
        '</div>',
        '<div class="p4u-dashboard-toolbar">',
        '<button type="button">N&auml;chste 7 Tage</button>',
        '<button type="button">Sortiert nach Datum</button>',
        '<input type="search" aria-label="Aktivit&auml;t suchen" placeholder="Suche nach Aktivit&auml;tstyp oder Aktivit&auml;tsname">',
        '</div>',
        '<div class="p4u-dashboard-timeline-entry">',
        '<div>',
        '<strong>Heute</strong>',
        '<span>Keine f&auml;lligen Aufgaben</span>',
        '</div>',
        '<a href="' + moduleUrl + '">Interaktives Lernmodul fortsetzen</a>',
        '</div>',
        '</section>',
        '<section class="p4u-dashboard-panel p4u-dashboard-courses">',
        '<div class="p4u-dashboard-panel-head">',
        '<h2>Kurs&uuml;bersicht</h2>',
        '</div>',
        '<div class="p4u-dashboard-toolbar">',
        '<button type="button">Favoriten</button>',
        '<input type="search" aria-label="Kurs suchen" placeholder="Suchen">',
        '<button type="button">Sortiert nach Kursname</button>',
        '<button type="button">Liste</button>',
        '</div>',
        '<div class="p4u-dashboard-course-list">',
        '<article class="p4u-dashboard-course-row">',
        '<button class="p4u-dashboard-star' + (isFavourite ? ' is-active' : '') + '" type="button" aria-pressed="' + (isFavourite ? 'true' : 'false') + '" aria-label="Kurs favorisieren">&#9733;</button>',
        '<div class="p4u-dashboard-course-main">',
        '<a href="' + courseUrl + '">' + courseTitle + '</a>',
        '<span>Modul 1 aktiv &middot; weitere Module vorbereitet</span>',
        '</div>',
        '<a class="p4u-dashboard-course-action" href="' + moduleUrl + '">Modul starten</a>',
        '<button class="p4u-dashboard-more" type="button" aria-label="Weitere Aktionen">&#8942;</button>',
        '</article>',
        '</div>',
        '</section>'
    ].join('');

    main.prepend(shell);

    const star = shell.querySelector('.p4u-dashboard-star');
    star.addEventListener('click', function() {
        const active = !star.classList.contains('is-active');
        star.classList.toggle('is-active', active);
        star.setAttribute('aria-pressed', active ? 'true' : 'false');
        localStorage.setItem(favouriteKey, active ? '1' : '0');
    });

    document.querySelectorAll('button, a').forEach(control => {
        const label = (control.textContent || '').trim();
        if (label.includes('Standard wiederherstellen') || label.includes('Block hinzuf') || label.includes('Block hinzufÃ¼gen')) {
            control.classList.add('p4u-dashboard-hidden-edit-control');
        }
    });
})();
