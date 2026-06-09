(function() {
    const sections = ['intro', 'signals', 'role', 'referral', 'emergency', 'case', 'quiz'];
    const sectionLabels = ['Einstieg', 'Warnsignale', 'Peer-Rolle', 'Weiterverweisen', 'Notfall', 'Fallbeispiel', 'Abschlussquiz'];
    const requiredBeforeQuiz = ['intro', 'signals', 'role', 'referral', 'emergency', 'case'];
    const completionKey = 'p4u-module5-completed';
    const statsKey = 'p4u-module5-quiz-stats';
    const visited = new Set(['intro']);

    let dragged = null;
    let quizAttempt = 1;
    let quizSubmitted = false;
    let furthestSectionIndex = 0;

    function byId(id) {
        return document.getElementById(id);
    }

    function readStats() {
        try {
            return JSON.parse(localStorage.getItem(statsKey)) || {attempts: 0, passed: 0};
        } catch (error) {
            return {attempts: 0, passed: 0};
        }
    }

    function writeStats(stats) {
        localStorage.setItem(statsKey, JSON.stringify(stats));
    }

    function isModuleCompleted() {
        return localStorage.getItem(completionKey) === '1';
    }

    function updateStatusSummary() {
        const stats = readStats();
        const completed = isModuleCompleted();
        byId('p4u-status-label').textContent = completed ? 'Abgeschlossen' : stats.attempts > 0 ? 'In Bearbeitung' : 'Offen';
        byId('p4u-status-attempts').textContent = stats.attempts;
        byId('p4u-status-passed').textContent = stats.passed;
        byId('p4u-status-note').textContent = completed
            ? 'Dieses Modul wurde bereits bestanden. Du kannst es jederzeit wiederholen.'
            : 'Schliesse das Quiz ab, um Modul 5 im Kurs als erledigt zu markieren.';
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', completed);
    }

    function recordQuizAttempt(passed) {
        const stats = readStats();
        stats.attempts++;
        if (passed) {
            stats.passed++;
        }
        writeStats(stats);
        updateStatusSummary();
    }

    function markModuleCompleted() {
        localStorage.setItem(completionKey, '1');
        updateStatusSummary();

        const completeUrl = document.querySelector('.p4u-shell')?.dataset.completeUrl;
        if (!completeUrl) {
            return;
        }

        fetch(completeUrl, {credentials: 'same-origin'})
            .then(response => response.json())
            .catch(() => null);
    }

    function loadMoodleCompletionStatus() {
        const statusUrl = document.querySelector('.p4u-shell')?.dataset.statusUrl;
        if (!statusUrl) {
            return;
        }

        fetch(statusUrl, {credentials: 'same-origin'})
            .then(response => response.json())
            .then(data => {
                if (data && data.completed) {
                    localStorage.setItem(completionKey, '1');
                    updateStatusSummary();
                }
            })
            .catch(() => null);
    }

    function isQuizUnlocked() {
        return requiredBeforeQuiz.every(id => visited.has(id));
    }

    function showMessage(message) {
        const result = byId('p4u-quiz-result') || byId('p4u-signals-result');
        if (!result) {
            return;
        }
        result.textContent = message;
        result.classList.remove('good', 'bad');
        result.classList.add('warning');
    }

    function updateQuizNav() {
        const quizNav = byId('p4u-quiz-nav');
        if (!quizNav) {
            return;
        }
        quizNav.classList.toggle('locked', !isQuizUnlocked());
    }

    function updateProgress(index) {
        const pct = Math.round(((index + 1) / sections.length) * 100);
        byId('p4u-progress-label').textContent = sectionLabels[index] + ' \u00b7 Station ' + (index + 1) + ' von ' + sections.length;
        byId('p4u-progress-fill').style.width = pct + '%';
        byId('p4u-progress-pct').textContent = pct + ' %';
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', isModuleCompleted());
    }

    function showSection(id) {
        if (id === 'quiz' && !isQuizUnlocked()) {
            showMessage('Das Quiz wird freigeschaltet, wenn du alle vorherigen Abschnitte besucht hast.');
            return;
        }

        document.querySelectorAll('.p4u-section').forEach(section => section.classList.remove('active'));
        byId('p4u-sec-' + id)?.classList.add('active');

        const currentIndex = sections.indexOf(id);
        furthestSectionIndex = Math.max(furthestSectionIndex, currentIndex);

        document.querySelectorAll('.p4u-nav button').forEach(button => {
            button.classList.toggle('active', button.dataset.target === id);
            const targetIndex = sections.indexOf(button.dataset.target);
            button.classList.toggle('visited', targetIndex >= 0 && targetIndex < furthestSectionIndex);
        });

        visited.add(id);
        updateQuizNav();
        updateProgress(currentIndex);
        window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function initSignalDragDrop() {
        document.querySelectorAll('.p4u-drag').forEach(item => {
            item.addEventListener('dragstart', event => {
                dragged = event.currentTarget;
            });
            item.addEventListener('dragend', () => {
                dragged = null;
            });
        });

        document.querySelectorAll('.p4u-dropzone').forEach(zone => {
            zone.addEventListener('dragover', event => {
                event.preventDefault();
                zone.classList.add('over');
            });
            zone.addEventListener('dragleave', () => zone.classList.remove('over'));
            zone.addEventListener('drop', event => {
                event.preventDefault();
                zone.classList.remove('over');
                if (dragged) {
                    zone.appendChild(dragged);
                }
            });
        });

        byId('p4u-check-signals')?.addEventListener('click', () => {
            const items = Array.from(document.querySelectorAll('.p4u-drag'));
            const placed = items.filter(item => item.closest('.p4u-dropzone'));
            const correct = placed.filter(item => item.closest('.p4u-dropzone')?.dataset.zone === item.dataset.zone);
            const result = byId('p4u-signals-result');
            result.classList.remove('good', 'bad', 'warning');
            if (placed.length < items.length) {
                result.textContent = 'Ordne erst alle Hinweise einer Kategorie zu.';
                result.classList.add('warning');
                return;
            }
            if (correct.length === items.length) {
                result.textContent = 'Richtig: Du hast die Warnsignale passend eingeordnet.';
                result.classList.add('good');
            } else {
                result.textContent = 'Noch nicht ganz: Pruefe besonders rechtliche und psychische Warnsignale nochmal.';
                result.classList.add('bad');
            }
        });

        byId('p4u-reset-signals')?.addEventListener('click', () => {
            const source = document.querySelector('.p4u-dropgrid .p4u-card:first-child');
            document.querySelectorAll('.p4u-drag').forEach(item => source.appendChild(item));
            const result = byId('p4u-signals-result');
            result.textContent = '';
            result.classList.remove('good', 'bad', 'warning');
        });
    }

    function initChoiceCards(selector, feedbackId) {
        const feedback = byId(feedbackId);
        document.querySelectorAll(selector + ' button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll(selector + ' button').forEach(option => option.classList.remove('selected'));
                button.classList.add('selected');
                feedback.textContent = button.dataset.feedback;
                feedback.classList.toggle('good', button.dataset.correct === 'true');
                feedback.classList.toggle('bad', button.dataset.correct !== 'true');
            });
        });
    }

    function initReferralChoices() {
        document.querySelectorAll('.p4u-referral-options button').forEach(button => {
            button.type = 'button';
        });

        document.addEventListener('click', event => {
            const button = event.target.closest('.p4u-referral-options button');
            if (!button) {
                return;
            }

            const caseBlock = button.closest('.p4u-referral-case');
            const feedback = caseBlock?.querySelector('.p4u-case-feedback');
            if (!caseBlock || !feedback) {
                return;
            }

            caseBlock.querySelectorAll('button').forEach(option => option.classList.remove('selected'));
            button.classList.add('selected');
            feedback.textContent = button.dataset.feedback;
            feedback.classList.toggle('good', button.dataset.correct === 'true');
            feedback.classList.toggle('bad', button.dataset.correct !== 'true');
            updateReferralResult();
        });
    }

    function updateReferralResult() {
        const cases = Array.from(document.querySelectorAll('.p4u-referral-case'));
        const answered = cases.filter(caseBlock => caseBlock.querySelector('button.selected'));
        const correct = cases.filter(caseBlock => caseBlock.querySelector('button.selected[data-correct="true"]'));
        const result = byId('p4u-referral-result');
        if (!result) {
            return;
        }
        result.classList.remove('good', 'bad', 'warning');
        if (answered.length < cases.length) {
            result.textContent = 'Bearbeite noch alle Situationen.';
            result.classList.add('warning');
            return;
        }
        if (correct.length === cases.length) {
            result.textContent = 'Sehr gut: Du hast fuer alle Situationen eine passende Weiterverweisung gewaehlt.';
            result.classList.add('good');
        } else {
            result.textContent = 'Teilweise richtig: Schau dir die Rueckmeldungen an und pruefe, welche Fachstelle jeweils am besten passt.';
            result.classList.add('bad');
        }
    }

    function initQuiz() {
        document.querySelectorAll('.p4u-quiz button').forEach(option => {
            option.addEventListener('click', () => {
                const group = option.closest('.p4u-quiz');
                group.classList.remove('unanswered');
                const feedback = group.querySelector('.p4u-question-feedback');
                if (feedback) {
                    feedback.textContent = '';
                    feedback.classList.remove('good', 'bad');
                }
                if (group.dataset.multi === 'true') {
                    option.classList.toggle('selected');
                } else {
                    group.querySelectorAll('button').forEach(button => button.classList.remove('selected'));
                    option.classList.add('selected');
                }
            });
        });

        byId('p4u-submit-quiz')?.addEventListener('click', checkQuiz);
        byId('p4u-retry-quiz')?.addEventListener('click', resetQuizAttempt);
        updateQuizAttemptLabel();
    }

    function checkQuiz() {
        if (quizSubmitted) {
            showMessage('Der aktuelle Versuch ist bereits abgeschlossen. Starte einen neuen Versuch.');
            return;
        }

        const groups = document.querySelectorAll('.p4u-quiz');
        let correctGroups = 0;
        let answeredGroups = 0;

        groups.forEach(group => {
            group.classList.remove('unanswered');
            const options = Array.from(group.querySelectorAll('button'));
            const selected = options.filter(option => option.classList.contains('selected'));
            let groupCorrect = selected.length > 0;
            if (selected.length > 0) {
                answeredGroups++;
            } else {
                group.classList.add('unanswered');
            }

            options.forEach(option => {
                option.classList.remove('correct', 'incorrect');
                const shouldSelect = option.dataset.correct === 'true';
                const isSelected = option.classList.contains('selected');
                if (shouldSelect) {
                    option.classList.add('correct');
                }
                if (isSelected && !shouldSelect) {
                    option.classList.add('incorrect');
                }
                if (shouldSelect !== isSelected) {
                    groupCorrect = false;
                }
            });

            if (groupCorrect) {
                correctGroups++;
            }
            updateQuestionFeedback(group, groupCorrect, selected.length > 0);
        });

        const result = byId('p4u-quiz-result');
        result.classList.remove('good', 'bad', 'warning');
        if (answeredGroups < groups.length) {
            const missing = groups.length - answeredGroups;
            result.innerHTML = '<strong>Noch nicht abschliessbar</strong><span>Noch ' + missing + ' Frage(n) sind offen.</span>';
            result.classList.add('warning');
            result.scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }

        quizSubmitted = true;
        byId('p4u-submit-quiz').disabled = true;
        byId('p4u-retry-quiz').classList.remove('p4u-hidden');
        byId('p4u-quiz-state').textContent = 'Versuch abgeschlossen';
        document.querySelectorAll('.p4u-quiz button').forEach(option => option.disabled = true);

        const percent = Math.round((correctGroups / groups.length) * 100);
        const passed = percent >= 60;
        recordQuizAttempt(passed);
        if (passed) {
            result.innerHTML = '<strong>Bestanden: ' + percent + ' %</strong><span>Modul 5 ist abgeschlossen. Du kannst es bei Bedarf trotzdem wiederholen.</span>';
            result.classList.add('good');
            byId('p4u-completion').classList.remove('p4u-hidden');
            byId('p4u-quiz-state').textContent = 'Bestanden';
            markModuleCompleted();
        } else {
            result.innerHTML = '<strong>Noch nicht bestanden: ' + percent + ' %</strong><span>Du brauchst mindestens 60 %. Schau dir die Hinweise an und starte danach einen neuen Versuch.</span>';
            result.classList.add('bad');
            byId('p4u-completion').classList.add('p4u-hidden');
            byId('p4u-quiz-state').textContent = 'Noch nicht bestanden';
        }

        result.scrollIntoView({behavior: 'smooth', block: 'center'});
    }

    function updateQuestionFeedback(group, isCorrect, isAnswered) {
        const feedback = group.querySelector('.p4u-question-feedback');
        if (!feedback || !isAnswered) {
            return;
        }
        feedback.textContent = isCorrect ? feedback.dataset.success : feedback.dataset.error;
        feedback.classList.toggle('good', isCorrect);
        feedback.classList.toggle('bad', !isCorrect);
    }

    function resetQuizAttempt() {
        quizAttempt++;
        quizSubmitted = false;
        document.querySelectorAll('.p4u-quiz button').forEach(option => {
            option.disabled = false;
            option.classList.remove('selected', 'correct', 'incorrect');
        });
        document.querySelectorAll('.p4u-quiz').forEach(group => group.classList.remove('unanswered'));
        document.querySelectorAll('.p4u-question-feedback').forEach(feedback => {
            feedback.textContent = '';
            feedback.classList.remove('good', 'bad');
        });
        const result = byId('p4u-quiz-result');
        result.textContent = '';
        result.classList.remove('good', 'bad', 'warning');
        byId('p4u-submit-quiz').disabled = false;
        byId('p4u-retry-quiz').classList.add('p4u-hidden');
        byId('p4u-completion').classList.add('p4u-hidden');
        byId('p4u-quiz-state').textContent = 'Neuer Versuch bereit';
        updateQuizAttemptLabel();
    }

    function updateQuizAttemptLabel() {
        byId('p4u-quiz-attempt').textContent = 'Versuch ' + quizAttempt;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.p4u-nav button[data-target]').forEach(button => {
            button.addEventListener('click', () => showSection(button.dataset.target));
        });
        document.querySelectorAll('[data-next]').forEach(button => {
            button.addEventListener('click', () => showSection(button.dataset.next));
        });

        initSignalDragDrop();
        initChoiceCards('.p4u-role-options', 'p4u-role-feedback');
        initChoiceCards('.p4u-practice-options', 'p4u-case-feedback');
        initReferralChoices();
        initQuiz();
        updateStatusSummary();
        loadMoodleCompletionStatus();
        updateProgress(0);
        updateQuizNav();
    });
})();
