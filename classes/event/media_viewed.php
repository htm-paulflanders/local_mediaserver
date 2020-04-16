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
 * @id         $Id: media_viewed.php 3596 2015-05-15 10:58:05Z pholden $
 */

namespace local_mediaserver\event;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * The local_mediaserver media viewed event class.
 *
 * @since Moodle 2.6
 */
class media_viewed extends \core\event\base {

    /**
     * Init method
     *
     * @return void
     */
    protected function init() {
        $this->context = \context_system::instance();

        $this->data['objecttable'] = 'local_mediaserver_stream';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_media_viewed', 'local_mediaserver');
    }

    /**
     * Returns description of what happened
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' viewed the media item with id '$this->objectid'.";
    }

    /**
     * Returns relevant URL
     *
     * @return \local_mediaserver_url
     */
    public function get_url() {
        return new \local_mediaserver_url('/local/mediaserver/view.php', array('id' => $this->objectid));
    }

    /**
     * Custom validation
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (! isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set');
        }
    }
}