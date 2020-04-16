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
 * @id         $Id: helper.php 3981 2015-12-01 14:22:29Z pholden $
 */

namespace mediaschedule_atlas;

defined('MOODLE_INTERNAL') || die();

class helper {

    /** @const string Format string for ISO dates. */
    const ISO_DATE_FORMAT = 'Y-m-d\TH:i:s.000\Z';

    /** $var array Press Association genre lookup. See https://github.com/atlasapi/atlas/blob/master/src/main/java/org/atlasapi/remotesite/pa/PaGenreMap.java */
    private static $genres = array(
        '1000' => 'Drama',
        '1100' => 'Drama',
        '1200' => 'Drama',
        '1300' => 'Drama',
        '1400' => 'Comedy',
        '1500' => 'Entertainment',
        '1600' => 'Drama',
        '1700' => 'Drama',
        '1800' => 'Drama',
        '1F00' => 'Drama',
        '1F01' => 'Drama',
        '1F02' => 'Drama',
        '1F03' => 'Drama',
        '1F04' => 'Drama',
        '1F05' => 'Drama',
        '1F06' => 'Childrens',
        '1F07' => 'Drama',
        '1F08' => 'Drama',
        '1F09' => 'Drama',
        '1F0A' => 'Drama',
        '1F0B' => 'Drama',
        '1F0C' => 'Drama',
        '1F0D' => 'Drama',
        '1F0E' => 'Drama',
        '1F0F' => 'Drama',
        '1F10' => 'Drama',
        '1F11' => 'Drama',
        '1F12' => 'Comedy',
        '1F13' => 'Comedy',
        '1F14' => 'Comedy',
        '1F15' => 'Drama',
        '1F16' => 'Drama',
        '1F17' => 'Drama',
        '2000' => 'News',
        '2200' => 'News',
        '2300' => 'News',
        '2F02' => 'News',
        '2F03' => 'News',
        '2F04' => 'News',
        '2F05' => 'News',
        '2F06' => 'News',
        '2F07' => 'News',
        '2F08' => 'News',
        '3000' => 'Entertainment',
        '3100' => 'Entertainment',
        '3200' => 'Entertainment',
        '3300' => 'Entertainment',
        '3F00' => 'Entertainment',
        '3F01' => 'Entertainment',
        '3F02' => 'Entertainment',
        '3F03' => 'Entertainment',
        '3F04' => 'Entertainment',
        '3F05' => 'Entertainment',
        '4000' => 'Sports',
        '4100' => 'Sports',
        '4200' => 'Sports',
        '4300' => 'Sports',
        '4400' => 'Sports',
        '4500' => 'Sports',
        '4600' => 'Sports',
        '4700' => 'Sports',
        '4800' => 'Sports',
        '4900' => 'Sports',
        '4A00' => 'Sports',
        '4B00' => 'Sports',
        '4F00' => 'Sports',
        '4F01' => 'Sports',
        '4F02' => 'Sports',
        '4F03' => 'Sports',
        '4F04' => 'Sports',
        '4F05' => 'Sports',
        '4F06' => 'Sports',
        '4F07' => 'Sports',
        '4F08' => 'Sports',
        '4F09' => 'Sports',
        '4F0A' => 'Sports',
        '4F0B' => 'Sports',
        '4F0C' => 'Sports',
        '4F0D' => 'Sports',
        '4F0E' => 'Sports',
        '4F0F' => 'Sports',
        '4F10' => 'Sports',
        '4F11' => 'Sports',
        '4F12' => 'Sports',
        '4F13' => 'Sports',
        '4F14' => 'Sports',
        '4F15' => 'Sports',
        '4F16' => 'Sports',
        '4F17' => 'Sports',
        '4F19' => 'Sports',
        '4F1A' => 'Sports',
        '4F1B' => 'Sports',
        '4F1C' => 'Sports',
        '4F1D' => 'Sports',
        '4F1E' => 'Sports',
        '4F1F' => 'Sports',
        '4F20' => 'Sports',
        '4F21' => 'Sports',
        '4F22' => 'Sports',
        '4F23' => 'Sports',
        '4F24' => 'Sports',
        '4F25' => 'Sports',
        '4F26' => 'Sports',
        '4F27' => 'Sports',
        '4F28' => 'Sports',
        '4F29' => 'Sports',
        '4F2A' => 'Sports',
        '4F2B' => 'Sports',
        '4F2C' => 'Sports',
        '4F2D' => 'Sports',
        '4F2E' => 'Sports',
        '4F2F' => 'Sports',
        '4F30' => 'Sports',
        '4F31' => 'Sports',
        '4F32' => 'Sports',
        '4F33' => 'Sports',
        '4F34' => 'Sports',
        '4F35' => 'Sports',
        '4F36' => 'Sports',
        '4F37' => 'Sports',
        '4F39' => 'Sports',
        '4F3A' => 'Sports',
        '4F3B' => 'Sports',
        '4F3C' => 'Sports',
        '4F3D' => 'Sports',
        '4F3E' => 'Sports',
        '4F3F' => 'Sports',
        '4F40' => 'Sports',
        '4F41' => 'Sports',
        '4F42' => 'Sports',
        '4F43' => 'Sports',
        '4F44' => 'Sports',
        '4F45' => 'Sports',
        '4F46' => 'Sports',
        '4F47' => 'Sports',
        '4F48' => 'Sports',
        '4F49' => 'Sports',
        '4F4A' => 'Sports',
        '4F4B' => 'Sports',
        '4F4C' => 'Sports',
        '4F4D' => 'Sports',
        '4F4E' => 'Sports',
        '4F50' => 'Sports',
        '4F51' => 'Sports',
        '4F52' => 'Sports',
        '4F53' => 'Sports',
        '4F54' => 'Sports',
        '4F55' => 'Sports',
        '4F56' => 'Sports',
        '4F57' => 'Sports',
        '4F58' => 'Sports',
        '5000' => 'Childrens',
        '5100' => 'Childrens',
        '5200' => 'Childrens',
        '5300' => 'Childrens',
        '5400' => 'Childrens',
        '5500' => 'Childrens',
        '6000' => 'Music',
        '6200' => 'Music',
        '6300' => 'Music',
        '6400' => 'Music',
        '6600' => 'Music',
        '6F01' => 'Music',
        '6F02' => 'Music',
        '6F03' => 'Music',
        '6F05' => 'Music',
        '6F06' => 'Music',
        '6F07' => 'Music',
        '6F08' => 'Music',
        '6F09' => 'Music',
        '6F0A' => 'Music',
        '7000' => 'Factual',
        '7100' => 'Factual',
        '7200' => 'Factual',
        '7400' => 'Factual',
        '7500' => 'Factual',
        '7600' => 'Factual',
        '7700' => 'Factual',
        '7800' => 'Factual',
        '7900' => 'Factual',
        '7A00' => 'Factual',
        '7B00' => 'Factual',
        '8000' => 'Factual',
        '8200' => 'Factual',
        '9000' => 'Lifestyle',
        '9100' => 'Factual',
        '9200' => 'Factual',
        '9300' => 'Factual',
        '9400' => 'Factual',
        '9500' => 'Factual',
        '9700' => 'Learning',
        '9F00' => 'Learning',
        '9F01' => 'Lifestyle',
        '9F02' => 'Lifestyle',
        '9F03' => 'Factual',
        '9F04' => 'Factual',
        '9F05' => 'Factual',
        '9F06' => 'Factual',
        '9F07' => 'Factual',
        '9F08' => 'Factual',
        '9F09' => 'Factual',
        '9F0A' => 'Factual',
        'A000' => 'Lifestyle',
        'A100' => 'Lifestyle',
        'A200' => 'Lifestyle',
        'A300' => 'Lifestyle',
        'A400' => 'Lifestyle',
        'A500' => 'Lifestyle',
        'A600' => 'Lifestyle',
        'A700' => 'Lifestyle',
        'AF00' => 'Lifestyle',
        'AF01' => 'Lifestyle',
        'AF02' => 'Lifestyle',
        'AF03' => 'Lifestyle',
        'BF01' => 'Film',
    );

    /**
     * Return a sensible program genre from Atlas genres element
     *
     * @param array $genres
     * @return string
     */
    public static function genre_lookup(array $genres) {
        $result = get_string('other');
        if (count($genres) == 0) {
            return $result;
        }

        // Sort the genres, and get the last item (will be one of Atlas' own references if supplied).
        \core_collator::asort($genres);
        $genre = array_pop($genres);

        // Check if genre URI matches an Atlas or Press Association reference.
        if (preg_match('|^http://ref.atlasapi.org/genres/atlas/(\w+)$|', $genre, $matches)) {
            $result = ucfirst($matches[1]);
        } else if (preg_match('|^http://pressassociation.com/genres/([A-F\d]{4})$|', $genre, $matches)) {
            // Try the lookup table to find a sensible value.
            $lookup = $matches[1];

            if (array_key_exists($lookup, self::$genres)) {
                $result = self::$genres[$lookup];
            }
        }

        return $result;
    }

    /**
     * Return a given timestamp as a formatted ISO date string in GMT (i.e. 2014-12-14T09:00:00.000Z)
     *
     * @param int $timestamp
     * @return string
     */
    public static function isodate($timestamp) {
        return gmdate(self::ISO_DATE_FORMAT, $timestamp);
    }
}
