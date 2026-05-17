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

use mod_uemsinfotutoria\local\team_data;
use mod_uemsinfotutoria\output\tutoria_page;

/**
 * Tests for template export rules.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_uemsinfotutoria\output\tutoria_page
 */
final class output_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_student_without_polo_does_not_receive_full_team_as_my_polo(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'expecttutor' => team_data::EXPECT_YES,
            'expectmediator' => team_data::EXPECT_NO,
        ]);
        $cm = get_coursemodule_from_instance('uemsinfotutoria', $module->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);

        $tutorroleid = $this->ensure_role(team_data::ROLE_TUTOR, 'Tutor Presencial');
        $student = $generator->create_and_enrol($course, 'student');
        $tutor = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Tutora']);
        $generator->enrol_user($tutor->id, $course->id, $tutorroleid);
        $polo = $generator->create_group(['courseid' => $course->id, 'name' => 'Polo Bataguassu']);
        groups_add_member($polo, $tutor);

        $this->setUser($student);
        $renderable = new tutoria_page($module, $cm, $course, $context, $student->id);
        $data = $renderable->export_for_template($PAGE->get_renderer('core'));

        $this->assertTrue($data['isstudent']);
        $this->assertFalse($data['has_polo']);
        $this->assertFalse($data['mine_has_tutors']);
        $this->assertCount(0, $data['mine_tutors']);
        $this->assertTrue($data['all_has_tutors']);
        $this->assertCount(1, $data['all_tutors']);
    }

    public function test_non_expected_functions_are_hidden_and_manager_gets_notice_when_none_expected(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'expecttutor' => team_data::EXPECT_NO,
            'expectmediator' => team_data::EXPECT_NO,
        ]);
        $cm = get_coursemodule_from_instance('uemsinfotutoria', $module->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);

        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);
        $studentdata = (new tutoria_page($module, $cm, $course, $context, $student->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertFalse($studentdata['hascontent']);
        $this->assertFalse($studentdata['shownotice']);

        $editingteacher = $generator->create_and_enrol($course, 'editingteacher');
        $this->setUser($editingteacher);
        $teacherdata = (new tutoria_page($module, $cm, $course, $context, $editingteacher->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertFalse($teacherdata['hascontent']);
        $this->assertTrue($teacherdata['shownotice']);
        $this->assertSame(get_string('nofunctionsexpected', 'uemsinfotutoria'), $teacherdata['nofunctionsexpected']);
    }

    /**
     * Ensure a course-level role exists for the test.
     *
     * @param string $shortname
     * @param string $fullname
     * @return int
     */
    private function ensure_role(string $shortname, string $fullname): int {
        global $DB;

        if ($role = $DB->get_record('role', ['shortname' => $shortname])) {
            return (int) $role->id;
        }

        $roleid = create_role($fullname, $shortname, '', 'teacher');
        set_role_contextlevels($roleid, [CONTEXT_COURSE]);
        return $roleid;
    }
}
