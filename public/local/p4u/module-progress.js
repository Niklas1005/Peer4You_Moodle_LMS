/**
 * Peer4You — Gemeinsamer Fortschritts-Store für alle interaktiven Module.
 *
 * P4UProgress.create() verwaltet besuchte Stationen, aktuelle Sektion und Quiz-Stats.
 * Speichert serverseitig über module_state.php; localStorage nur als Fallback-Cache.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
(function(global) {
    'use strict';

    const MODULE_ACTIVITY_LABELS = {
        1: 'Interaktives Lernmodul starten',
        2: 'Interaktives Lernmodul Finanzwissen starten',
        3: 'Interaktives Lernmodul Kommunikation starten',
        4: 'Interaktives Lernmodul Digitale Sicherheit starten',
        5: 'Interaktives Lernmodul Krisen erkennen starten',
    };

    let saveTimer = null;

    /**
     * @param {object} config
     * @param {number} config.moduleNumber
     * @param {string[]} config.sections
     * @param {string[]} [config.gatedSections]
     * @param {function} [config.onRestore]
     * @param {function} [config.collectExtra]
     * @param {function} [config.getQuizStats]
     */
    function create(config) {
        const {
            moduleNumber,
            sections,
            gatedSections = [],
            onRestore = null,
            collectExtra = null,
            getQuizStats = null,
        } = config;

        const shell = document.querySelector('.p4u-shell');
        const userId = shell?.dataset.userId || '0';
        const progressUrl = shell?.dataset.progressUrl || '';
        const progressKey = 'p4u-u' + userId + '-module' + moduleNumber + '-progress';
        const visited = new Set(['intro']);
        const gated = new Set(gatedSections);
        let currentSection = 'intro';

        function buildProgressPayload() {
            return {
                visited: Array.from(visited),
                currentSection,
                extra: collectExtra ? collectExtra() : {},
            };
        }

        function applyProgressPayload(data) {
            if (!data || typeof data !== 'object') {
                return false;
            }

            if (Array.isArray(data.visited)) {
                visited.clear();
                data.visited.forEach(id => {
                    if (sections.includes(id)) {
                        visited.add(id);
                    }
                });
                if (!visited.has('intro')) {
                    visited.add('intro');
                }
            }

            if (data.currentSection && sections.includes(data.currentSection)) {
                currentSection = data.currentSection;
            }

            if (onRestore && data.extra) {
                onRestore(data.extra);
            }

            return true;
        }

        function load() {
            try {
                const raw = localStorage.getItem(progressKey);
                if (!raw) {
                    return false;
                }
                return applyProgressPayload(JSON.parse(raw));
            } catch (error) {
                return false;
            }
        }

        function persistLocal() {
            localStorage.setItem(progressKey, JSON.stringify(buildProgressPayload()));
        }

        function persistRemote() {
            if (!progressUrl) {
                return;
            }

            if (saveTimer) {
                clearTimeout(saveTimer);
            }

            saveTimer = setTimeout(() => {
                const body = {
                    progress: buildProgressPayload(),
                };

                if (typeof getQuizStats === 'function') {
                    body.quizStats = getQuizStats();
                }

                fetch(progressUrl + '&action=save', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(body),
                }).catch(() => {
                    // Local cache remains available if the server is temporarily unreachable.
                });
            }, 400);
        }

        function persist() {
            persistLocal();
            persistRemote();
        }

        async function loadRemote() {
            if (!progressUrl) {
                return null;
            }

            try {
                const response = await fetch(progressUrl + '&action=get', {
                    method: 'GET',
                    credentials: 'same-origin',
                });
                const data = await response.json();
                if (!data || !data.ok) {
                    return null;
                }

                if (data.progress) {
                    applyProgressPayload(data.progress);
                    persistLocal();
                }

                return data;
            } catch (error) {
                return null;
            }
        }

        function isGatedSection(id) {
            return gated.has(id);
        }

        function canAccessSection(id) {
            const index = sections.indexOf(id);
            if (index < 0) {
                return false;
            }
            if (visited.has(id)) {
                return true;
            }
            if (index === 0) {
                return true;
            }
            return visited.has(sections[index - 1]);
        }

        function markVisited(id) {
            if (!sections.includes(id)) {
                return;
            }
            visited.add(id);
            persist();
        }

        function completeGatedSection(id) {
            markVisited(id);
        }

        function setCurrentSection(id) {
            if (!sections.includes(id)) {
                return;
            }
            currentSection = id;
            persist();
        }

        function recordSectionEnter(id) {
            setCurrentSection(id);
            if (!isGatedSection(id)) {
                if (!visited.has(id)) {
                    visited.add(id);
                }
                persist();
            } else {
                persist();
            }
        }

        function updateNav(activeId) {
            document.querySelectorAll('.p4u-nav button[data-target]').forEach(button => {
                const target = button.dataset.target;
                button.classList.toggle('active', target === activeId);
                button.classList.toggle('visited', visited.has(target));
                button.classList.toggle('nav-locked', !canAccessSection(target));
            });
        }

        function getCurrentSection() {
            return currentSection;
        }

        function getVisited() {
            return visited;
        }

        return {
            load,
            loadRemote,
            persist,
            isGatedSection,
            canAccessSection,
            markVisited,
            completeGatedSection,
            recordSectionEnter,
            updateNav,
            getCurrentSection,
            getVisited,
        };
    }

    function clearCourseIndexBadges() {
        document.querySelectorAll('.p4u-index-status').forEach(badge => badge.remove());
    }

    function addCourseIndexBadge(link, label, state) {
        const item = link.closest('li, .courseindex-item, .courseindex-link, .activity-item, div') || link;
        item.querySelectorAll('.p4u-index-status').forEach(existing => existing.remove());

        const badge = document.createElement('span');
        badge.className = 'p4u-index-status is-' + state;
        badge.textContent = label;
        link.insertAdjacentElement('afterend', badge);
    }

    /**
     * Show a single custom badge only when the module is completed.
     *
     * @param {number} moduleNumber
     * @param {boolean} completed
     */
    function updateCourseIndexStatus(moduleNumber, completed) {
        const needle = MODULE_ACTIVITY_LABELS[moduleNumber];
        if (!needle) {
            return;
        }

        document.querySelectorAll('#courseindex a, .courseindex a, [data-region="courseindex"] a').forEach(link => {
            const label = (link.textContent || '').trim();
            const item = link.closest('li, .courseindex-item, .courseindex-link, .activity-item, div') || link;
            item.querySelectorAll('.p4u-index-status').forEach(existing => existing.remove());

            if (!label.includes(needle)) {
                return;
            }

            if (completed) {
                addCourseIndexBadge(link, 'Abgeschlossen', 'done');
            }
        });
    }

    global.P4UProgress = {
        create,
        updateCourseIndexStatus,
        clearCourseIndexBadges,
    };
})(window);
