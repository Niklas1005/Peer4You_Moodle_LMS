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
 * Theme helper functions for theme_p4u.
 *
 * @package   theme_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Login card logo URL — theme asset first, then Moodle admin logos.
 *
 * @param renderer_base $output Page renderer.
 * @return string
 */
function theme_p4u_get_login_logo_url(renderer_base $output): string {
    return $output->image_url('loginlogo', 'theme_p4u')->out(false);
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme Theme configuration object.
 * @return string
 */
function theme_p4u_get_main_scss_content($theme): string {
    global $CFG;

    $scss = '';
    $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $scss .= "\n";
    $scss .= file_get_contents($CFG->dirroot . '/theme/p4u/scss/p4u.scss');
    $scss .= "\n";
    $scss .= file_get_contents($CFG->dirroot . '/theme/p4u/scss/login.scss');

    return $scss;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_p4u_get_extra_scss($theme): string {
    return '';
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme Theme configuration object.
 * @return string
 */
function theme_p4u_get_pre_scss($theme): string {
    $scss = '';

    if (defined('BEHAT_SITE_RUNNING')) {
        $scss .= "\$behatsite: true;\n";
    }

    // Thinner Bootstrap focus rings site-wide.
    $scss .= "\$focus-ring-width: 0.125rem;\n";
    $scss .= "\$focus-ring-opacity: 0.2;\n";

    return $scss;
}

/**
 * Get compiled css.
 *
 * @return string
 */
function theme_p4u_get_precompiled_css(): string {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/boost/style/moodle.css');
}

/**
 * Theme user preferences.
 *
 * @return array[]
 */
function theme_p4u_user_preferences(): array {
    return [
        'drawer-open-block' => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => false,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
        'drawer-open-index' => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => true,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
    ];
}
