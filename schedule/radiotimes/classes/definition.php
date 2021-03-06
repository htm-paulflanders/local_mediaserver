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
 * @id         $Id: definition.php 4092 2016-02-04 10:44:01Z pholden $
 */

namespace mediaschedule_radiotimes;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class definition extends \local_mediaserver\media_schedule {
    /** @const string Radio Times XMLTV endpoint URL format. */
    const XMLTV_FORMAT = 'http://xmltv.radiotimes.com/xmltv/%d.dat';

    /** @const integer Define the XMLTV program fields. */
    const XMLTV_TITLE = 0;
    const XMLTV_SERIES = 1;
    const XMLTV_EPISODE = 2;
    const XMLTV_GENRE = 16;
    const XMLTV_DESCRIPTION = 17;
    const XMLTV_DATE = 19;
    const XMLTV_TIME = 20;
    const XMLTV_DURATION = 22;

    /** @var array Subset of program fields we're interested in. */
    private static $fields = array(
        self::XMLTV_TITLE, self::XMLTV_SERIES, self::XMLTV_EPISODE, self::XMLTV_GENRE, self::XMLTV_DESCRIPTION,
        self::XMLTV_DATE, self::XMLTV_TIME, self::XMLTV_DURATION,
    );

    /** @var array Cached timezone data. */
    private $tzdata = array();

    /**
     * Return timezone data for a given year
     *
     * @param int $year
     * @return array start times for GMT/DST
     */
    private function timezone_data($year) {
        global $DB;

        if (! array_key_exists($year, $this->tzdata)) {
            $timezone = \core_date::get_user_timezone_object(LOCAL_MEDIASERVER_TIMEZONE);
            $transitions = $timezone->getTransitions(
                gmmktime(0, 0, 0, 1, 1, $year), gmmktime(0, 0, 0, 31, 12, $year));

            $this->tzdata[$year] = array('dst' => $transitions[1]['ts'], 'std' => $transitions[2]['ts']);
        }

        return $this->tzdata[$year];
    }

    /**
     * Extract program start and end time from XMLTV program data
     *
     * Sometimes we need to force DST because the XMLTV data may indicate that a program starts at 01:30 during a crossover
     * from DST to GMT, and we can't automatically determine which timezone that time refers to
     *
     * @param string $date i.e. 12/02/2014
     * @param string $time i.e. 14:30
     * @param string $duration in minutes
     * @param bool $forcedst
     * @return array of int timestamp begin/end
     */
    private function extract_program_timing($date, $time, $duration, $forcedst) {
        list($day, $month, $year) = explode('/', $date);
        list($hour, $minute) = explode(':', $time);

        // Always calculate in GMT, adjust for DST later (they will be an hour out).
        $timebegin = gmmktime($hour, $minute, 0, $month, $day, $year);

        // If times fall within DST, we need to shift back an hour to calculate their 'real' GMT value.
        $tzdata = $this->timezone_data($year);
        if ($timebegin > $tzdata['dst'] && ($timebegin < $tzdata['std'] || $forcedst)) {
            $timebegin -= HOURSECS;
        }

        $timeend = $timebegin + ($duration * MINSECS);

        return array($timebegin, $timeend);
    }

    /**
     * Extract program series information from XMLTV program data
     *
     * @param string $data
     * @return array of int series/episode
     */
    private function extract_program_series($data) {
        $series = $episode = 0;

        if (preg_match('/^(\d+)(\/\d+)?, series (\d+)$/i', $data, $matches)) {
            $series  = (int)$matches[3];
            $episode = (int)$matches[1];
        }

        return array($series, $episode);
    }

    /**
     * Download data from Radio Times XMLTV service
     *
     * @param string $configuration Numeric code used by Radio Times to identify a channel
     * @param int $start
     * @param int $finish
     * @return void
     */
    public function download($configuration, $start, $finish) {
        $endpoint = sprintf(self::XMLTV_FORMAT, (int)$configuration);

        if ($response = $this->request($endpoint)) {
            $file = explode("\n", $response);

            // Each response line is tilde delimeted, containing 23 fields.
            foreach ($file as $line) {
                $data = explode('~', $line);
                if (count($data) == 23) {
                    // But we're only interested in a subset of them.
                    $data = array_intersect_key($data, array_flip(self::$fields));

                    // Check if title indicates program timezone is in BST (during switchover).
                    $title = $data[self::XMLTV_TITLE];
                    $forcedst = (strpos($title, '(BST)') === 0);

                    // Broadcast must be between listing begin/end.
                    list($timebegin, $timeend) = $this->extract_program_timing($data[self::XMLTV_DATE], $data[self::XMLTV_TIME], $data[self::XMLTV_DURATION], $forcedst);
                    if ($timebegin < $start || $timeend > $finish) {
                        continue;
                    }

                    $program = new \stdClass;
                    $program->title = $title;
                    $program->genre = $data[self::XMLTV_GENRE];

                    list($series, $episode) = $this->extract_program_series($data[self::XMLTV_SERIES]);
                    $program->series = $series;
                    $program->episode = $episode;

                    $program->episodetitle = $data[self::XMLTV_EPISODE];
                    $program->description = $data[self::XMLTV_DESCRIPTION];

                    $program->timebegin = $timebegin;
                    $program->timeend = $timeend;

                    array_push($this->programs, $program);
                }
            }
        }
    }
}
