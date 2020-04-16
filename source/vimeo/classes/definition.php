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
 * @id         $Id: definition.php 4432 2016-10-14 11:33:01Z pholden $
 */

namespace mediasource_vimeo;

defined('MOODLE_INTERNAL') || die();

class definition extends \local_mediaserver\media_source {

    /**
     * Return Vimeo video ID from URL; matches the following:
     * http[s]://vimeo.com/{$reference}
     *
     * @return string|boolean false if no match
     */
    public function get_reference() {
        $result = false;

        if (preg_match('|^https?://vimeo\.com/(\d+)|i', $this->get_url(), $matches)) {
            $result = $matches[1];
        }

        return $result;
    }

    /**
     * Return job script for a Vimeo stream
     *
     * @param stdClass $stream
     * @return string
     */
    public function get_job(\stdClass $stream) {
        $arguments = array('id' => $stream->code, 'vid' => $stream->reference, 'title' => $stream->title);

        return self::safe_job('./get_vimeo.sh', $arguments);
    }
}