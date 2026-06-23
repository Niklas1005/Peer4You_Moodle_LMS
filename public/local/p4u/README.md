# local_p4u — Peer4You Lernmodule

Moodle Local Plugin für die **interaktiven Peer-Schulungsmodule** und den Lernfortschritt.

## Module

| Nr. | Dateien | Status |
|-----|---------|--------|
| 1 | `module1.php`, `module1.js` | Interaktiv — Einführung & Rollenverständnis |
| 2 | `module2.php`, `module2.js` | Interaktiv — Finanzwissen |
| 3–4, 6 | `module_placeholder.php?module=N` | Platzhalter |
| 5 | `module5.php`, `module5.js` | Interaktiv — Krisen erkennen |

## API-Endpunkte

| Endpunkt | Methode | Zweck |
|----------|---------|-------|
| `module_state.php?action=get` | GET | Fortschritt + Quiz-Stats + Completion-Status laden |
| `module_state.php?action=save` | POST (JSON) | Fortschritt/Quiz-Stats speichern (User Preferences) |
| `complete_module.php?action=status` | GET | Moodle-Completion-Status abfragen |
| `complete_module.php?action=complete` | POST | Modul als abgeschlossen markieren (nach Quiz ≥ 60 %) |

## JavaScript

| Datei | Zweck |
|-------|-------|
| `module-progress.js` | Gemeinsamer Fortschritts-Helper `P4UProgress.create()` — Server + localStorage |
| `module1.js` / `module2.js` / `module5.js` | Modul-spezifische UI, Quiz, interaktive Aufgaben |
| `dashboard.js` | Hero-Banner auf `/my/` (Dashboard) |

## PHP-Kern

| Datei | Zweck |
|-------|-------|
| `lib.php` | Hooks: Dashboard-/Kurs-CSS einbinden |
| `classes/local/progress_store.php` | User Preferences (autoritative DB-Quelle) |
| `complete_module.php` | Moodle `course_modules_completion` nach Quiz |

## Kursverknüpfung

URL-Aktivitäten im Kurs `P4U-TEST` verweisen auf `/local/p4u/module{N}.php?courseid=…`.  
Kursstruktur wird per CLI angelegt: [local/p4u/cli/README.md](../../../local/p4u/cli/README.md)

## Ausführliche Doku

- [docs/09-interactive-modules-architecture.md](../../../docs/09-interactive-modules-architecture.md)
- [docs/06-module1-implementation-notes.md](../../../docs/06-module1-implementation-notes.md)

## Lizenz

GNU GPL v3 (Moodle-Plugin-Konvention)
