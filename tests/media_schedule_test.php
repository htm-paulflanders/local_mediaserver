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
 * @id         $Id: media_schedule_test.php 4630 2017-06-13 14:09:33Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use \local_mediaserver\media_schedule;

/**
 * Unit test for base media schedule class
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_media_schedule_testcase extends advanced_testcase {

    /**
     * Data provider for test_clean_program_title
     *
     * @return array
     */
    public function clean_program_title_provider() {
        return array(
            array('Panorama'),
            array(' Panorama '),
            // Bracketed GMT/BST timezones should be removed.
            array('(GMT) Panorama'),
            array('(BST) Panorama'),
            array('GMT is the best', 'GMT is the best'),
            array('(UTC) Panorama', '(UTC) Panorama'),
            // Long titles should be shortened (98 chars to prevent word splitting).
            array('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis augue libero, posuere sed nisi eget turpis duis',
                  'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis augue libero, posuere sed nisi eget'),
        );
    }

    /**
     * Test cleaning program title
     *
     * @param string $title
     * @param string $expected
     * @return void
     *
     * @dataProvider clean_program_title_provider
     */
    public function test_clean_program_title($title, $expected = 'Panorama') {
        $cleaned = media_schedule::clean_program_title($title);

        $this->assertEquals($expected, $cleaned);
    }

    /**
     * Data provider for test_clean_episode_title
     *
     * @return array
     */
    public function clean_episode_title_provider() {
        return array(
            array('Panorama', ' episode with space ', 'Episode with space'),
            // Should return empty string when program and episode title are the same, or episode is empty.
            array('Panorama', 'Panorama'),
            array('Panorama', ''),
            // Make sure program title is removed from beginning/end.
            array('Panorama', 'Panorama - Unit Testing', 'Unit Testing'),
            array('Panorama', 'Unit Testing: Panorama', 'Unit Testing'),
            array('Postman Pat', 'Postman Pat does some Testing', 'Does some Testing'),
            // Escape regex characters.
            array('9/11', 'The Truth', 'The Truth'),   
            // Long titles should be shortened (98 chars to prevent word splitting).
            array('Panorama', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis augue libero, posuere sed nisi eget turpis duis',
                  'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis augue libero, posuere sed nisi eget'),         
        );
    }

    /**
     * Test cleaning episode title
     *
     * @param string $program
     * @param string $episode
     * @param string $expected
     * @return void
     *
     * @dataProvider clean_episode_title_provider
     */
    public function test_clean_episode_title($program, $episode, $expected = '') {
        $cleaned = media_schedule::clean_episode_title($program, $episode);

        $this->assertEquals($expected, $cleaned);
    }
}
