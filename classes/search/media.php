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
 * @id         $Id: media.php 4691 2018-02-02 17:32:47Z pholden $
 */

namespace local_mediaserver\search;

defined('MOODLE_INTERNAL') || die();

class media extends \core_search\base {

    /**
     * Returns recordset containing media streams modified since specified timestamp
     *
     * @param int $modifiedfrom
     * @return \moodle_recordset
     */
    public function get_recordset_by_timestamp($modifiedfrom = 0) {
        global $DB;

        return $DB->get_recordset_select('local_mediaserver_stream', 'submitted >= ?', array($modifiedfrom), 'submitted ASC');
    }

    /**
     * Returns the document associated with this media stream
     *
     * @param stdClass $record
     * @param array $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) {
        global $DB;

        $document = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);

        $document->set('contextid', \context_system::instance()->id);
        $document->set('courseid', SITEID);
        $document->set('itemid', $record->id);
        $document->set('title', content_to_text($record->title, false));
        $document->set('content', content_to_text($record->description, false));
        $document->set('userid', $record->userid);
        $document->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $document->set('modified', $record->submitted);

        // Index recorded program data.
        if ($record->source == 'epg') {
            $sql = 'SELECT p.description
                      FROM {local_mediaserver_program} p
                      JOIN {local_mediaserver_recording} r ON r.program = p.id
                     WHERE r.stream = :id';

            $description = $DB->get_field_sql($sql, ['id' => $record->id], MUST_EXIST);
            $document->set('description1', content_to_text($description, false));
        }

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && $options['lastindexedtime'] < $record->submitted) {
            $document->set_is_new(true);
        }

        return $document;
    }

    /**
     * Whether the user can access the document or not
     *
     * @param int $id
     * @return int
     */
    public function check_access($id) {
        global $DB;

        if (! $stream = $DB->get_record('local_mediaserver_stream', array('id' => $id))) {
            return \core_search\manager::ACCESS_DELETED;
        }

        if ($stream->done == 0) {
            return \core_search\manager::ACCESS_DENIED;
        }

        if (! has_capability('local/mediaserver:view', \context_system::instance())) {
            return \core_search\manager::ACCESS_DENIED;
        }

        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Returns a url to the document
     *
     * @param \core_search\document $document
     * @return \local_mediaserver\url
     */
    public function get_doc_url(\core_search\document $document) {
        return new \local_mediaserver\url('/local/mediaserver/view.php', array('id' => $document->get('itemid')));
    }

    /**
     * Returns a url to the document context
     *
     * @param \core_search\document $document
     * @return \local_mediaserver\url
     */
    public function get_context_url(\core_search\document $document) {
        global $DB;

        $category = $DB->get_field('local_mediaserver_stream', 'category', array('id' => $document->get('itemid')));

        return new \local_mediaserver\url('/local/mediaserver/index.php', array('id' => $category));
    }
}
