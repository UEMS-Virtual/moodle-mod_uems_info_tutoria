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
 * Renderable page for mod_uems_info_tutoria.
 *
 * @package    mod_uems_info_tutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_uems_info_tutoria\output;

use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Page renderable for the tutoring information activity.
 */
class tutoria_page implements renderable, templatable {
    /** @var stdClass Activity instance. */
    private stdClass $instance;

    /** @var stdClass Course module. */
    private stdClass $cm;

    /**
     * Constructor.
     *
     * @param stdClass $instance Activity instance.
     * @param stdClass $cm Course module.
     */
    public function __construct(stdClass $instance, stdClass $cm) {
        $this->instance = $instance;
        $this->cm = $cm;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output Renderer.
     * @return array<string, mixed>
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'hasintro' => trim((string) $this->instance->intro) !== '',
            'intro' => format_module_intro('uems_info_tutoria', $this->instance, $this->cm->id),
            'placeholder' => get_string('placeholdercontent', 'uems_info_tutoria'),
        ];
    }
}
