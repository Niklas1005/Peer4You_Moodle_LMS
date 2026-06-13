<?php
// This file is part of Moodle - http://moodle.org/

namespace local_p4u\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;

/**
 * Privacy provider for local_p4u.
 *
 * @package   local_p4u
 * @copyright 2026
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadata_provider {
    /**
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        for ($module = 1; $module <= 6; $module++) {
            $collection->add_user_preference(
                'local_p4u_module' . $module . '_progress',
                'privacy:metadata:progress'
            );
            $collection->add_user_preference(
                'local_p4u_module' . $module . '_quiz_stats',
                'privacy:metadata:quizstats'
            );
        }

        return $collection;
    }
}
