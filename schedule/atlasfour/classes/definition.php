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
 * @id         $Id: definition.php 4300 2016-06-06 14:22:22Z pholden $
 */

namespace mediaschedule_atlasfour;

use mediaschedule_atlas\helper;

defined('MOODLE_INTERNAL') || die();

class definition extends \local_mediaserver\media_schedule {
    /** @const string SCHEDULE_API Endpoint for schedule data. */
    const SCHEDULE_API = 'http://atlas.metabroadcast.com/4/schedules/%s.json';

    /** @const string Root JSON elements for accessing schedule data. */
    const ATLAS_ROOT = 'schedule';
    const ATLAS_ITEMS = 'entries';

    /** @const string JSON elements for each program. */
    const ATLAS_ITEM = 'item';
    const ATLAS_TITLE_CONTAINER = 'display_title';
    const ATLAS_TITLE = 'title';
    const ATLAS_SERIES_NUMBER = 'series_number';
    const ATLAS_EPISODE_NUMBER = 'episode_number';
    const ATLAS_GENRES = 'genres';
    const ATLAS_DESCRIPTION = 'description';

    /** @const string JSON elements for each program broadcast. */
    const ATLAS_BROADCASTS = 'broadcasts';
    const ATLAS_TRANSMISSION = 'transmission_time';
    const ATLAS_DURATION = 'broadcast_duration';

    /** @var array Subset of program fields we're interested in. */
    private static $fields = array(
        self::ATLAS_TITLE_CONTAINER, self::ATLAS_TITLE, self::ATLAS_SERIES_NUMBER, self::ATLAS_EPISODE_NUMBER, self::ATLAS_GENRES, self::ATLAS_DESCRIPTION, self::ATLAS_BROADCASTS
    );

    /**
     * Make a request to the Atlas API for schedule data
     *
     * @param string $channel Atlas channel ID
     * @param int $start
     * @param int $finish
     * @return string
     *
     * @throws \moodle_exception
     */
    private function request_schedule($channel, $start, $finish) {
        // The API endpoint in version 4 contains the channel code in the request URL.
        $scheduleapi = sprintf(self::SCHEDULE_API, clean_param($channel, PARAM_ALPHANUM));

        $post = array(
            'key' => get_config('mediaschedule_atlas', 'apikey'),
            'source' => 'pressassociation.com',
            'from' => helper::isodate($start),
            'to' => helper::isodate($finish),
            'annotations' => 'content_detail',
        );

        // Catch exception thrown from request method, extract appropriate error message from returned data.
        try {
            $response = $this->request($scheduleapi, $post);
        } catch (\local_mediaserver\exception\media_schedule_exception $ex) {
            $json = json_decode($ex->response->results, true);
            $debug = trim($ex->debuginfo) . ' (' . $json['message'] . ')';

            throw new \moodle_exception($ex->errorcode, $ex->module, '', null, $debug);
        }

        return $response;
    }

    /**
     * Download schedule data from the Atlas API service
     *
     * @param string $configuration Atlas channel ID
     * @param int $start
     * @param int $finish
     * @return void
     */
    public function download($configuration, $start, $finish) {
        $response = $this->request_schedule($configuration, $start, $finish);

        $json = json_decode($response, true);
        foreach ($json[self::ATLAS_ROOT][self::ATLAS_ITEMS] as $item) {
            $item = array_intersect_key($item[self::ATLAS_ITEM], array_flip(self::$fields));

            $program = new \stdClass;
            $program->title = $item[self::ATLAS_TITLE_CONTAINER][self::ATLAS_TITLE];

            // Check whether we have both series and episode data available for program.
            if (array_key_exists(self::ATLAS_SERIES_NUMBER, $item) && ($item[self::ATLAS_SERIES_NUMBER] > 0) && 
                array_key_exists(self::ATLAS_EPISODE_NUMBER, $item) && ($item[self::ATLAS_EPISODE_NUMBER] > 0)) {

                $program->series  = (int)$item[self::ATLAS_SERIES_NUMBER];
                $program->episode = (int)$item[self::ATLAS_EPISODE_NUMBER];
            } else {
                $program->series  = 0;
                $program->episode = 0;
            }

            // The title element is used either for the program or episode title.
            $program->episodetitle = $item[self::ATLAS_TITLE];

            $program->description = $item[self::ATLAS_DESCRIPTION];

            // Genres element is an array of URI's, pass them to a lookup method to get a sensible value.
            $genres = $item[self::ATLAS_GENRES];
            $program->genre = helper::genre_lookup($genres);

            // Broadcasts element is an array, we just use the first item in it.
            $broadcast = array_shift($item[self::ATLAS_BROADCASTS]);
            $program->timebegin = strtotime($broadcast[self::ATLAS_TRANSMISSION]);
            $program->timeend = $program->timebegin + $broadcast[self::ATLAS_DURATION];

            array_push($this->programs, $program);
        }
    }
}
