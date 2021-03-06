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
 * @id         $Id: category.php 3602 2015-05-19 16:08:52Z pholden $
 */

namespace local_mediaserver\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class category extends \moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        list($category) = $this->_customdata;

        $mform->addElement('text', 'name', get_string('categoryname', 'local_mediaserver'), array('maxlength' => 64));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');
        $mform->addRule('name', get_string('maximumchars', '', 64), 'maxlength', 64);

        $mform->addElement('hidden', 'parent');
        $mform->setType('parent', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);

        $this->set_data($category);
    }

    /**
     * Form validation; ensure category name is unique
     *
     * @param array $data raw form data
     * @param array $files
     * @return array of errors
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Category name must be unique in parent.
        if ($data['parent'] > 0) {
            $parent = $DB->get_record('local_mediaserver_category', array('id' => $data['parent']), '*', MUST_EXIST);

            $path = $parent->path;
            $depth = $parent->depth;
        } else {
            $path = '/';
            $depth = 0;
        }

        $select = $DB->sql_like('path', ':pathlike') . ' AND depth = :depth AND name = :name';
        $params = array('pathlike' => rtrim($path, '/') . '/%', 'depth' => $depth + 1, 'name' => trim($data['name']));

        if ($category = $DB->get_record_select('local_mediaserver_category', $select, $params)) {
            if ($category->id != $data['id']) {
                $errors['name'] = get_string('errornotunique', 'local_mediaserver');
            }
        }

        return $errors;
    }
}
