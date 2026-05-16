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
 * View page for mod_uems_info_tutoria.
 *
 * @package    mod_uems_info_tutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = optional_param('id', 0, PARAM_INT); // Course module id.
$n = optional_param('n', 0, PARAM_INT); // Instance id.

if ($id) {
    $cm = get_coursemodule_from_id('uems_info_tutoria', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $uemsinfotutoria = $DB->get_record('uems_info_tutoria', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $uemsinfotutoria = $DB->get_record('uems_info_tutoria', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $uemsinfotutoria->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('uems_info_tutoria', $uemsinfotutoria->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/uems_info_tutoria:view', $context);

$PAGE->set_url('/mod/uems_info_tutoria/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($uemsinfotutoria->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Mark viewed for completion if enabled.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($uemsinfotutoria->name));

echo format_module_intro('uems_info_tutoria', $uemsinfotutoria, $cm->id);

echo $OUTPUT->notification(get_string('placeholdercontent', 'uems_info_tutoria'), 'info', false);

echo $OUTPUT->footer();
