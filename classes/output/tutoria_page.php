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
 * Renderable page for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_uemsinfotutoria\output;

use mod_uemsinfotutoria\local\team_data;
use stdClass;

/**
 * Page renderable for the tutoring information activity.
 */
class tutoria_page implements \renderable, \templatable {

    /** @var stdClass Activity instance. */
    private stdClass $instance;

    /** @var object Course module (cm_info or plain stdClass). */
    private object $cm;

    /** @var stdClass Course record. */
    private stdClass $course;

    /** @var \context_module Module context. */
    private \context_module $context;

    /** @var int Viewing user id. */
    private int $userid;

    /**
     * Constructor.
     *
     * @param stdClass         $instance Activity instance record.
     * @param object           $cm       Course module record (stdClass or cm_info).
     * @param stdClass         $course   Course record.
     * @param \context_module  $context  Module context.
     * @param int              $userid   Viewing user id (defaults to $USER->id).
     */
    public function __construct(
        stdClass $instance,
        object $cm,
        stdClass $course,
        \context_module $context,
        int $userid = 0
    ) {
        global $USER;
        $this->instance = $instance;
        $this->cm       = $cm;
        $this->course   = $course;
        $this->context  = $context;
        $this->userid   = $userid ?: (int) $USER->id;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output
     * @return array<string, mixed>
     */
    public function export_for_template(\renderer_base $output): array {
        $coursecontext   = \context_course::instance($this->course->id);
        $isstudent       = team_data::is_student($this->userid, $coursecontext);
        $canmanage       = team_data::can_manage_activities($this->userid, $coursecontext);
        $expectmediators = team_data::expects_mediators($this->instance, $this->course);
        $expecttutors    = team_data::expects_tutors($this->instance, $this->course);
        $hascontent      = $expectmediators || $expecttutors;

        if (!$hascontent) {
            return [
                'hascontent' => false,
                'shownotice' => $canmanage,
                'nofunctionsexpected' => get_string('nofunctionsexpected', 'uemsinfotutoria'),
            ];
        }

        $polo_groups = team_data::get_polo_groups($this->course->id);
        $team        = team_data::get_team($this->course->id, $this->context);

        $mediators_data = $this->format_members($team['mediators']);
        $tutors_data    = $this->format_members($team['tutors']);
        $supporttitle   = trim($this->instance->supporttitle ?? '');
        if ($supporttitle === '') {
            $supporttitle = get_string('seuponto', 'uemsinfotutoria');
        }

        $base = [
            'hascontent' => true,
            'shownotice' => false,
            'show_mediators' => $expectmediators,
            'show_tutors' => $expecttutors,
            'all_mediators' => $mediators_data,
            'all_tutors' => $tutors_data,
            'all_has_mediators' => !empty($mediators_data),
            'all_has_tutors' => !empty($tutors_data),
            'all_empty_mediators_message' => get_string('mediatornotinformedcourse', 'uemsinfotutoria'),
            'all_empty_tutors_message' => get_string('tutornotinformedcourse', 'uemsinfotutoria'),
        ];

        if ($isstudent) {
            $student_polos = team_data::get_student_polos($this->userid, $polo_groups);
            $polo_name     = !empty($student_polos) ? $student_polos[0] : '';
            $mine_mediators = $this->filter_by_polo($team['mediators'], $polo_name);
            $mine_tutors    = $this->filter_by_polo($team['tutors'], $polo_name);

            return $base + [
                'isstudent'           => true,
                'supporttitle'        => $supporttitle,
                'polo_name'           => $polo_name,
                'has_polo'            => !empty($polo_name),
                'mine_mediators'      => $mine_mediators,
                'mine_tutors'         => $mine_tutors,
                'mine_has_mediators'  => !empty($mine_mediators),
                'mine_has_tutors'     => !empty($mine_tutors),
                'mine_mediator_label' => $this->mediator_label(count($mine_mediators)),
                'mine_tutor_label'    => $this->tutor_label(count($mine_tutors)),
                'mine_empty_mediators_message' => get_string('mediatornotinformedpolo', 'uemsinfotutoria'),
                'mine_empty_tutors_message' => get_string('tutornotinformedpolo', 'uemsinfotutoria'),
                'nopolohelp' => get_string('nopolohelp', 'uemsinfotutoria'),
            ];
        }

        return $base + [
            'isstudent' => false,
        ];
    }

    /**
     * Convert raw member arrays into template-ready arrays.
     *
     * @param array $members  Output of team_data::get_team().
     * @return array
     */
    private function format_members(array $members): array {
        $result = [];
        foreach ($members as $m) {
            $user   = $m['user'];
            $polos  = $m['polos'];
            $count  = count($polos);

            $polos_text  = $this->join_polos($polos);
            $polos_label = $count > 1
                ? get_string('polosatendidos', 'uemsinfotutoria')
                : get_string('polo', 'uemsinfotutoria');

            $result[] = [
                'name'            => fullname($user),
                'profileimageurl' => $m['profileimageurl'],
                'messageurl'      => $m['messageurl'],
                'polos_label'     => $polos_label,
                'polos_text'      => $polos_text,
                'has_polos'       => !empty($polos),
            ];
        }
        return $result;
    }

    /**
     * Filter team members to those assigned to a given polo.
     *
     * @param array  $members   Raw team_data members.
     * @param string $polo_name Polo name to filter by.
     * @return array  Template-ready member arrays.
     */
    private function filter_by_polo(array $members, string $polo_name): array {
        if ($polo_name === '') {
            return [];
        }

        $filtered = array_filter($members, function ($m) use ($polo_name) {
            return in_array($polo_name, $m['polos'], true);
        });

        return $this->format_members(array_values($filtered));
    }

    /**
     * Singular/plural label for mediators.
     */
    private function mediator_label(int $count): string {
        return $count === 1
            ? get_string('mediadorpedagogico', 'uemsinfotutoria')
            : get_string('mediadorespedagogicos', 'uemsinfotutoria');
    }

    /**
     * Singular/plural label for tutors.
     */
    private function tutor_label(int $count): string {
        return $count === 1
            ? get_string('tutorpresencial', 'uemsinfotutoria')
            : get_string('tutorespresenciais', 'uemsinfotutoria');
    }

    /**
     * Join polo names with a middle dot separator (A · B · C).
     *
     * @param string[] $polos
     * @return string
     */
    private function join_polos(array $polos): string {
        return implode(' · ', $polos);
    }
}
