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
 * @id         $Id: queue_table.php 4666 2017-08-18 10:13:58Z pholden $
 */

namespace local_mediaserver\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class queue_table extends \table_sql {

    /**
     * Constructor
     */
    public function __construct() {
        global $PAGE;

        parent::__construct('local-mediaserver-queue-table');

        // Define columns.
        $headers = get_strings(array('programtitle', 'series', 'episodes', 'episodelast'), 'local_mediaserver');

        $columns = array(
            'icon' => null,
            'title' => get_string('name'),
            'fullname' => get_string('name'),
            'submitted' => get_string('date'),
        );
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        // Table configuration.
        $this->set_attribute('class', $this->attributes['class'] . ' table-programs local-mediaserver-queue-table');

        $this->sortable(true, 'submitted', SORT_DESC);
        $this->no_sorting('icon');

        $this->useridfield = 'userid';

        $this->initialbars(false);
        $this->collapsible(false);

        $this->init_sql();
    }

    /**
     * Initializes table SQL properties
     *
     * @return void
     */
    protected function init_sql() {
        global $DB, $USER;

        $fields = 's.*, ' . get_all_user_name_fields(true, 'u');

        $from = '{local_mediaserver_stream} s
            JOIN {user} u ON u.id = s.userid';

        $where = 'done = :done';
        $params = array('done' => 0);

        // Users without the 'reports' capability can only see their own queued items.
        if (! has_capability('local/mediaserver:reports', \context_system::instance())) {
            $where .= ' AND userid = :userid';
            $params['userid'] = $USER->id;
        }

        $this->set_sql($fields, $from, $where, $params);
        $this->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);
    }

    /**
     * Return SQL fragment that can be used in an ORDER BY clause
     *
     * @return string
     */
    public function get_sql_sort() {
        $sort = parent::get_sql_sort();

        return $sort . ', s.id DESC';
    }

    /**
     * Format stream icon table column
     *
     * @param stdClass $stream
     * @return string
     */
    public function col_icon(\stdClass $stream) {
        global $OUTPUT;

        // Return schedule class icon for EPG streams.
        if (strcmp($stream->source, 'epg') == 0) {
            $icon = local_mediaserver_schedule_current()->get_icon();
        } else {
            $sourceclass = "mediasource_{$stream->source}\definition";

            // Return source class icons or fallback to generic ones.
            if (class_exists($sourceclass)) {
                $icon = (new $sourceclass)->get_icon();
            } else {
                $title = get_string('source' . $stream->source, 'local_mediaserver');

                $iconmap = array(
                    'upload' => 't/backup',
                    'url' => '/e/insert_edit_link',
                );

                $icon = new \pix_icon($iconmap[$stream->source], $title, 'moodle', array('class' => 'icon'));
            }
        }

        return $OUTPUT->render($icon);
    }

    /**
     * Format stream title table column
     *
     * @param stdClass $stream
     * @return string
     */
    public function col_title(\stdClass $stream) {
        global $DB;

        $strtitle = s($stream->title);

        if ($stream->source == 'upload' && $stream->reference) {
            $download = \moodle_url::make_pluginfile_url(\context_system::instance()->id, 'local_mediaserver', 'upload',
                $stream->id, '/', $stream->reference);

            $title = \html_writer::link($download, $strtitle, array('title' => $stream->title));
        } else if ($stream->source == 'epg') {
            $sql = 'SELECT p.*
                      FROM {local_mediaserver_program} p
                      JOIN {local_mediaserver_recording} r ON r.program = p.id
                     WHERE r.stream = :stream';

            $program = $DB->get_record_sql($sql, array('stream' => $stream->id), MUST_EXIST);
            $programlink = new \local_mediaserver_url('/local/mediaserver/guide/channel.php',
                array('id' => $program->channel, 't' => $program->timebegin), 'p' . $program->id);

            $title = \html_writer::link($programlink, $strtitle, array('title' => $stream->title));
        } else {
            $title = \html_writer::tag('span', $strtitle, array('title' => $stream->title));
        }

        return $title;
    }

    /**
     * Format stream submitted table column
     *
     * @param stdClass $stream
     * @return string
     */
    public function col_submitted(\stdClass $stream) {
        $format = get_string('strftimedatetime', 'langconfig');

        return userdate($stream->submitted, $format);
    }
}
