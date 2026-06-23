# theme_p4u — Peer4You Custom-Theme

Moodle-Theme für die Diakonie Peer-Schuldenberater-Ausbildung. Erbt von **Boost** (`$THEME->parents = ['boost']`), keine Core-Änderungen.

## Zweck

- **Login:** Diakonie-Branding, eigenes Layout (`layout/login.php`, `templates/login.mustache`)
- **Dashboard** (`/my/`): Boost-Drawers-Layout, Hero-Bereich via `local_p4u` + `dashboard.css`
- **Kursansicht:** Einheitliche Farben (Primary `#009BDC`), Fokus-Ringe, Kursindex

## Wichtige Dateien

| Datei | Funktion |
|-------|----------|
| `config.php` | Layout-Mapping (frontpage, mydashboard, login, course → drawers/login) |
| `lib.php` | SCSS-Zusammenführung (Boost + `p4u.scss` + `login.scss`) |
| `scss/p4u.scss` | Design-Tokens, Komponenten |
| `scss/login.scss` | Login-spezifische Styles |
| `layout/login.php` | Login-Seitenlayout |
| `templates/` | Mustache-Templates (frontpage, mydashboard, login) |
| `pix/loginlogo.png` | Diakonie-Logo auf der Login-Karte |
| `lang/de/theme_p4u.php` | Deutsche Theme-Strings |

## Aktivierung

```bash
php admin/cli/cfg.php --name=theme --set=p4u
php admin/cli/purge_caches.php
php admin/cli/build_theme_css.php --themes=p4u   # optional, bei SCSS-Änderungen
```

## Container-Pfad

Apache `DocumentRoot` = `/var/www/html/public` → Theme liegt unter `/var/www/html/public/theme/p4u/`.

## Ausführliche Doku

- [docs/05-theme-p4u-runbook.md](../../../docs/05-theme-p4u-runbook.md)
- [docs/03-customization-strategy.md](../../../docs/03-customization-strategy.md)

## Lizenz

GNU GPL v3 (Moodle-Plugin-Konvention)
