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
 * Upgrade steps for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_uemsinfotutoria upgrade steps.
 *
 * @param int $oldversion Installed plugin version.
 * @return bool
 */
function xmldb_uemsinfotutoria_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026051602) {
        $table = new xmldb_table('uemsinfotutoria');

        $field = new xmldb_field('supporttitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'introformat');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('expecttutor', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'supporttitle');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('expectmediator', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'expecttutor');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026051602, 'uemsinfotutoria');
    }

    if ($oldversion < 2026051603) {
        $table = new xmldb_table('uemsinfotutoria');
        $field = new xmldb_field('supporttitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'introformat');

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
            $dbman->change_field_default($table, $field);
        }

        upgrade_mod_savepoint(true, 2026051603, 'uemsinfotutoria');
    }

    return true;
}
