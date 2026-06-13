(function() {
    const sections = ['intro', 'flashcards', 'roles', 'exercise', 'video', 'casestudy', 'quiz'];
    const sectionLabels = ['Einstieg', 'Grundlagen', 'Rolle kl\u00e4ren', 'Zuordnung', 'Praxisimpuls', 'Fallbeispiel', 'Abschlussquiz'];
    const requiredBeforeQuiz = ['intro', 'flashcards', 'roles', 'exercise', 'video', 'casestudy'];
    const completionKey = 'p4u-module1-completed';
    const statsKey = 'p4u-module1-quiz-stats';

    const flashcards = [
        {
            front: 'Was ist Peer-Beratung?',
            back: 'Beratung auf Augenh\u00f6he durch Gleichaltrige. Peers h\u00f6ren zu, informieren und vermitteln an Fachstellen weiter.'
        },
        {
            front: 'Warum ist Peer-Beratung wirksam?',
            back: 'Sie baut Hemmschwellen ab, st\u00e4rkt Vertrauen und hilft, Probleme fr\u00fcher anzusprechen.'
        },
        {
            front: 'Deine Rolle als Peer',
            back: 'Du begleitest und unterst\u00fctzt in der Peerberatung, ohne professionelle Beratung zu ersetzen.'
        },
        {
            front: 'Was Peers nicht tun',
            back: 'Keine rechtliche Beratung, keine Empfehlung von Finanzprodukten, stattdessen Weitervermittlung an Fachstellen.'
        }
    ];

    const progress = P4UProgress.create({
        moduleNumber: 1,
        sections,
        gatedSections: ['exercise', 'video', 'casestudy'],
        onRestore: restoreModuleExtra,
        collectExtra: collectModuleExtra,
    });
    const visited = progress.getVisited();

    let flashIndex = 0;
    let dragged = null;
    let quizAttempt = 1;
    let quizSubmitted = false;

    function byId(id) {
        return document.getElementById(id);
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

    function updateProgress(index) {
        const pct = Math.round(((index + 1) / sections.length) * 100);
        byId('p4u-progress-label').textContent = sectionLabels[index] + ' \u00b7 Station ' + (index + 1) + ' von ' + sections.length;
        byId('p4u-progress-fill').style.width = pct + '%';
        byId('p4u-progress-pct').textContent = pct + ' %';
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', isModuleCompleted());
    }

    function isQuizUnlocked() {
        return requiredBeforeQuiz.every(id => visited.has(id));
    }

    function isModuleCompleted() {
        return localStorage.getItem(completionKey) === '1';
    }

    function getQuizStats() {
        try {
            return JSON.parse(localStorage.getItem(statsKey)) || {attempts: 0, passed: 0};
        } catch (error) {
            return {attempts: 0, passed: 0};
        }
    }

    function saveQuizStats(stats) {
        localStorage.setItem(statsKey, JSON.stringify(stats));
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

    function updateStatusSummary(completed = isModuleCompleted()) {
        const stats = getQuizStats();
        byId('p4u-status-label').textContent = completed ? 'Abgeschlossen' : 'Offen';
        byId('p4u-status-attempts').textContent = String(stats.attempts);
        byId('p4u-status-passed').textContent = String(stats.passed);
        byId('p4u-status-note').textContent = completed
            ? 'Modul 1 ist abgeschlossen. Du kannst die Inhalte trotzdem jederzeit wiederholen.'
            : 'Schlie\u00dfe das Quiz ab, um Modul 1 im Kurs als erledigt zu markieren.';
    }

    function setCompletionUi(completed) {
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', completed);
        updateStatusSummary(completed);
        updateCourseIndexStatus(completed);
        if (completed) {
            byId('p4u-quiz-state').textContent = 'Bereits bestanden';
        }
    }

    function addCourseIndexBadge(link, label, state) {
        const item = link.closest('li, .courseindex-item, .courseindex-link, .activity-item, div') || link;
        item.querySelectorAll('.p4u-index-status').forEach(existing => existing.remove());

        const badge = document.createElement('span');
        badge.className = 'p4u-index-status is-' + state;
        badge.textContent = label;
        link.insertAdjacentElement('afterend', badge);
    }

    function updateCourseIndexStatus(completed) {
        document.querySelectorAll('#courseindex a, .courseindex a, [data-region="courseindex"] a').forEach(link => {
            const label = (link.textContent || '').trim();
            if (label.includes('Interaktives Lernmodul starten')) {
                addCourseIndexBadge(link, completed ? 'Abgeschlossen' : 'In Bearbeitung', completed ? 'done' : 'active');
            } else if (label.includes('vorbereiten')) {
                addCourseIndexBadge(link, 'Offen', 'open');
            }
        });
    }

    function markModuleCompleted() {
        localStorage.setItem(completionKey, '1');
        setCompletionUi(true);
        syncMoodleCompletion();
    }

    function loadMoodleCompletionStatus() {
        const statusUrl = document.querySelector('.p4u-shell')?.dataset.statusUrl;
        if (!statusUrl) {
            setCompletionUi(isModuleCompleted());
            return;
        }

        fetch(statusUrl, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.ok && data.completed) {
                    localStorage.setItem(completionKey, '1');
                    setCompletionUi(true);
                } else {
                    setCompletionUi(isModuleCompleted());
                }
            })
            .catch(() => setCompletionUi(isModuleCompleted()));
    }

    function syncMoodleCompletion() {
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
            .catch(() => {
                // The in-module completion state remains visible even if Moodle sync is temporarily unavailable.
            });
    }

    function updateQuizNav() {
        byId('p4u-quiz-nav')?.classList.toggle('locked', !isQuizUnlocked());
    }

    function showMessage(message) {
        const existing = document.querySelector('.p4u-toast');
        if (existing) {
            existing.remove();
        }
        const toast = document.createElement('div');
        toast.className = 'p4u-toast alert alert-info';
        toast.style.position = 'fixed';
        toast.style.right = '24px';
        toast.style.bottom = '24px';
        toast.style.zIndex = '9999';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2800);
    }

    function renderFlashcard() {
        const card = flashcards[flashIndex];
        byId('p4u-flash-front').textContent = card.front;
        byId('p4u-flash-back').textContent = card.back;
        byId('p4u-flash-count').textContent = 'Karte ' + (flashIndex + 1) + ' von ' + flashcards.length;
        byId('p4u-flashcard').classList.remove('flipped');
        byId('p4u-flash-prev').disabled = flashIndex === 0;
        byId('p4u-flash-next').disabled = flashIndex === flashcards.length - 1;
        byId('p4u-flash-next').textContent = flashIndex === flashcards.length - 1 ? 'Alle Karten gesehen' : 'N\u00e4chste Karte';
    }

    function initFlashcards() {
        const card = byId('p4u-flashcard');
        card?.addEventListener('click', () => card.classList.toggle('flipped'));
        card?.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.classList.toggle('flipped');
            }
        });
        byId('p4u-flash-prev')?.addEventListener('click', () => {
            flashIndex = Math.max(0, flashIndex - 1);
            renderFlashcard();
            progress.persist();
        });
        byId('p4u-flash-next')?.addEventListener('click', () => {
            flashIndex = Math.min(flashcards.length - 1, flashIndex + 1);
            renderFlashcard();
            progress.persist();
        });
        renderFlashcard();
    }

    function initDragDrop() {
        document.querySelectorAll('.p4u-draggable').forEach(card => {
            card.addEventListener('dragstart', () => {
                dragged = card;
                card.classList.remove('correct', 'incorrect');
                byId('p4u-drag-next').disabled = true;
            });
        });

        document.querySelectorAll('.p4u-dropzone, #p4u-card-bank').forEach(zone => {
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
                    dragged = null;
                    byId('p4u-drag-next').disabled = true;
                }
            });
        });

        byId('p4u-check-drag')?.addEventListener('click', checkDragAnswers);
        byId('p4u-reset-drag')?.addEventListener('click', resetDragAnswers);
    }

    function checkDragAnswers() {
        let total = 0;
        let correct = 0;

        document.querySelectorAll('.p4u-draggable').forEach(card => {
            card.classList.remove('correct', 'incorrect');
        });

        document.querySelectorAll('.p4u-dropzone').forEach(zone => {
            const accepted = (zone.dataset.accept || '').split(',');
            zone.querySelectorAll('.p4u-draggable').forEach(card => {
                total++;
                if (accepted.includes(card.dataset.id)) {
                    correct++;
                    card.classList.add('correct');
                } else {
                    card.classList.add('incorrect');
                }
            });
        });

        byId('p4u-card-bank').querySelectorAll('.p4u-draggable').forEach(card => {
            total++;
            card.classList.add('incorrect');
        });

        if (total === 0) {
            showMessage('Ziehe zuerst Karten in die Felder.');
            byId('p4u-drag-next').disabled = true;
        } else if (correct === total) {
            byId('p4u-drag-next').disabled = false;
            progress.completeGatedSection('exercise');
            showMessage('Alles richtig. Du kannst weitergehen.');
        } else {
            byId('p4u-drag-next').disabled = true;
            showMessage(correct + ' von ' + total + ' richtig. Korrigiere die roten Karten.');
        }
    }

    function resetDragAnswers() {
        const bank = byId('p4u-card-bank');
        document.querySelectorAll('.p4u-draggable').forEach(card => {
            card.classList.remove('correct', 'incorrect');
            bank.appendChild(card);
        });
        byId('p4u-drag-next').disabled = true;
    }

    function initQuiz() {
        document.querySelectorAll('.p4u-quiz button').forEach(option => {
            option.addEventListener('click', () => {
                if (quizSubmitted) {
                    showMessage('Dieser Versuch ist abgeschlossen. Starte einen neuen Versuch, um Antworten zu \u00e4ndern.');
                    return;
                }
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

    function initRoleSelfcheck() {
        document.querySelectorAll('.p4u-role-option').forEach(option => {
            option.addEventListener('click', () => {
                const isCorrect = option.dataset.correct === 'true';
                document.querySelectorAll('.p4u-role-option').forEach(button => {
                    button.classList.remove('selected', 'correct', 'incorrect');
                });
                option.classList.add('selected', isCorrect ? 'correct' : 'incorrect');

                const feedback = byId('p4u-role-feedback');
                feedback.textContent = option.dataset.feedback;
                feedback.classList.toggle('good', isCorrect);
                feedback.classList.toggle('bad', !isCorrect);
            });
        });
    }

    function initCaseChoice() {
        document.querySelectorAll('.p4u-case-options button').forEach(option => {
            option.addEventListener('click', () => {
                const isCorrect = option.dataset.correct === 'true';
                document.querySelectorAll('.p4u-case-options button').forEach(button => {
                    button.classList.remove('selected', 'correct', 'incorrect');
                });
                option.classList.add('selected', isCorrect ? 'correct' : 'incorrect');

                const feedback = byId('p4u-case-feedback');
                feedback.textContent = option.dataset.feedback;
                feedback.classList.toggle('good', isCorrect);
                feedback.classList.toggle('bad', !isCorrect);
                progress.completeGatedSection('casestudy');
            });
        });
    }

    function initPracticeChoice() {
        document.querySelectorAll('.p4u-practice-options button').forEach(option => {
            option.addEventListener('click', () => {
                const isCorrect = option.dataset.correct === 'true';
                document.querySelectorAll('.p4u-practice-options button').forEach(button => {
                    button.classList.remove('selected', 'correct', 'incorrect');
                });
                option.classList.add('selected', isCorrect ? 'correct' : 'incorrect');

                const feedback = byId('p4u-practice-feedback');
                feedback.textContent = option.dataset.feedback;
                feedback.classList.toggle('good', isCorrect);
                feedback.classList.toggle('bad', !isCorrect);
                byId('p4u-practice-reveal')?.classList.remove('p4u-hidden');
                progress.completeGatedSection('video');
            });
        });
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
            result.innerHTML = missing === 1
                ? '<strong>Noch nicht abschlie\u00dfbar</strong><span>Noch eine Frage ist offen. W\u00e4hle dort eine Antwort aus und schlie\u00dfe das Quiz dann ab.</span>'
                : '<strong>Noch nicht abschlie\u00dfbar</strong><span>Noch ' + missing + ' Fragen sind offen. W\u00e4hle dort Antworten aus und schlie\u00dfe das Quiz dann ab.</span>';
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
            result.innerHTML = '<strong>Bestanden: ' + percent + ' %</strong><span>Modul 1 ist abgeschlossen. Du siehst unten eine kurze Zusammenfassung und kannst bei Bedarf trotzdem einen neuen Versuch starten.</span>';
            result.classList.add('good');
            byId('p4u-completion').classList.remove('p4u-hidden');
            byId('p4u-quiz-state').textContent = 'Bestanden';
            markModuleCompleted();
        } else {
            result.innerHTML = '<strong>Noch nicht bestanden: ' + percent + ' %</strong><span>Du brauchst mindestens 60 %. Schau dir die Hinweise bei den Fragen an und starte danach einen neuen Versuch.</span>';
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
        showMessage('Neuer Quiz-Versuch gestartet.');
    }

    function restoreChoiceButton(selector, feedbackText, feedbackId, revealId) {
        if (!feedbackText) {
            return;
        }
        document.querySelectorAll(selector + ' button').forEach(button => {
            if (button.dataset.feedback !== feedbackText) {
                return;
            }
            const isCorrect = button.dataset.correct === 'true';
            button.classList.add('selected', isCorrect ? 'correct' : 'incorrect');
            const feedback = byId(feedbackId);
            if (feedback) {
                feedback.textContent = feedbackText;
                feedback.classList.toggle('good', isCorrect);
                feedback.classList.toggle('bad', !isCorrect);
            }
            if (revealId) {
                byId(revealId)?.classList.remove('p4u-hidden');
            }
        });
    }

    function collectModuleExtra() {
        const dragPlacements = {};
        document.querySelectorAll('.p4u-draggable').forEach(card => {
            const zone = card.closest('.p4u-dropzone');
            dragPlacements[card.dataset.id] = zone ? zone.dataset.accept : 'bank';
        });

        const practiceBtn = document.querySelector('.p4u-practice-options button.selected');
        const caseBtn = document.querySelector('.p4u-case-options button.selected');
        const quizSelections = {};
        document.querySelectorAll('.p4u-quiz').forEach((group, index) => {
            const selected = Array.from(group.querySelectorAll('button.selected')).map(button => button.textContent.trim());
            if (selected.length) {
                quizSelections[index] = selected;
            }
        });

        return {
            flashIndex,
            dragPlacements,
            dragNextEnabled: Boolean(byId('p4u-drag-next') && !byId('p4u-drag-next').disabled),
            practiceFeedback: practiceBtn ? practiceBtn.dataset.feedback : null,
            caseFeedback: caseBtn ? caseBtn.dataset.feedback : null,
            quizAttempt,
            quizSubmitted,
            quizSelections,
        };
    }

    function restoreModuleExtra(extra) {
        if (typeof extra.flashIndex === 'number') {
            flashIndex = Math.min(Math.max(0, extra.flashIndex), flashcards.length - 1);
        }
        if (extra.dragPlacements) {
            Object.entries(extra.dragPlacements).forEach(([cardId, zoneKey]) => {
                const card = document.querySelector('.p4u-draggable[data-id="' + cardId + '"]');
                if (!card) {
                    return;
                }
                if (zoneKey === 'bank') {
                    byId('p4u-card-bank')?.appendChild(card);
                    return;
                }
                const zone = document.querySelector('.p4u-dropzone[data-accept="' + zoneKey + '"]');
                zone?.appendChild(card);
            });
        }
        if (extra.dragNextEnabled && byId('p4u-drag-next')) {
            byId('p4u-drag-next').disabled = false;
        }
        restoreChoiceButton('.p4u-practice-options', extra.practiceFeedback, 'p4u-practice-feedback', 'p4u-practice-reveal');
        restoreChoiceButton('.p4u-case-options', extra.caseFeedback, 'p4u-case-feedback');
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

        initFlashcards();
        initDragDrop();
        initRoleSelfcheck();
        initPracticeChoice();
        initCaseChoice();
        initQuiz();
        updateStatusSummary();
        loadMoodleCompletionStatus();
        progress.load();
        renderFlashcard();
        updateQuizAttemptLabel();
        showSection(progress.getCurrentSection());
    });
})();
