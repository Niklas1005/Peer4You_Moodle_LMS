(function() {
    const sections = ['intro', 'signals', 'role', 'referral', 'emergency', 'case', 'quiz'];
    const sectionLabels = ['Einstieg', 'Warnsignale', 'Peer-Rolle', 'Weiterverweisen', 'Notfall', 'Fallbeispiel', 'Abschlussquiz'];
    const requiredBeforeQuiz = ['intro', 'signals', 'role', 'referral', 'emergency', 'case'];
    const MODULE_NUMBER = 5;

    function storageKey(suffix) {
        const userId = document.querySelector('.p4u-shell')?.dataset.userId || '0';
        return 'p4u-u' + userId + '-module' + MODULE_NUMBER + '-' + suffix;
    }

    const progress = P4UProgress.create({
        moduleNumber: MODULE_NUMBER,
        sections,
        gatedSections: ['signals', 'referral', 'case'],
        onRestore: restoreModuleExtra,
        collectExtra: collectModuleExtra,
        getQuizStats,
    });
    const visited = progress.getVisited();

    let dragged = null;
    let quizAttempt = 1;
    let quizSubmitted = false;

    function byId(id) {
        return document.getElementById(id);
    }

    function getQuizStats() {
        try {
            return JSON.parse(localStorage.getItem(storageKey('quiz-stats'))) || {attempts: 0, passed: 0};
        } catch (error) {
            return {attempts: 0, passed: 0};
        }
    }

    function saveQuizStats(stats, syncRemote = true) {
        localStorage.setItem(storageKey('quiz-stats'), JSON.stringify(stats));
        if (syncRemote) {
            progress.persist();
        }
    }

    function isModuleCompleted() {
        return localStorage.getItem(storageKey('completed')) === '1';
    }

    function updateStatusSummary(completed = isModuleCompleted()) {
        const stats = getQuizStats();
        byId('p4u-status-label').textContent = completed ? 'Abgeschlossen' : 'Offen';
        byId('p4u-status-attempts').textContent = stats.attempts;
        byId('p4u-status-passed').textContent = stats.passed;
        byId('p4u-status-note').textContent = completed
            ? 'Dieses Modul wurde bereits bestanden. Du kannst es jederzeit wiederholen.'
            : 'Schliesse das Quiz ab, um Modul 5 im Kurs als erledigt zu markieren.';
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', completed);
    }

    function setCompletionUi(completed) {
        updateStatusSummary(completed);
        P4UProgress.updateCourseIndexStatus(MODULE_NUMBER, completed);
        if (completed) {
            byId('p4u-quiz-state').textContent = 'Bereits bestanden';
        }
    }

    function applyRemoteState(remote) {
        if (remote && remote.quizStats) {
            saveQuizStats(remote.quizStats, false);
        }

        if ((remote && remote.completed) || isModuleCompleted()) {
            localStorage.setItem(storageKey('completed'), '1');
            setCompletionUi(true);
            return;
        }

        setCompletionUi(false);
    }

    function recordQuizAttempt(passed) {
        const stats = getQuizStats();
        stats.attempts++;
        if (passed) {
            stats.passed++;
        }
        saveQuizStats(stats);
        updateStatusSummary(isModuleCompleted() || passed);
    }

    function markModuleCompleted() {
        localStorage.setItem(storageKey('completed'), '1');
        setCompletionUi(true);
        progress.persist();

        const completeUrl = document.querySelector('.p4u-shell')?.dataset.completeUrl;
        if (!completeUrl) {
            return;
        }

        fetch(completeUrl, {
            method: 'POST',
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.ok) {
                    byId('p4u-quiz-state').textContent = 'Bestanden und im Kurs markiert';
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

        if (!progress.canAccessSection(id)) {
            showMessage('Bitte schlie\u00dfe die vorherige Station ab, bevor du hier weitermachst.');
            return;
        }

        document.querySelectorAll('.p4u-section').forEach(section => section.classList.remove('active'));
        byId('p4u-sec-' + id)?.classList.add('active');

        progress.recordSectionEnter(id);
        progress.updateNav(id);
        updateQuizNav();
        updateProgress(sections.indexOf(id));
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
                progress.completeGatedSection('signals');
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

    function initChoiceCards(selector, feedbackId, gateSectionId) {
        const feedback = byId(feedbackId);
        document.querySelectorAll(selector + ' button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll(selector + ' button').forEach(option => option.classList.remove('selected'));
                button.classList.add('selected');
                feedback.textContent = button.dataset.feedback;
                feedback.classList.toggle('good', button.dataset.correct === 'true');
                feedback.classList.toggle('bad', button.dataset.correct !== 'true');
                if (gateSectionId) {
                    progress.completeGatedSection(gateSectionId);
                }
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
        progress.completeGatedSection('referral');
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
                progress.persist();
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
        progress.persist();
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
        progress.persist();
    }

    function collectModuleExtra() {
        const signalPlacements = {};
        document.querySelectorAll('.p4u-drag').forEach(item => {
            const zone = item.closest('.p4u-dropzone');
            signalPlacements[item.dataset.zone + ':' + item.textContent.trim()] = zone ? zone.dataset.zone : 'bank';
        });

        const referralSelections = {};
        document.querySelectorAll('.p4u-referral-case').forEach((caseBlock, index) => {
            const selected = caseBlock.querySelector('button.selected');
            if (selected) {
                referralSelections[index] = selected.dataset.feedback;
            }
        });

        const caseBtn = document.querySelector('.p4u-practice-options button.selected');
        const quizSelections = {};
        document.querySelectorAll('.p4u-quiz').forEach((group, index) => {
            const selected = Array.from(group.querySelectorAll('button.selected')).map(button => button.textContent.trim());
            if (selected.length) {
                quizSelections[index] = selected;
            }
        });

        return {
            signalPlacements,
            referralSelections,
            caseFeedback: caseBtn ? caseBtn.dataset.feedback : null,
            quizAttempt,
            quizSubmitted,
            quizSelections,
        };
    }

    function restoreModuleExtra(extra) {
        if (extra.signalPlacements) {
            Object.entries(extra.signalPlacements).forEach(([itemKey, zoneId]) => {
                const zoneKey = itemKey.split(':')[0];
                const items = Array.from(document.querySelectorAll('.p4u-drag'));
                const item = items.find(node => node.dataset.zone === zoneKey);
                if (!item) {
                    return;
                }
                if (zoneId === 'bank') {
                    const source = document.querySelector('.p4u-dropgrid .p4u-card:first-child');
                    source?.appendChild(item);
                    return;
                }
                const zone = document.querySelector('.p4u-dropzone[data-zone="' + zoneId + '"]');
                zone?.appendChild(item);
            });
        }
        if (extra.referralSelections) {
            const cases = Array.from(document.querySelectorAll('.p4u-referral-case'));
            Object.entries(extra.referralSelections).forEach(([index, feedbackText]) => {
                const caseBlock = cases[Number(index)];
                if (!caseBlock) {
                    return;
                }
                caseBlock.querySelectorAll('button').forEach(button => {
                    if (button.dataset.feedback === feedbackText) {
                        button.classList.add('selected');
                        const feedback = caseBlock.querySelector('.p4u-case-feedback');
                        if (feedback) {
                            feedback.textContent = feedbackText;
                            feedback.classList.toggle('good', button.dataset.correct === 'true');
                            feedback.classList.toggle('bad', button.dataset.correct !== 'true');
                        }
                    }
                });
            });
        }
        if (extra.caseFeedback) {
            document.querySelectorAll('.p4u-practice-options button').forEach(button => {
                if (button.dataset.feedback === extra.caseFeedback) {
                    button.classList.add('selected');
                    const feedback = byId('p4u-case-feedback');
                    if (feedback) {
                        feedback.textContent = extra.caseFeedback;
                        feedback.classList.toggle('good', button.dataset.correct === 'true');
                        feedback.classList.toggle('bad', button.dataset.correct !== 'true');
                    }
                }
            });
        }
        if (typeof extra.quizAttempt === 'number') {
            quizAttempt = extra.quizAttempt;
        }
        if (extra.quizSubmitted) {
            quizSubmitted = true;
            byId('p4u-submit-quiz').disabled = true;
            byId('p4u-retry-quiz')?.classList.remove('p4u-hidden');
            document.querySelectorAll('.p4u-quiz button').forEach(option => option.disabled = true);
        }
        if (extra.quizSelections) {
            document.querySelectorAll('.p4u-quiz').forEach((group, index) => {
                const saved = extra.quizSelections[index];
                if (!saved) {
                    return;
                }
                group.querySelectorAll('button').forEach(button => {
                    if (saved.includes(button.textContent.trim())) {
                        button.classList.add('selected');
                    }
                });
            });
        }
        updateReferralResult();
    }

    function updateQuizAttemptLabel() {
        byId('p4u-quiz-attempt').textContent = 'Versuch ' + quizAttempt;
    }

    document.addEventListener('DOMContentLoaded', async () => {
        document.querySelectorAll('.p4u-nav button[data-target]').forEach(button => {
            button.addEventListener('click', () => showSection(button.dataset.target));
        });
        document.querySelectorAll('[data-next]').forEach(button => {
            button.addEventListener('click', () => showSection(button.dataset.next));
        });

        initSignalDragDrop();
        initChoiceCards('.p4u-role-options', 'p4u-role-feedback');
        initChoiceCards('.p4u-practice-options', 'p4u-case-feedback', 'case');
        initReferralChoices();
        initQuiz();

        const remote = await progress.loadRemote();
        if (!remote) {
            progress.load();
        }
        applyRemoteState(remote);
        updateQuizAttemptLabel();
        showSection(progress.getCurrentSection());
    });
})();
