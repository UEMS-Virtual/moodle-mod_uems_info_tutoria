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
 * Restore steps for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @category   backup
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one uemsinfotutoria activity instance.
 */
class restore_uemsinfotutoria_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the paths to restore.
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        $paths = [];
        $paths[] = new restore_path_element('uemsinfotutoria', '/activity/uemsinfotutoria');

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the activity instance record.
     *
     * @param array $data Restored data.
     */
    protected function process_uemsinfotutoria($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        $newitemid = $DB->insert_record('uemsinfotutoria', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore related files.
     */
    protected function after_execute() {
        $this->add_related_files('mod_uemsinfotutoria', 'intro', null);
    }
}
