/**
 * Peer4You — gemeinsame Interaktionslogik für Modul 3 und 4.
 *
 * @package local_p4u
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
(function() {
    'use strict';

    const shell = document.querySelector('.p4u-shell');
    if (!shell) {
        return;
    }

    const moduleNumber = Number(shell.dataset.moduleNumber);
    const sections = JSON.parse(shell.dataset.sections || '[]');
    const gatedSections = JSON.parse(shell.dataset.gatedSections || '[]');
    let quizAttempt = 1;
    let quizSubmitted = false;

    const byId = id => document.getElementById(id);
    const progress = window.P4UProgress.create({
        moduleNumber,
        sections,
        gatedSections,
        collectExtra,
        onRestore: restoreExtra,
        getQuizStats: () => ({attempts: Math.max(0, quizAttempt - 1), passed: quizSubmitted ? 1 : 0}),
    });

    function showSection(id) {
        if (!progress.canAccessSection(id)) {
            showNotice('Schließe zuerst die vorherige Station ab.');
            return;
        }
        document.querySelectorAll('.p4u-section').forEach(section => section.classList.remove('active'));
        byId('p4u-sec-' + id)?.classList.add('active');
        progress.recordSectionEnter(id);
        progress.updateNav(id);
        const index = sections.indexOf(id);
        byId('p4u-progress-label').textContent = 'Station ' + (index + 1) + ' von ' + sections.length;
        byId('p4u-progress-fill').style.width = Math.round(((index + 1) / sections.length) * 100) + '%';
        byId('p4u-progress-pct').textContent = Math.round(((index + 1) / sections.length) * 100) + ' %';
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function showNotice(text, good = false) {
        const notice = byId('p4u-live-notice');
        if (!notice) return;
        notice.textContent = text;
        notice.classList.toggle('good', good);
        notice.classList.toggle('bad', !good);
    }

    function initChoices() {
        document.querySelectorAll('.p4u-practice-options').forEach(group => {
            group.querySelectorAll('button').forEach(button => button.addEventListener('click', () => {
                group.querySelectorAll('button').forEach(item => item.classList.remove('selected', 'correct', 'incorrect'));
                const correct = button.dataset.correct === 'true';
                button.classList.add('selected', correct ? 'correct' : 'incorrect');
                const feedback = group.parentElement.querySelector('.p4u-practice-feedback');
                if (feedback) {
                    feedback.textContent = button.dataset.feedback || '';
                    feedback.className = 'p4u-practice-feedback ' + (correct ? 'good' : 'bad');
                }
                if (correct && group.dataset.gate) {
                    progress.completeGatedSection(group.dataset.gate);
                    document.querySelector('[data-next-from="' + group.dataset.gate + '"]')?.removeAttribute('disabled');
                    progress.updateNav(group.dataset.gate);
                }
                progress.persist();
            }));
        });
    }

    function initChecks() {
        document.querySelectorAll('.p4u-checklist input').forEach(input => input.addEventListener('change', () => {
            const list = input.closest('.p4u-checklist');
            const all = Array.from(list.querySelectorAll('input')).every(item => item.checked);
            if (all && list.dataset.gate) {
                progress.completeGatedSection(list.dataset.gate);
                document.querySelector('[data-next-from="' + list.dataset.gate + '"]')?.removeAttribute('disabled');
                progress.updateNav(list.dataset.gate);
            }
            progress.persist();
        }));
    }

    function initQuiz() {
        document.querySelectorAll('.p4u-quiz button').forEach(button => button.addEventListener('click', () => {
            const question = button.closest('.p4u-quiz');
            question.querySelectorAll('button').forEach(item => item.classList.remove('selected'));
            button.classList.add('selected');
            progress.persist();
        }));

        byId('p4u-submit-quiz')?.addEventListener('click', async () => {
            const questions = Array.from(document.querySelectorAll('.p4u-quiz'));
            if (questions.some(question => !question.querySelector('button.selected'))) {
                showNotice('Bitte beantworte zuerst alle Fragen.');
                return;
            }
            let correct = 0;
            questions.forEach(question => {
                const selected = question.querySelector('button.selected');
                const isCorrect = selected.dataset.correct === 'true';
                if (isCorrect) correct++;
                selected.classList.add(isCorrect ? 'correct' : 'incorrect');
                question.querySelector('button[data-correct="true"]').classList.add('correct');
                question.querySelectorAll('button').forEach(button => button.disabled = true);
            });
            const percent = Math.round(correct / questions.length * 100);
            quizAttempt++;
            byId('p4u-status-attempts').textContent = String(quizAttempt - 1);
            byId('p4u-quiz-result').textContent = correct + ' von ' + questions.length + ' richtig (' + percent + ' %).';
            byId('p4u-quiz-result').className = 'p4u-quiz-result ' + (percent >= 60 ? 'good' : 'bad');
            if (percent >= 60) {
                quizSubmitted = true;
                byId('p4u-status-label').textContent = 'Abgeschlossen';
                byId('p4u-status-passed').textContent = '1';
                showNotice('Bestanden! Das Modul wurde abgeschlossen.', true);
                await fetch(shell.dataset.completeUrl, {method: 'POST', credentials: 'same-origin'}).catch(() => null);
                window.P4UProgress.updateCourseIndexStatus(moduleNumber, true);
            } else {
                showNotice('Noch nicht bestanden. Mindestens 60 Prozent sind erforderlich.');
                byId('p4u-retry-quiz').classList.remove('p4u-hidden');
            }
            progress.persist();
        });

        byId('p4u-retry-quiz')?.addEventListener('click', () => {
            quizSubmitted = false;
            document.querySelectorAll('.p4u-quiz button').forEach(button => {
                button.disabled = false;
                button.classList.remove('selected', 'correct', 'incorrect');
            });
            byId('p4u-quiz-result').textContent = '';
            byId('p4u-retry-quiz').classList.add('p4u-hidden');
            byId('p4u-quiz-attempt').textContent = 'Versuch ' + quizAttempt;
            progress.persist();
        });
    }

    function collectExtra() {
        const choices = {};
        document.querySelectorAll('.p4u-practice-options').forEach((group, index) => {
            const selected = group.querySelector('button.selected');
            if (selected) choices[index] = selected.textContent.trim();
        });
        const checks = Array.from(document.querySelectorAll('.p4u-checklist input')).map(input => input.checked);
        const quiz = {};
        document.querySelectorAll('.p4u-quiz').forEach((question, index) => {
            const selected = question.querySelector('button.selected');
            if (selected) quiz[index] = selected.textContent.trim();
        });
        return {choices, checks, quiz, quizAttempt, quizSubmitted};
    }

    function restoreExtra(extra) {
        if (!extra) return;
        document.querySelectorAll('.p4u-practice-options').forEach((group, index) => {
            const text = extra.choices?.[index];
            group.querySelectorAll('button').forEach(button => {
                if (button.textContent.trim() === text) button.click();
            });
        });
        document.querySelectorAll('.p4u-checklist input').forEach((input, index) => {
            input.checked = Boolean(extra.checks?.[index]);
        });
        document.querySelectorAll('.p4u-checklist[data-gate]').forEach(list => {
            if (Array.from(list.querySelectorAll('input')).every(input => input.checked)) {
                document.querySelector('[data-next-from="' + list.dataset.gate + '"]')?.removeAttribute('disabled');
            }
        });
        document.querySelectorAll('.p4u-quiz').forEach((question, index) => {
            const text = extra.quiz?.[index];
            question.querySelectorAll('button').forEach(button => {
                if (button.textContent.trim() === text) button.classList.add('selected');
            });
        });
        quizAttempt = Number(extra.quizAttempt) || 1;
        quizSubmitted = Boolean(extra.quizSubmitted);
        byId('p4u-quiz-attempt').textContent = 'Versuch ' + quizAttempt;
    }

    document.addEventListener('DOMContentLoaded', async () => {
        document.querySelectorAll('.p4u-nav button[data-target]').forEach(button => button.addEventListener('click', () => showSection(button.dataset.target)));
        document.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', () => showSection(button.dataset.next)));
        initChoices();
        initChecks();
        initQuiz();
        const remote = await progress.loadRemote();
        if (!remote) progress.load();
        if (remote?.quizStats) {
            byId('p4u-status-attempts').textContent = String(remote.quizStats.attempts || 0);
            byId('p4u-status-passed').textContent = String(remote.quizStats.passed || 0);
        }
        if (remote?.completed) {
            byId('p4u-status-label').textContent = 'Abgeschlossen';
            window.P4UProgress.updateCourseIndexStatus(moduleNumber, true);
        }
        showSection(progress.getCurrentSection());
    });
})();
