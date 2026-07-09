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

        $this->assertTrue($data['showmypolo']);
        $this->assertFalse($data['has_polo']);
        $this->assertFalse($data['mine_has_tutors']);
        $this->assertCount(0, $data['mine_tutors']);
        $this->assertTrue($data['all_has_tutors']);
        $this->assertCount(1, $data['all_tutors']);
    }

    public function test_my_polo_view_is_limited_to_students_and_on_site_tutors(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'expecttutor' => team_data::EXPECT_YES,
            'expectmediator' => team_data::EXPECT_YES,
        ]);
        $cm = get_coursemodule_from_instance('uemsinfotutoria', $module->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);

        $tutorroleid = $this->ensure_role(team_data::ROLE_TUTOR, 'Tutor Presencial');
        $mediatorroleid = $this->ensure_role(team_data::ROLE_MEDIATOR, 'Mediador Pedagógico');

        $student = $generator->create_and_enrol($course, 'student');
        $tutor = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Tutora']);
        $mediator = $generator->create_user(['firstname' => 'Maria', 'lastname' => 'Mediadora']);
        $teacher = $generator->create_and_enrol($course, 'teacher');

        $generator->enrol_user($tutor->id, $course->id, $tutorroleid);
        $generator->enrol_user($mediator->id, $course->id, $mediatorroleid);

        $cases = [
            'student' => [$student, true],
            'on-site tutor' => [$tutor, true],
            'pedagogical mediator' => [$mediator, false],
            'teacher' => [$teacher, false],
        ];

        foreach ($cases as $label => [$user, $expected]) {
            $this->setUser($user);
            $data = (new tutoria_page($module, $cm, $course, $context, $user->id))
                ->export_for_template($PAGE->get_renderer('core'));

            $this->assertSame($expected, $data['showmypolo'], $label);
        }
    }

    public function test_student_panel_uses_new_default_title_and_hides_empty_full_intro(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'intro' => '',
            'introformat' => FORMAT_HTML,
            'supporttitle' => '',
            'expecttutor' => team_data::EXPECT_YES,
            'expectmediator' => team_data::EXPECT_YES,
        ]);
        $module->intro = '';
        $cm = get_coursemodule_from_instance('uemsinfotutoria', $module->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);

        $student = $generator->create_and_enrol($course, 'student');
        $this->setUser($student);

        $data = (new tutoria_page($module, $cm, $course, $context, $student->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertSame(get_string('seuponto', 'uemsinfotutoria'), $data['supporttitle']);
        $this->assertSame('', $data['full_intro']);
        $this->assertFalse($data['has_full_intro']);
        $this->assertSame(get_string('mediadorespedagogicos', 'uemsinfotutoria'), $data['mine_mediator_label']);
        $this->assertSame(get_string('tutorespresenciais', 'uemsinfotutoria'), $data['mine_tutor_label']);
    }

    public function test_full_intro_is_shown_when_configured(): void {
        global $PAGE;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', [
            'course' => $course->id,
            'intro' => 'Subtítulo opcional da lista completa',
            'introformat' => FORMAT_HTML,
            'expecttutor' => team_data::EXPECT_YES,
            'expectmediator' => team_data::EXPECT_NO,
        ]);
        $cm = get_coursemodule_from_instance('uemsinfotutoria', $module->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $PAGE->set_context($context);

        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $data = (new tutoria_page($module, $cm, $course, $context, $teacher->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertTrue($data['has_full_intro']);
        $this->assertStringContainsString('Subtítulo opcional da lista completa', $data['full_intro']);
    }

    public function test_polo_names_are_normalized_for_display_without_affecting_filtering(): void {
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
        $teacher = $generator->create_and_enrol($course, 'teacher');
        $tutor = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Tutora']);
        $generator->enrol_user($tutor->id, $course->id, $tutorroleid);

        $rawnames = [
            'POLO UAB DE BATAGUASSU',
            'POLO UAB DE CAMPO GRANDE',
            'POLO ASSOCIADO DE NOVA ANDRADINA',
            'POLO DE RIO BRILHANTE',
        ];
        foreach ($rawnames as $rawname) {
            $group = $generator->create_group(['courseid' => $course->id, 'name' => $rawname]);
            groups_add_member($group, $tutor);
            if ($rawname === 'POLO UAB DE CAMPO GRANDE') {
                groups_add_member($group, $student);
            }
        }

        $this->setUser($student);
        $studentdata = (new tutoria_page($module, $cm, $course, $context, $student->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $this->assertSame('Campo Grande', $studentdata['polo_name']);
        $this->assertTrue($studentdata['mine_has_tutors']);

        $this->setUser($teacher);
        $teacherdata = (new tutoria_page($module, $cm, $course, $context, $teacher->id))
            ->export_for_template($PAGE->get_renderer('core'));

        $polonames = array_column($teacherdata['all_tutors'][0]['polos_items'], 'name');
        sort($polonames);
        $this->assertSame(['Bataguassu', 'Campo Grande', 'Nova Andradina', 'Rio Brilhante'], $polonames);
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
