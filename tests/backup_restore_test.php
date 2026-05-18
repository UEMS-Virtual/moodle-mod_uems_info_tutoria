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

namespace mod_uemsinfotutoria;

use backup;
use backup_controller;
use backup_setting;
use restore_controller;
use restore_dbops;

/**
 * Tests for backup and restore support.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class backup_restore_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    public function test_course_backup_and_restore_keeps_activity_settings(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();

        $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'name' => 'Equipe da tutoria',
            'intro' => 'Texto de introdução da atividade',
            'introformat' => FORMAT_HTML,
            'supporttitle' => 'Atendimento do estudante',
            'expecttutor' => 1,
            'expectmediator' => 2,
        ]);

        $newcourseid = $this->backup_and_restore_course($course);

        $records = $DB->get_records('uemsinfotutoria', ['course' => $newcourseid]);
        $this->assertCount(1, $records);

        $restored = reset($records);
        $this->assertSame('Equipe da tutoria', $restored->name);
        $this->assertSame('Texto de introdução da atividade', $restored->intro);
        $this->assertSame((int) FORMAT_HTML, (int) $restored->introformat);
        $this->assertSame('Atendimento do estudante', $restored->supporttitle);
        $this->assertSame(1, (int) $restored->expecttutor);
        $this->assertSame(2, (int) $restored->expectmediator);
    }

    /**
     * Backup a course and restore it into a new course.
     *
     * @param \stdClass $course Source course.
     * @return int Restored course id.
     */
    private function backup_and_restore_course(\stdClass $course): int {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $CFG->backup_file_logger_level = backup::LOG_NONE;

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value(false);

        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        $newcourseid = restore_dbops::create_new_course(
            $course->fullname,
            $course->shortname . '_restored',
            $course->category
        );

        $rc = new restore_controller(
            $backupid,
            $newcourseid,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $USER->id,
            backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_status(backup_setting::NOT_LOCKED);
        $rc->get_plan()->get_setting('users')->set_value(false);

        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}
