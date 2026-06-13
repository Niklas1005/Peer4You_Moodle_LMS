/**
 * Shared station progress persistence for P4U interactive modules.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
(function(global) {
    'use strict';

    /**
     * @param {object} config
     * @param {number} config.moduleNumber
     * @param {string[]} config.sections
     * @param {string[]} [config.gatedSections]
     * @param {function} [config.onRestore]
     * @param {function} [config.collectExtra]
     */
    function create(config) {
        const {
            moduleNumber,
            sections,
            gatedSections = [],
            onRestore = null,
            collectExtra = null,
        } = config;

        const progressKey = 'p4u-module' + moduleNumber + '-progress';
        const visited = new Set(['intro']);
        const gated = new Set(gatedSections);
        let currentSection = 'intro';

        function load() {
            try {
                const raw = localStorage.getItem(progressKey);
                if (!raw) {
                    return false;
                }

                const data = JSON.parse(raw);
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
            } catch (error) {
                return false;
            }
        }

        function persist() {
            const payload = {
                visited: Array.from(visited),
                currentSection,
                extra: collectExtra ? collectExtra() : {},
            };
            localStorage.setItem(progressKey, JSON.stringify(payload));
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

    global.P4UProgress = {create};
})(window);
