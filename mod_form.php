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
 * Instance form for mod_uemsinfotutoria.
 *
 * @package    mod_uemsinfotutoria
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Activity instance form.
 */
class mod_uemsinfotutoria_mod_form extends moodleform_mod {
    /**
     * Define the form.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setDefault('name', get_string('defaultname', 'uemsinfotutoria'));

        $this->standard_intro_elements();
        $mform->setDefault('introeditor', [
            'text' => get_string('defaultintro', 'uemsinfotutoria'),
            'format' => FORMAT_HTML,
        ]);

        $mform->addElement('header', 'uemsinfotutoriasettings', get_string('pluginname', 'uemsinfotutoria'));

        $mform->addElement('text', 'supporttitle', get_string('supporttitle', 'uemsinfotutoria'), ['size' => '64']);
        $mform->setType('supporttitle', PARAM_TEXT);
        $mform->addRule('supporttitle', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setDefault('supporttitle', get_string('seuponto', 'uemsinfotutoria'));

        $expectoptions = [
            0 => get_string('expect:auto', 'uemsinfotutoria'),
            1 => get_string('yes'),
            2 => get_string('no'),
        ];

        $mform->addElement('select', 'expecttutor', get_string('expecttutor', 'uemsinfotutoria'), $expectoptions);
        $mform->setDefault('expecttutor', 0);
        $mform->addHelpButton('expecttutor', 'expecttutor', 'uemsinfotutoria');

        $mform->addElement('select', 'expectmediator', get_string('expectmediator', 'uemsinfotutoria'), $expectoptions);
        $mform->setDefault('expectmediator', 0);
        $mform->addHelpButton('expectmediator', 'expectmediator', 'uemsinfotutoria');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
