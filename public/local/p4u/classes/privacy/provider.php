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
     * Describes the user data stored by this plugin: per-module progress and quiz stats
     * are saved as Moodle user preferences, one preference per module per data type.
     *
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
