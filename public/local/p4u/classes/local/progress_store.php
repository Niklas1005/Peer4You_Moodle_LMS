<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Peer4You — Speichert Lernstationen-Fortschritt pro Nutzer in Moodle User Preferences.
 *
 * Wird von module_state.php (AJAX) und module-progress.js (Browser) genutzt.
 * Autoritative Quelle ist die DB; localStorage dient nur als Offline-Cache.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_p4u\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Reads and writes per-user module progress in Moodle user preferences.
 */
class progress_store {
    /**
     * Returns the Moodle user preference key for module progress.
     *
     * @param int $module Module number 1-6.
     * @return string
     */
    public static function progress_preference_name(int $module): string {
        return 'local_p4u_module' . $module . '_progress';
    }

    /**
     * Returns the Moodle user preference key for quiz attempt statistics.
     *
     * @param int $module Module number 1-6.
     * @return string
     */
    public static function quiz_stats_preference_name(int $module): string {
        return 'local_p4u_module' . $module . '_quiz_stats';
    }

    /**
     * Loads the progress payload for a user and module from Moodle user preferences.
     *
     * @param int $module Module number 1-6.
     * @param int $userid
     * @return array|null Decoded progress payload, or null if none stored yet.
     */
    public static function get_progress(int $module, int $userid): ?array {
        $raw = get_user_preferences(self::progress_preference_name($module), null, $userid);
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Loads quiz attempt statistics for a user and module.
     *
     * @param int $module Module number 1-6.
     * @param int $userid
     * @return array{attempts:int,passed:int} Always returns a valid struct; defaults to zeros.
     */
    public static function get_quiz_stats(int $module, int $userid): array {
        $raw = get_user_preferences(self::quiz_stats_preference_name($module), null, $userid);
        if ($raw === null || $raw === '') {
            return ['attempts' => 0, 'passed' => 0];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['attempts' => 0, 'passed' => 0];
        }

        return [
            'attempts' => (int)($decoded['attempts'] ?? 0),
            'passed' => (int)($decoded['passed'] ?? 0),
        ];
    }

    /**
     * Persists a module progress payload to Moodle user preferences as JSON.
     *
     * @param int   $module   Module number 1-6.
     * @param int   $userid
     * @param array $progress Progress payload (visited, currentSection, extra).
     */
    public static function save_progress(int $module, int $userid, array $progress): void {
        set_user_preference(
            self::progress_preference_name($module),
            json_encode($progress, JSON_UNESCAPED_UNICODE),
            $userid
        );
    }

    /**
     * Persists quiz attempt statistics to Moodle user preferences as JSON.
     *
     * Only `attempts` and `passed` are stored; extra keys in $stats are discarded.
     *
     * @param int   $module Module number 1-6.
     * @param int   $userid
     * @param array $stats  Must contain at minimum `attempts` and `passed` integer keys.
     */
    public static function save_quiz_stats(int $module, int $userid, array $stats): void {
        $payload = [
            'attempts' => (int)($stats['attempts'] ?? 0),
            'passed' => (int)($stats['passed'] ?? 0),
        ];
        set_user_preference(
            self::quiz_stats_preference_name($module),
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            $userid
        );
    }

    /**
     * Finds the URL course-module (cm_info) that links to the interactive module page.
     *
     * Matches by URL path (/local/p4u/moduleN.php) or by the legacy display name for
     * module 1 ("Interaktives Lernmodul starten"). Returns null if not found.
     *
     * @param \stdClass $course
     * @param int       $module Module number 1-6.
     * @return \cm_info|null
     */
    public static function find_module_cm(\stdClass $course, int $module): ?\cm_info {
        global $DB;

        $modinfo = get_fast_modinfo($course);
        $modulepath = '/local/p4u/module' . $module . '.php';

        foreach ($modinfo->cms as $cm) {
            if ($cm->modname !== 'url') {
                continue;
            }

            $urlrecord = $DB->get_record('url', ['id' => $cm->instance], 'id, name, externalurl');
            if (!$urlrecord) {
                continue;
            }

            $name = trim($urlrecord->name);
            $matchesname = $module === 1 && $name === 'Interaktives Lernmodul starten';
            $matchesurl = strpos($urlrecord->externalurl, $modulepath) !== false;

            if ($matchesname || $matchesurl) {
                return $cm;
            }
        }

        return null;
    }

    /**
     * Checks whether a user has completed the Moodle activity linked to an interactive module.
     *
     * Uses Moodle's completion API; returns false if completion is not enabled for the activity.
     *
     * @param \stdClass $course
     * @param int       $module Module number 1-6.
     * @param int       $userid
     * @return bool
     */
    public static function is_module_completed(\stdClass $course, int $module, int $userid): bool {
        require_once($GLOBALS['CFG']->libdir . '/completionlib.php');

        $target = self::find_module_cm($course, $module);
        if (!$target) {
            return false;
        }

        $completion = new \completion_info($course);
        if (!$completion->is_enabled($target)) {
            return false;
        }

        $data = $completion->get_data($target, false, $userid);
        return in_array((int)$data->completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS], true);
    }
}
