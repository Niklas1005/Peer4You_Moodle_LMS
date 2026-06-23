<?php
// This file is part of Moodle - http://moodle.org/

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
   * @param int $module Module number 1-6
   * @return string
   */
    public static function progress_preference_name(int $module): string {
        return 'local_p4u_module' . $module . '_progress';
    }

  /**
   * @param int $module Module number 1-6
   * @return string
   */
    public static function quiz_stats_preference_name(int $module): string {
        return 'local_p4u_module' . $module . '_quiz_stats';
    }

  /**
   * @param int $module
   * @param int $userid
   * @return array|null Decoded progress payload or null
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
   * @param int $module
   * @param int $userid
   * @return array{attempts:int,passed:int}
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
   * @param int $module
   * @param int $userid
   * @param array $progress
   */
    public static function save_progress(int $module, int $userid, array $progress): void {
        set_user_preference(
            self::progress_preference_name($module),
            json_encode($progress, JSON_UNESCAPED_UNICODE),
            $userid
        );
    }

  /**
   * @param int $module
   * @param int $userid
   * @param array $stats
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
   * Find the URL activity cm for an interactive module.
   *
   * @param \stdClass $course
   * @param int $module
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
   * @param \stdClass $course
   * @param int $module
   * @param int $userid
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
