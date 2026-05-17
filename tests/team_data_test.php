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

/**
 * Tests for tutoring team data rules.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_uemsinfotutoria\local\team_data
 */
final class team_data_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * @dataProvider reoffer_marker_provider
     * @param string $shortname
     * @param bool $expected
     */
    public function test_reoffer_marker_is_detected_as_isolated_token(string $shortname, bool $expected): void {
        $this->assertSame($expected, team_data::is_reoffer_course($shortname));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function reoffer_marker_provider(): array {
        return [
            'reo in parentheses' => ['CISOL_23_2S_SA_(REO)_cb9e8', true],
            'reo between underscores' => ['ABC_REO_2026', true],
            'reo2 with hyphens and parentheses' => ['ABC-(REO2)-2026', true],
            'reo2 between underscores' => ['ABC_REO2_x', true],
            'reo inside another word' => ['TEOREOLOGIA_2026', false],
            'reo inside prefix' => ['PREOFERTA_2026', false],
            'reo inside suffix' => ['COREO_ABC', false],
        ];
    }

    public function test_expectation_modes_resolve_from_instance_and_course_shortname(): void {
        $regularcourse = (object) ['shortname' => 'ABC_2026'];
        $reoffercourse = (object) ['shortname' => 'ABC_(REO)_2026'];

        $auto = (object) ['expecttutor' => team_data::EXPECT_AUTO, 'expectmediator' => team_data::EXPECT_AUTO];
        $yes = (object) ['expecttutor' => team_data::EXPECT_YES, 'expectmediator' => team_data::EXPECT_YES];
        $no = (object) ['expecttutor' => team_data::EXPECT_NO, 'expectmediator' => team_data::EXPECT_NO];

        $this->assertTrue(team_data::expects_tutors($auto, $regularcourse));
        $this->assertTrue(team_data::expects_tutors($auto, $reoffercourse));
        $this->assertTrue(team_data::expects_mediators($auto, $regularcourse));
        $this->assertFalse(team_data::expects_mediators($auto, $reoffercourse));

        $this->assertTrue(team_data::expects_tutors($yes, $reoffercourse));
        $this->assertTrue(team_data::expects_mediators($yes, $reoffercourse));
        $this->assertFalse(team_data::expects_tutors($no, $regularcourse));
        $this->assertFalse(team_data::expects_mediators($no, $regularcourse));
    }

    public function test_get_polo_groups_returns_only_course_groups_named_as_polos(): void {
        $course = $this->getDataGenerator()->create_course();
        $othercourse = $this->getDataGenerator()->create_course();

        $polo = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Polo Bataguassu']);
        $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Seminário']);
        $this->getDataGenerator()->create_group(['courseid' => $othercourse->id, 'name' => 'Polo Outro Curso']);

        $groups = team_data::get_polo_groups($course->id);

        $this->assertArrayHasKey($polo->id, $groups);
        $this->assertCount(1, $groups);
        $this->assertSame('Polo Bataguassu', $groups[$polo->id]->name);
    }

    public function test_get_team_returns_active_role_users_with_their_polos(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $module = $generator->create_module('uemsinfotutoria', ['course' => $course->id]);
        $context = \context_module::instance($module->cmid);

        $tutorroleid = $this->ensure_role(team_data::ROLE_TUTOR, 'Tutor Presencial');
        $mediatorroleid = $this->ensure_role(team_data::ROLE_MEDIATOR, 'Mediador Pedagógico');

        $tutor = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Tutora']);
        $mediator = $generator->create_user(['firstname' => 'Maria', 'lastname' => 'Mediadora']);
        $student = $generator->create_user(['firstname' => 'João', 'lastname' => 'Aluno']);
        $suspendedtutor = $generator->create_user(['firstname' => 'Carlos', 'lastname' => 'Suspenso']);

        $generator->enrol_user($tutor->id, $course->id, $tutorroleid);
        $generator->enrol_user($mediator->id, $course->id, $mediatorroleid);
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($suspendedtutor->id, $course->id, $tutorroleid, 'manual', 0, 0, ENROL_USER_SUSPENDED);

        $polo = $generator->create_group(['courseid' => $course->id, 'name' => 'Polo Bataguassu']);
        $notpolo = $generator->create_group(['courseid' => $course->id, 'name' => 'Grupo de estudo']);
        groups_add_member($polo, $tutor);
        groups_add_member($polo, $mediator);
        groups_add_member($notpolo, $student);

        $team = team_data::get_team($course->id, $context);

        $this->assertCount(1, $team['tutors']);
        $this->assertCount(1, $team['mediators']);
        $this->assertSame($tutor->id, $team['tutors'][0]['user']->id);
        $this->assertSame(['Polo Bataguassu'], $team['tutors'][0]['polos']);
        $this->assertSame($mediator->id, $team['mediators'][0]['user']->id);
        $this->assertSame(['Polo Bataguassu'], $team['mediators'][0]['polos']);
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
