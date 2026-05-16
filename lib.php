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
 * Library callbacks for mod_uems_info_tutoria.
 *
 * @package    mod_uems_info_tutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * List supported features.
 *
 * @param string $feature Feature name.
 * @return mixed True if supported, null otherwise.
 */
function uems_info_tutoria_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Add a new instance of the activity.
 *
 * @param stdClass $data Form data.
 * @param mod_uems_info_tutoria_mod_form|null $mform Form instance.
 * @return int New instance id.
 */
function uems_info_tutoria_add_instance(stdClass $data, ?mod_uems_info_tutoria_mod_form $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    return $DB->insert_record('uems_info_tutoria', $data);
}

/**
 * Update an existing instance of the activity.
 *
 * @param stdClass $data Form data.
 * @param mod_uems_info_tutoria_mod_form|null $mform Form instance.
 * @return bool
 */
function uems_info_tutoria_update_instance(stdClass $data, ?mod_uems_info_tutoria_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    return $DB->update_record('uems_info_tutoria', $data);
}

/**
 * Delete an instance of the activity.
 *
 * @param int $id Instance id.
 * @return bool
 */
function uems_info_tutoria_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists('uems_info_tutoria', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('uems_info_tutoria', ['id' => $id]);
    return true;
}
