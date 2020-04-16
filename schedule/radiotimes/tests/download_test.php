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
 * @id         $Id: download_test.php 4033 2016-01-07 16:02:45Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use local_mediaserver\phpunit\schedule_download_testcase;

/**
 * Unit test for Radio Times schedule download method
 *
 * @group greenhead
 * @group local_mediaserver
 */
class mediaschedule_radiotimes_download_testcase extends schedule_download_testcase {

    /**
     * Tests schedule download method using fixtures data
     *
     * @return void
     */
    public function test_download() {
        $class = '\\mediaschedule_radiotimes\\definition';
        $fixture = __DIR__ . '/fixtures/132.txt';
        $start = 1435251600; // 25/06/15 18:00 BST.

        // The endpoint doesn't support start/finish natively, the class should do this filtering itself.
        $schedule = $this->download($class, $fixture, $start, $start + (HOURSECS * 6));
        $this->assertEquals(6, count($schedule));

        $expected = array(
            1435251600 => ['The Simpsons', 'My Mother the Carjacker'],
            1435253400 => ['Hollyoaks', ''],
            1435255200 => ['Channel 4 News', ''],
            1435258800 => ['Dogs: Their Secret Lives', ''],
            1435262400 => ['The Tribe', ''],
            1435266000 => ['Peter Kay: Live & Back on Nights!', 'Peter Kay: Live & Back on Nights! Part Two'],
        );
        foreach ($expected as $timebegin => $titles) {
            $program = array_shift($schedule);

            $this->assertEquals($timebegin, $program->timebegin);
            $this->assertEquals($titles[0], $program->title);
            $this->assertEquals($titles[1], $program->episodetitle);
        }
    }
}
