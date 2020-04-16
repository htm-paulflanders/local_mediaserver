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
 * @id         $Id: media_record.php 2988 2014-06-16 13:12:02Z pholden $
 */

namespace local_mediaserver\form;

defined('MOODLE_INTERNAL') || die();

class media_record extends media_base {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        list($program, $returnurl) = $this->_customdata;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'single');
        $mform->setType('single', PARAM_BOOL);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        parent::definition();

        $title = local_mediaserver_program_title_default($program);
        $this->set_data(array('id' => $program->id, 'single' => 1, 'title' => $title, 'returnurl' => $returnurl));
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

        list($program, $returnurl) = $this->_customdata;

        // Make sure program start time is within allowed recording window.
        if ($program->timebegin < (time() + LOCAL_MEDIASERVER_RECORD_BUFFER)) {
            $errors['title'] = get_string('errorprogramexpired', 'local_mediaserver');
        } else {
            // Make sure there is an available tuner to record the program.
            if (local_mediaserver_program_tuner($program) === false) {
                $errors['title'] = get_string('errornotuners', 'local_mediaserver');
            }
        }

        return $errors;
    }
}
