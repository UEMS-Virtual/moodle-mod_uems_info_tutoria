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
 * Backup steps for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @category   backup
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the complete activity structure for backup.
 */
class backup_uemsinfotutoria_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure stored in uemsinfotutoria.xml.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $uemsinfotutoria = new backup_nested_element('uemsinfotutoria', ['id'], [
            'course',
            'name',
            'intro',
            'introformat',
            'supporttitle',
            'expecttutor',
            'expectmediator',
            'timecreated',
            'timemodified',
        ]);

        $uemsinfotutoria->set_source_table('uemsinfotutoria', ['id' => backup::VAR_ACTIVITYID]);
        $uemsinfotutoria->annotate_files('mod_uemsinfotutoria', 'intro', null);

        return $this->prepare_activity_structure($uemsinfotutoria);
    }
}
