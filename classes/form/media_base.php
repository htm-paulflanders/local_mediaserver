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
 * @package    local_mediaserver
 * @copyright  2013 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @id         $Id: media_base.php 4519 2017-02-06 16:27:59Z pholden $
 */

namespace local_mediaserver\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

abstract class media_base extends \moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'mediainfo', get_string('addcontentinfo', 'local_mediaserver'));

        $mform->addElement('text', 'title', get_string('name'), array('maxlength' => 100));
        $mform->addRule('title', get_string('required'), 'required');
        $mform->addRule('title', get_string('maximumchars', '', 100), 'maxlength', 100);
        $mform->setType('title', PARAM_TEXT);

        $options = local_mediaserver_root_category_select();

        $mform->addElement('autocomplete', 'category', get_string('category', 'local_mediaserver'), $options);
        $mform->addRule('category', get_string('required'), 'required');
        $mform->setType('category', PARAM_INT);

        $mform->addElement('htmleditor', 'description', 'Description');
        $mform->setType('description', PARAM_CLEANHTML);

        $mform->setExpanded('mediainfo');

        $this->add_action_buttons(true);
    }

    /**
     * Form validation
     *
     * @param array $data raw form data
     * @param array $files
     * @return array of errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Category must be selected.
        if (array_key_exists('category', $data) && $data['category'] == 0) {
            $errors['category'] = get_string('required');
        }

        return $errors;
    }
}
