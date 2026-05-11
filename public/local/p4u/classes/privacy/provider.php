<?php
// This file is part of Moodle - http://moodle.org/

namespace local_p4u\privacy;

use core_privacy\local\metadata\null_provider;

/**
 * Privacy provider for local_p4u.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements null_provider {
    /**
     * Explain that this plugin stores no personal data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
