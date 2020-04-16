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
 * @id         $Id: genre_test.php 3980 2015-12-01 14:00:16Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use mediaschedule_atlas\helper;

/**
 * Unit test for Atlas Helper class genre resolution
 *
 * @group greenhead
 * @group local_mediaserver
 */
class mediaschedule_atlas_genre_testcase extends advanced_testcase {

    /**
     * Test the genre_lookup method
     *
     * @return void
     */
    public function test_genres() {
        // Simple/single element genres.
        $this->assertEquals('Sports', helper::genre_lookup(['http://ref.atlasapi.org/genres/atlas/Sports']));
        $this->assertEquals('Sports', helper::genre_lookup(['http://pressassociation.com/genres/4F4B']));

        // Sports should be returned because 4F4B will be the last item when sorted.
        $genres = [
            'http://pressassociation.com/genres/4F4B',
            'http://pressassociation.com/genres/1F11',
        ];
        $this->assertEquals('Sports', helper::genre_lookup($genres));

        // Sports should be returned because native Atlas genres are preferred.
        $genres = [
            'http://ref.atlasapi.org/genres/atlas/Sports',
            'http://pressassociation.com/genres/1F11',
        ];
        $this->assertEquals('Sports', helper::genre_lookup($genres));

        // Failed lookups.
        $other = get_string('other');
        $this->assertEquals($other, helper::genre_lookup([]));
        $this->assertEquals($other, helper::genre_lookup(['Rubbish']));
        $this->assertEquals($other, helper::genre_lookup(['http://pressassociation.com/genres/ZZZZ']));
    }
}
