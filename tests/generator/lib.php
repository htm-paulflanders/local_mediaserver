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
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @id         $Id: lib.php 4752 2018-05-31 15:06:04Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/comment/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class local_mediaserver_generator extends component_generator_base {

    /**
     * Create stream record
     *
     * @param array|stdClass $record
     * @return stdClass
     *
     * @throws coding_exception
     */
    public function create_stream($record = null) {
        $record = (array)$record;

        $requiredfields = array('source', 'reference', 'title');
        foreach ($requiredfields as $field) {
            if (! isset($record[$field])) {
                throw new coding_exception('\'stream\' requires the field \'' . $field . '\' to be specified');
            }
        }

        if (! isset($record['description'])) {
            $record['description'] = '';
        }

        if (! isset($record['category'])) {
            $record['category'] = 1;
        }

        if (! isset($record['userid'])) {
            $record['userid'] = 0;
        }

        if (! isset($record['comments'])) {
            $record['comments'] = 0;
        }

        return local_mediaserver_stream_add($record['source'], $record['reference'], $record['title'], $record['description'],
            $record['category'], $record['userid'], $record['comments']);
    }

    /**
     * Create comment record
     *
     * @param array|stdClass $record
     * @return stdClass
     *
     * @throws coding_exception
     */
    public function create_comment($record = null) {
        $record = (array)$record;

        $requiredfields = array('streamid', 'comment');
        foreach ($requiredfields as $field) {
            if (! isset($record[$field])) {
                throw new coding_exception('\'stream\' requires the field \'' . $field . '\' to be specified');
            }
        }

        $options = new stdClass;
        $options->context = context_system::instance();
        $options->component = 'local_mediaserver';
        $options->area = 'stream';
        $options->itemid = $record['streamid'];

        $comment = new comment($options);
        $comment->set_post_permission(true);

        return $comment->add($record['comment']);
    }
}
