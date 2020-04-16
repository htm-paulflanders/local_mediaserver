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
 * @id         $Id: today.php 4842 2018-10-26 14:49:09Z pholden $
 */

namespace local_mediaserver\block;

defined('MOODLE_INTERNAL') || die();

class today extends \block_contents {

    /** @var int Unix timestamp. */
    private $time;

    /** @var int Channel ID. */
    private $channel;

    /**
     * Class constructor; initialize block title and contents
     *
     * @param int $time
     * @param int $channel
     */
    public function __construct($time, $channel = 0) {
        global $PAGE;

        parent::__construct();

        // Initialise javascript module, for displaying program info when hovering over it.
        $PAGE->requires->js_call_amd('local_mediaserver/today', 'init');

        // Normalize time, round down to beginning of day.
        $this->time    = local_mediaserver_time_round($time, true);
        $this->channel = $channel;

        $this->title   = get_string('today');
        $this->content = $this->get_programs();
    }

    /**
     * Generate today's program content
     *
     * @return string
     */
    private function get_programs() {
        global $DB;

        $strtimeformat = get_string('strftimetime', 'langconfig');
        $now = time();

        $output = \html_writer::start_tag('ul');

        if ($programs = $this->get_programs_data()) {
            foreach ($programs as $program) {
                $classes = array('small-text');
                $classes[] = ($program->stream ? 'program-recorded' : 'program-favourite');

                if ($program->timebegin < ($now + LOCAL_MEDIASERVER_RECORD_BUFFER)) {
                    $classes[] = 'program-old';
                }

                $link = new \local_mediaserver_url('/local/mediaserver/guide/channel.php',
                    array('id' => $program->channel, 't' => $program->timebegin), 'p' . $program->id);

                $channel = $DB->get_field('local_mediaserver_channel', 'name', array('id' => $program->channel));

                $strtime = local_mediaserver_local_time($program->timebegin, $strtimeformat);
                $strduration = format_time($program->timeend - $program->timebegin);

                // Program JSON data object.
                $data = json_encode(array(
                    'time' => $strtime,
                    'duration' => $strduration,
                    'description' => shorten_text($program->description, 440),
                ));
                $content  = \html_writer::link($link, $program->title, array('data-program' => $data));
                $content .= $channel . ', ' . get_string('programpopupheading', 'local_mediaserver', (object)['time' => $strtime, 'duration' => $strduration]);

                $output .= \html_writer::tag('li', $content, array('class' => implode(' ', $classes)));
            }
        } else {
            $output .= \html_writer::tag('li', get_string('nothingtodisplay'));
        }

        $output .= \html_writer::end_tag('ul');

        return $output;
    }

    /**
     * Return all recorded/favourite programs for a given day
     *
     * @return array
     */
    private function get_programs_data() {
        global $DB, $USER;

        // Time for the end of the day.
        $date = usergetdate($this->time);
        $timeend = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);

        // Return all program recordings or favourites.
        $select = 'p.timebegin BETWEEN :timebegin AND :timeend AND COALESCE(r.id, f.id) IS NOT NULL';
        $params = array('userid' => $USER->id, 'timebegin' => $this->time, 'timeend' => $timeend);

        // Limit by channel if set.
        if ($this->channel) {
            $select .= ' AND p.channel = :channel';
            $params['channel'] = $this->channel;
        }

        return local_mediaserver_program_select($select, $params);
    }
}
