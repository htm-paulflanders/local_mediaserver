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
 * @id         $Id: series_updated.php 4408 2016-09-01 09:35:46Z pholden $
 */

namespace local_mediaserver\event;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * The local_mediaserver series updated event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - int finished: series finished state.
 * }
 * @since Moodle 2.9
 */
class series_updated extends \core\event\base {

    /**
     * Init method
     *
     * @return void
     */
    protected function init() {
        $this->context = \context_system::instance();

        $this->data['objecttable'] = 'local_mediaserver_series';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_series_updated', 'local_mediaserver');
    }

    /**
     * Returns description of what happened
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' updated the series link with id '$this->objectid' setting finished to '{$this->other['finished']}'.";
    }

    /**
     * Returns relevant URL
     *
     * @return \local_mediaserver_url
     */
    public function get_url() {
        return new \local_mediaserver_url('/local/mediaserver/guide/links.php');
    }

    /**
     * Custom validation
     *
     * @return void
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();

        if (! isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set');
        }

        if (! isset($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set');
        }

        if (! isset($this->other['finished'])) {
            throw new \coding_exception('The \'finished\' must be set in other');
        }
    }
}
