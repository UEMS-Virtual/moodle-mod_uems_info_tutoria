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
 * Test data generator for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @category   test
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Tutoring information module data generator.
 *
 * @package    mod_uemsinfotutoria
 * @category   test
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_uemsinfotutoria_generator extends testing_module_generator {

    /**
     * Create an activity instance for tests.
     *
     * @param array|stdClass|null $record
     * @param array|null $options
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null): stdClass {
        $record = (object) (array) $record;

        if (!isset($record->name)) {
            $record->name = 'Equipe de Tutoria e Mediação';
        }
        if (!isset($record->intro)) {
            $record->intro = '';
        }
        if (!isset($record->introformat)) {
            $record->introformat = FORMAT_HTML;
        }
        if (!isset($record->supporttitle)) {
            $record->supporttitle = 'Sua Equipe de Tutoria e Mediação';
        }
        if (!isset($record->expecttutor)) {
            $record->expecttutor = 0;
        }
        if (!isset($record->expectmediator)) {
            $record->expectmediator = 0;
        }

        return parent::create_instance($record, (array) $options);
    }
}
