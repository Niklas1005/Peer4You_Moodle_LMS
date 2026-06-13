# Interactive modules architecture (P4U)

Reference for developers and agents working on Peer4You learning modules in `local/p4u`.

## Module inventory

| Module | Status | Git files | Course activity |
|--------|--------|-----------|-----------------|
| 1 | Interactive | `module1.php`, `module1.js` | URL → `/local/p4u/module1.php` |
| 2 | Interactive | `module2.php`, `module2.js` | URL → `/local/p4u/module2.php` |
| 3 | Placeholder | `module_placeholder.php?module=3` | URL (CLI skeleton) |
| 4 | Placeholder | `module_placeholder.php?module=4` | URL (CLI skeleton) |
| 5 | Interactive | `module5.php`, `module5.js` | URL → `/local/p4u/module5.php` |
| 6 | Placeholder | `module_placeholder.php?module=6` | URL (CLI skeleton) |

**Interactive UI is always in Git** under `Dateien/LMS/public/local/p4u/`.  
**Course sections and URL activities** are in MariaDB, created by `local/p4u/cli/create_training_modules.php`.

See also: `08-environments-and-customer-handoff.md` (Git vs database vs moodledata).

## Progress and completion (two layers)

### Station progress (per module session)

- **Script:** `module-progress.js` — shared helper `P4UProgress.create()`
- **Storage:** `localStorage` key `p4u-module{N}-progress`
- **Payload:** `visited[]`, `currentSection`, `extra` (flashcards, drag-drop, quiz selections, etc.)
- **Restored on:** page load / return from course overview
- **Gated sections:** interactive stations count as visited only after the learner completes the task (e.g. M1 Praxisimpuls after answer)
- **Navigation:** sequential lock — sidebar jumps only to visited stations or the immediate next station

### Module completion (Moodle course)

- **Trigger:** quiz passed (≥ 60 %)
- **Browser:** `localStorage` `p4u-module{N}-completed`, `p4u-module{N}-quiz-stats`
- **Server:** `complete_module.php` → Moodle `course_modules_completion` (activity completion)
- **Not stored in DB:** per-station progress (optional future: `user_preference` endpoint)

## localStorage keys

| Key | Purpose |
|-----|---------|
| `p4u-module{N}-progress` | Station visited, current section, interactive state |
| `p4u-module{N}-completed` | `1` if quiz passed |
| `p4u-module{N}-quiz-stats` | `{ attempts, passed }` |

## Gated sections by module

| Module | Gated section IDs | Completion rule |
|--------|-------------------|-----------------|
| 1 | `exercise`, `video`, `casestudy` | Drag correct; practice answer; case answer |
| 2 | `credit`, `budget`, `counseling` | Practice answer; drag correct; case answer |
| 5 | `signals`, `referral`, `case` | Drag all correct; all referral cases answered; case answer |

## OK badge vs quiz unlock

Both use the same `visited` Set (fixed 2026-06-13). Previously, OK badges used `furthestSectionIndex`, which showed false OK when skipping stations.

Quiz unlock: `requiredBeforeQuiz.every(id => visited.has(id))`.

## File map

```
public/local/p4u/
  module-progress.js    # shared progress store
  module1.php/js        # Modul 1
  module2.php/js        # Modul 2
  module5.php/js        # Modul 5
  module_placeholder.php
  complete_module.php   # Moodle completion API
  styles.css
local/p4u/cli/
  create_training_modules.php
  create_modul1.php
```

## Deploy checklist after JS changes

1. Push to Git → Coolify rebuild on reference server
2. Purge Moodle caches (`purge_caches.php`)
3. Hard-refresh browser (localStorage is per-browser)

## Known limitations

- Station progress is **per browser** (localStorage), not cross-device
- M3/M4/M6 remain placeholders until Seeker delivers content
- Quiz Type A (JS) vs Type B (Moodle Quiz) — manager decision still open

## Change log

- **2026-06-09:** M2/M5 interactive modules merged; `complete_module.php` for Moodle completion sync
- **2026-06-13:** `module-progress.js`; OK-badge fix; nav lock; gated sections; persistence for M1/M2/M5
