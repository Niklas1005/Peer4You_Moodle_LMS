(function() {
    const sections = ['intro', 'everyday', 'interest', 'credit', 'budget', 'counseling', 'quiz'];
    const sectionLabels = ['Einstieg', 'Alltag', 'Zinsen', 'Kredite', 'Budgetplan', 'Beratung', 'Abschlussquiz'];
    const requiredBeforeQuiz = ['intro', 'everyday', 'interest', 'credit', 'budget', 'counseling'];
    const completionKey = 'p4u-module2-completed';
    const statsKey = 'p4u-module2-quiz-stats';
    const visited = new Set(['intro']);

    const flashcards = [
        {
            front: 'Zinsen',
            back: 'Zinsen sind das Entgelt fuer geliehenes oder angelegtes Geld. Beim Sparen koennen sie helfen, bei Krediten kosten sie Geld.'
        },
        {
            front: 'Dispozins',
            back: 'Der Dispo ist ein Kreditrahmen auf dem Girokonto. Er sollte nur kurz und in Ausnahmen genutzt werden, weil die Zinsen oft hoch sind.'
        },
        {
            front: 'Effektiver Jahreszins',
            back: 'Er zeigt die Kreditkosten inklusive wichtiger Preisbestandteile und macht Angebote besser vergleichbar.'
        },
        {
            front: 'Zinseszins',
            back: 'Zinsen werden erneut verzinst. Beim Sparen kann das helfen, bei Schulden kann es die Belastung erhoehen.'
        },
        {
            front: 'Buy Now Pay Later',
            back: 'Heute kaufen und spaeter zahlen fuehlt sich leicht an. Riskant wird es, wenn mehrere kleine Zahlungen, Mahngebuehren oder Verzugszinsen zusammenkommen.'
        }
    ];

    let flashIndex = 0;
    let dragged = null;
    let quizAttempt = 1;
    let quizSubmitted = false;
    let furthestSectionIndex = 0;

    function byId(id) {
        return document.getElementById(id);
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
            ? 'Modul 2 ist abgeschlossen. Du kannst die Inhalte trotzdem jederzeit wiederholen.'
            : 'Schlie\u00dfe das Quiz ab, um Modul 2 im Kurs als erledigt zu markieren.';
    }

    function setCompletionUi(completed) {
        document.querySelector('.p4u-shell')?.classList.toggle('is-completed', completed);
        updateStatusSummary(completed);
        if (completed) {
            byId('p4u-quiz-state').textContent = 'Bereits bestanden';
        }
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

    function initCalculator() {
        const input = byId('p4u-weekly-cost');
        if (!input) {
            return;
        }

        const render = () => {
            const weekly = Math.max(0, Number(input.value || 0));
            byId('p4u-monthly-cost').textContent = Math.round(weekly * 4) + ' EUR';
            byId('p4u-yearly-cost').textContent = Math.round(weekly * 48) + ' EUR';
        };

        input.addEventListener('input', render);
        render();
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
        });
        byId('p4u-flash-next')?.addEventListener('click', () => {
            flashIndex = Math.min(flashcards.length - 1, flashIndex + 1);
            renderFlashcard();
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

    function initChoiceCards(selector, feedbackId, revealId) {
        document.querySelectorAll(selector + ' button').forEach(option => {
            option.addEventListener('click', () => {
                const isCorrect = option.dataset.correct === 'true';
                document.querySelectorAll(selector + ' button').forEach(button => {
                    button.classList.remove('selected', 'correct', 'incorrect');
                });
                option.classList.add('selected', isCorrect ? 'correct' : 'incorrect');

                const feedback = byId(feedbackId);
                feedback.textContent = option.dataset.feedback;
                feedback.classList.toggle('good', isCorrect);
                feedback.classList.toggle('bad', !isCorrect);

                if (revealId) {
                    byId(revealId)?.classList.remove('p4u-hidden');
                }
            });
        });
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
            result.innerHTML = '<strong>Bestanden: ' + percent + ' %</strong><span>Modul 2 ist abgeschlossen. Du siehst unten eine kurze Zusammenfassung und kannst bei Bedarf trotzdem einen neuen Versuch starten.</span>';
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
        showMessage('Neuer Quiz-Versuch gestartet.');
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

        initCalculator();
        initFlashcards();
        initDragDrop();
        initChoiceCards('.p4u-practice-options', 'p4u-practice-feedback', 'p4u-practice-reveal');
        initChoiceCards('.p4u-case-options', 'p4u-case-feedback');
        initQuiz();
        updateStatusSummary();
        loadMoodleCompletionStatus();
        updateProgress(0);
        updateQuizNav();
    });
})();
