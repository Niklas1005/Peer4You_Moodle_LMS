<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Output callbacks for local_p4u.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Checks whether the current page is the Moodle dashboard.
 *
 * @return bool
 */
function local_p4u_is_dashboard_page(): bool {
    global $PAGE;

    if (empty($PAGE) || empty($PAGE->pagetype)) {
        return false;
    }

    return $PAGE->pagetype === 'my-index';
}

/**
 * Checks whether the current page is the P4U training course view.
 *
 * @return bool
 */
function local_p4u_is_training_course_page(): bool {
    global $PAGE;

    if (empty($PAGE) || empty($PAGE->pagetype) || empty($PAGE->course)) {
        return false;
    }

    return $PAGE->pagetype === 'course-view-topics'
        && (int)$PAGE->course->id === 2;
}

/**
 * Adds dashboard-specific CSS.
 *
 * @return string
 */
function local_p4u_before_standard_html_head(): string {
    global $CFG;

    $html = '';
    if (local_p4u_is_dashboard_page()) {
        $cssurl = $CFG->wwwroot . '/local/p4u/dashboard.css';
        $html .= '<link rel="stylesheet" href="' . s($cssurl) . '">';
    }
    if (local_p4u_is_training_course_page()) {
        $cssurl = $CFG->wwwroot . '/local/p4u/course.css';
        $html .= '<link rel="stylesheet" href="' . s($cssurl) . '">';
    }
    return $html;
}

/**
 * Adds dashboard-specific markup and behaviour.
 *
 * @return string
 */
function local_p4u_before_footer(): string {
    global $CFG;

    // Dashboard overlay JS disabled: use standard Moodle /my/ blocks (theme_p4u drawers layout).
    return '';
}
