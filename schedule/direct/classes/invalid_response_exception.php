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
 * @id         $Id: invalid_response_exception.php 4308 2016-06-10 09:30:14Z pholden $
 */

namespace mediaschedule_direct;

defined('MOODLE_INTERNAL') || die();

class invalid_response_exception extends \invalid_response_exception {

    /**
     * Constructor
     *
     * @param string $debug
     * @param array $json
     */
    public function __construct($debug, array $json = null) {
        // Try and extract useful error information from JSON array.
        if ($json !== null) {
            $debug .= ', Code: ' . $json['code'];

            if (array_key_exists('message', $json)) {
                $debug .= ' (' . $json['message'] . ')';
            } else if (array_key_exists('response', $json)) {
                $debug .= ' (' . $json['response'] . ')';
            }
        }

        parent::__construct($debug);
    }
}
