# CLI — Kursstruktur Peer4You

Kommandozeilen-Skripte zur Anlage und Aktualisierung der **6 Modul-Abschnitte** im Moodle-Kurs.

## Hauptskript

```bash
php local/p4u/cli/create_training_modules.php
```

### Optionen

| Option | Standard | Beschreibung |
|--------|----------|--------------|
| `--courseid=ID` | — | Bestehenden Kurs verwenden |
| `--shortname=NAME` | `P4U-TEST` | Kurzname; Kurs wird erstellt falls nicht vorhanden |
| `--help` | — | Hilfe anzeigen |

### Was das Skript tut

1. Kurs `P4U-TEST` anlegen oder wiederverwenden (6 Abschnitte, Completion aktiviert)
2. Pro Abschnitt: URL-Aktivität → `module1.php` … `module5.php` oder Platzhalter
3. Admin-User einschreiben (Dashboard-Sichtbarkeit)
4. **Availability-Kette** setzen: Demo **M1 → M2 → M5** (Freischaltung nach Quiz-Abschluss)

### Wann ausführen?

- Nach **erstem Deploy** auf neuem Server
- Nach **Git-Push**, wenn sich URLs, Abschnittsnamen oder Availability geändert haben
- **Nicht** bei reinen JS/CSS-Änderungen in den Moduldateien (dann reicht Cache-Purge)

```bash
php local/p4u/cli/create_training_modules.php
php admin/cli/purge_caches.php
```

## Weiteres Skript

- `create_modul1.php` — Legacy-Hilfe nur für Modul 1 (ersetzt durch `create_training_modules.php`)

## Hinweis Git vs. DB

Das Skript schreibt in die **MariaDB** (Kurs, Abschnitte, Aktivitäten). Der Code liegt in Git; die Kursstruktur auf dem Server entsteht erst nach CLI-Lauf.

Details: [docs/08-environments-and-customer-handoff.md](../../../docs/08-environments-and-customer-handoff.md)
