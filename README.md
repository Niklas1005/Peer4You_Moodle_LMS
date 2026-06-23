# Peer4You — Moodle LMS

E-Learning-Plattform für die **Peer-Schuldenberater-Ausbildung** der Diakonie Baden-Baden und Rastatt.  
Hochschulprojekt **SW_Implementierung** (Fakultät W) — technische Basis: **Moodle 5.2 Stable** (`MOODLE_502_STABLE`), PHP 8.3, Docker.

**Live-Referenz:** [lms.schulerwebdesign.de](https://lms.schulerwebdesign.de) (Coolify/Hetzner).  
Push auf `main` deployt automatisch den Code auf den Referenzserver.

---

## Schnellstart (lokal mit Docker)

```bash
git clone https://github.com/Niklas1005/Peer4You_Moodle_LMS.git
cd Peer4You_Moodle_LMS
git checkout MOODLE_502_STABLE
cp .env.example .env          # Passwörter anpassen
docker compose up -d --build
```

- Moodle: `http://localhost:8080`
- Web-Root: `public/` (nicht Repo-Root)
- Nach Erstinstallation: Theme aktivieren, CLI-Kursstruktur anlegen — Details in [docs/02-local-setup.md](docs/02-local-setup.md)

```bash
docker compose exec web php admin/cli/cfg.php --name=theme --set=p4u
docker compose exec web php local/p4u/cli/create_training_modules.php
docker compose exec web php admin/cli/purge_caches.php
```

---

## Eigene Entwicklung (Projektgruppe)

| Pfad | Zweck | Status |
|------|-------|--------|
| `public/theme/p4u/` | Custom-Theme: Login, Dashboard, Diakonie-Branding | aktiv |
| `public/local/p4u/` | Interaktive Lernmodule M1/M2/M5, Fortschritt, APIs | M1/M2/M5 live; M3/M4/M6 Platzhalter |
| `local/p4u/cli/` | CLI: Kurs `P4U-TEST`, 6 Abschnitte, Availability-Kette | getestet |
| `docker/`, `docker-compose.yml` | Lokaler Dev-Stack (web, db, cron) | aktiv |
| `scripts/handoff/` | `.mbz`-Export/Import für Kundenübergabe | dokumentiert |
| `docs/` | Architektur, Setup, Deploy, Handoff | laufend |

Ausführliche Übersicht für Bericht und Team: [docs/UEBERSICHT_EIGENE_ENTWICKLUNG.md](docs/UEBERSICHT_EIGENE_ENTWICKLUNG.md)

---

## Nicht unsere Arbeit

Dieses Repository enthält den **vollständigen Moodle-Upstream-Tree** (GPL). Folgende Bereiche stammen von Moodle HQ / Community und wurden **nicht** vom Projektteam entwickelt:

| Bereich | Beispiele |
|---------|-----------|
| Moodle Core | `public/lib/`, `admin/`, `public/mod/`, Standard-Themes |
| Upstream-Doku | `INSTALL.txt`, `CONTRIBUTING.md` |
| Original-README | [docs/upstream-moodle-readme.md](docs/upstream-moodle-readme.md) |

**Nicht im Git** (pro Server/Instanz): Kursinhalte, Quiz-Fragen (Moodle-UI), Nutzer, Uploads, `config.php` — siehe [docs/08-environments-and-customer-handoff.md](docs/08-environments-and-customer-handoff.md).

---

## Projektstruktur (Team-Pfade)

```
Peer4You_Moodle_LMS/
├── public/
│   ├── theme/p4u/          # Custom-Theme (Boost-Kind)
│   └── local/p4u/          # Plugin: interaktive Module, APIs, JS
├── local/p4u/cli/          # CLI-Skripte (Kursstruktur)
├── docker/                 # PHP/Apache-Image, Entrypoint
├── docker-compose.yml
├── scripts/handoff/        # Kurs-Backup Export/Import
├── docs/                   # Projektdokumentation
├── .agents/skills/         # Cursor-Entwicklungstool (s. unten)
└── skills-lock.json        # Cursor Skill-Lock (s. unten)
```

---

## Dokumentation

| Dokument | Inhalt |
|----------|--------|
| [docs/README.md](docs/README.md) | Index aller Dokumente |
| [docs/02-local-setup.md](docs/02-local-setup.md) | Lokales Setup Schritt für Schritt |
| [docs/03-customization-strategy.md](docs/03-customization-strategy.md) | Upgrade-sichere Anpassungsstrategie |
| [docs/09-interactive-modules-architecture.md](docs/09-interactive-modules-architecture.md) | Modul-Architektur, Fortschritt, APIs |
| [docs/08-environments-and-customer-handoff.md](docs/08-environments-and-customer-handoff.md) | Git vs. DB vs. moodledata, Kundenübergabe |

---

## Cursor-Entwicklungstools (nicht Teil der LMS-Lösung)

Verzeichnis `.agents/skills/` und Datei `skills-lock.json` dienen der **KI-gestützten Entwicklung in Cursor** und gehören nicht zur lauffähigen Moodle-Lösung. Sie können bei Bedarf ignoriert oder vor einer Abgabe entfernt werden — die LMS-Funktionalität hängt nicht davon ab.

---

## Lizenz

- **Moodle Core:** GNU GPL v3 — siehe [docs/upstream-moodle-readme.md](docs/upstream-moodle-readme.md) und [moodledev.io/general/license](https://moodledev.io/general/license)
- **Eigene Plugins** (`theme_p4u`, `local_p4u`): GNU GPL v3 (Moodle-Plugin-Konvention)

---

## Team / Hochschule

| Name | Rolle | Schwerpunkt |
|------|-------|-------------|
| Niklas *(Nachname)* | *(Rolle)* | Theme, Infrastruktur, Dokumentation |
| Alexander *(Nachname)* | *(Rolle)* | Interaktive Module, Inhalte |

*Platzhalter — vom Team vor Abgabe ergänzen (siehe auch Anhang Team-Beiträge in der Projektdokumentation).*

Hochschule: *(Name)* · Studiengang: *(Name)* · Modul: SW_Implementierung · Betreuer: *(Name)*
