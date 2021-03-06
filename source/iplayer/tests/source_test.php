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
 * @id         $Id: source_test.php 4825 2018-08-23 09:31:41Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use \mediasource_iplayer\definition;

global $CFG;
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * Unit test for media source reference matching
 *
 * @group greenhead
 * @group local_mediaserver
 */
class mediasource_iplayer_source_testcase extends advanced_testcase {

    /** @const string Source URL. */
    const SOURCE_URL = 'http://www.bbc.co.uk/iplayer/episode/b05xc96x/traffic-cops-series-14-1-rural-raiders';

    /** @var \mediasource_iplayer\definition Source instance. */
    private $source;

    /**
     * Create source instance
     *
     * @return void
     */
    public function setUp() {
        $this->source = local_mediaserver_source_reference(self::SOURCE_URL);
    }

    /**
     * Tests source instance type
     *
     * @return void
     */
    public function test_instance() {
        $this->assertInstanceOf(definition::class, $this->source);
    }

    /**
     * Tests get_source_type
     *
     * @return void
     */
    public function test_get_source_type() {
        $this->assertEquals('iplayer', $this->source->get_source_type());
    }

    /**
     * Tests get_reference
     *
     * @return void
     */
    public function test_get_reference() {
        $this->assertEquals('b05xc96x', $this->source->get_reference());
    }

    /**
     * Tests get_job
     *
     * @return void
     */
    public function test_get_job() {
        $stream = new stdClass;
        $stream->code = 'abc12';
        $stream->reference = $this->source->get_reference();
        $stream->title = 'Traffic Cops';

        $expected = "./get_iplayer_episode.sh -id 'abc12' -vid 'b05xc96x' -title 'Traffic Cops'";
        $this->assertEquals($expected, $this->source->get_job($stream));
    }

    /**
     * Data provider for test_get_reference_matches
     *
     * @return array
     */
    public function get_reference_matches_provider() {
        return array(
            // TV episode.
            array('http://www.bbc.co.uk/iplayer/episode/b05xc96x/traffic-cops-series-14-1-rural-raiders', 'b05xc96x'),
            // Radio programme.
            array('http://www.bbc.co.uk/programmes/p02p1sqt', 'p02p1sqt'),
            // Expanded PIDs.
            array('https://www.bbc.co.uk/programmes/w3csvtwr', 'w3csvtwr'),
            array('https://www.bbc.co.uk/programmes/b0b2gspd', 'b0b2gspd'),
            // Trailing anchor/params.
            array('https://www.bbc.co.uk/programmes/b04p270w#play', 'b04p270w'),
            array('https://www.bbc.co.uk/programmes/b04p270w?foo=bar', 'b04p270w'),
        );
    }

    /**
     * Tests source matching against URL
     *
     * @param string $url
     * @param string $expected
     * @return void
     *
     * @dataProvider get_reference_matches_provider
     */
    public function test_get_reference_matches($url, $expected) {
        $instance = new definition($url);

        $this->assertEquals($expected, $instance->get_reference());
    }

    /**
     * Data provider for test_get_reference_none_matches
     *
     * @return array
     */
    public function get_reference_none_matches_provider() {
        return array(
            // Too long.
            array('http://www.bbc.co.uk/iplayer/episode/b05xc96xx/traffic-cops-series-14-1-rural-raiders'),
            // Too short.
            array('http://www.bbc.co.uk/programmes/p02p1sq'),
            // Invalid PIDs.
            array('https://www.bbc.co.uk/programmes/e3csvtwr'),
            array('https://www.bbc.co.uk/programmes/o0b2gspd'),
        );
    }

    /**
     * Tests source matching against non-matching URL
     *
     * @param string $url
     * @return void
     *
     * @dataProvider get_reference_none_matches_provider
     */
    public function test_get_reference_none_matches($url) {
        $this->assertFalse((new definition($url))->get_reference());
    }
}
