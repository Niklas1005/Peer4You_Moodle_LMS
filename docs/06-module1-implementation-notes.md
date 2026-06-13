# Modul 1 Implementation Notes

## Purpose
Modul 1 is the first interactive learning module for the Peer4You Moodle training. It demonstrates the intended learning format for future modules: short input, active interaction, realistic application, and retryable knowledge checks.

## Current Learning Flow
- External Peer4You dashboard opens the interactive Moodle module.
- Modul 1 is shown as a guided learning path with seven stations: Einstieg, Grundlagen, Rolle klaeren, Rollencheck, Praxisimpuls, Fall anwenden, Abschlussquiz.
- The quiz checks the whole module: basic knowledge, role boundaries, and application in the Marko case.
- The quiz supports multiple attempts and gives direct feedback per question.

## Didactic Intent
- Start with orientation, duration, and learning goals.
- Reinforce core concepts through cards and role-boundary sorting.
- Apply the role in a realistic case before the quiz.
- Use the final quiz as a module-level check, not as an isolated memory test.
- Use retryable quiz attempts as learning support, not as a punitive test.

## Current Strengths
- Interactive learner journey is visible inside Moodle.
- Moodle course context is available.
- Admin user is enrolled in the test course, so it appears on the Moodle dashboard.
- Progress, completion state, and quiz attempts are shown inside the module.
- The module has a clear completion moment after a successful quiz attempt.

## Quiz content: Type A vs Type B (decision pending)

| Type | Location | Handoff |
|------|----------|---------|
| **A** | `module1.js` (current end-of-module quiz) | Git deploy |
| **B** | Moodle Quiz activity in course | `.mbz` export |

**Status (2026-05-19):** Team tendency is **Type B**; **manager approval pending** — see `Notizen/Entwicklung To-Do.md` and `docs/08-environments-and-customer-handoff.md`.

## Known Implementation Gaps
- Quiz results are not yet written to the Moodle gradebook.
- Station progress is persisted in **browser localStorage** (`module-progress.js`); optional server-side sync not implemented.
- Video content still needs the final media file.
- Module content should be reviewed by the training/domain team before rollout.

## Modules 2 and 5

M2 and M5 follow the same pattern as Modul 1 (`module2.php/js`, `module5.php/js`, shared `module-progress.js`). See `09-interactive-modules-architecture.md`.

## Focus For Next Iteration
- Finalize wording and examples in Modul 1.
- Replace the video section with the final media asset or a short scripted clip.
- Optional: server-side progress via Moodle `user_preference` for cross-device handoff.
- M3/M4/M6: replace placeholders when Seeker content arrives.
