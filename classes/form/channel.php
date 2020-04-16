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
 * @id         $Id: channel.php 4302 2016-06-06 14:52:31Z pholden $
 */

namespace local_mediaserver\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class channel extends \moodleform {

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        list($channel, $filemanageroptions) = $this->_customdata;

        $mform->addElement('text', 'name', get_string('channelname', 'local_mediaserver'), array('maxlength' => 32));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');
        $mform->addRule('name', get_string('maximumchars', '', 32), 'maxlength', 32);
        $mform->addHelpButton('name', 'channelname', 'local_mediaserver');

        // Customize form depending on configured schedule source.
        $source = get_config('local_mediaserver', 'schedulesource');

        $mform->addElement('text', 'configuration', get_string('channelconfig', $source));
        $mform->setType('configuration', PARAM_ALPHANUM);
        $mform->addRule('configuration', null, 'required');
        $mform->addHelpButton('configuration', 'channelconfig', $source);

        $hours = array_map(function($hour) {
            return sprintf('%02d:00', $hour);
        }, range(0, 24));

        $hourbegin = array();
        $hourbegin[] = &$mform->createElement('select', 'hourbegin', null, $hours);
        $hourbegin[] = &$mform->createElement('checkbox', 'disabled', null, get_string('disable'));

        $mform->addGroup($hourbegin, 'broadcast', get_string('channelbegin', 'local_mediaserver'), ' ', false);
        $mform->disabledIf('hourbegin', 'disabled', 'checked');
        $mform->setType('hourbegin', PARAM_INT);

        $mform->addElement('select', 'hourend', get_string('channelend', 'local_mediaserver'), $hours);
        $mform->disabledIf('hourend', 'disabled', 'checked');
        $mform->setType('hourend', PARAM_INT);

        // Allow sortorder to be configured if editing channel.
        $maxorder = $DB->get_field('local_mediaserver_channel', 'COALESCE(MAX(sortorder), 0)', array());
        if (! empty($channel->id)) {
            $orders = range(1, $maxorder);
            $orders = array_combine($orders, $orders);

            $mform->addElement('select', 'sortorder', get_string('order'), $orders);
            $mform->setType('sortorder', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'sortorder');
            $mform->setDefault('sortorder', $maxorder + 1);
            $mform->hardFreeze('sortorder');
            $mform->setType('sortorder', PARAM_INT);
        }

        $mform->addElement('filemanager', 'iconfile', get_string('icon'), null, $filemanageroptions);
        $mform->addRule('iconfile', null, 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);

        $this->set_data($channel);
    }

    /**
     * Form validation; ensure channel name/configuration (relative to current schedule source) are unique
     *
     * @param array $data raw form data
     * @param array $files
     * @return array of errors
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $id = $data['id'];
        $name = trim($data['name']);
        $configuration = trim($data['configuration']);

        // Make sure channel name isn't already in use.
        if ($DB->record_exists_select('local_mediaserver_channel', 'name = ? AND id != ?', array($name, $id))) {
            $errors['name'] = get_string('errornotunique', 'local_mediaserver');
        }

        // Make sure configuration isn't already assigned to another channel (use case-sensitive comparison).
        $select = 'plugin = ? AND ' . $DB->sql_like('value', '?', true) . ' AND name != ?';
        $source = get_config('local_mediaserver', 'schedulesource');

        if ($DB->record_exists_select('config_plugins', $select, array($source, $configuration, 'channel' . $id))) {
            $errors['configuration'] = get_string('errornotunique', 'local_mediaserver');
        }

        return $errors;
    }
}
