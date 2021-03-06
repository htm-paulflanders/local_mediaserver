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
 * @id         $Id: source_test.php 4443 2016-10-27 12:17:29Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * Unit test for media source reference matching
 *
 * @group greenhead
 * @group local_mediaserver
 */
class mediasource_youtube_source_testcase extends advanced_testcase {

    /** @const string Source URL. */
    const SOURCE_URL = 'http://youtube.com/watch?v=FHCYHldJi_g';

    /** @var \mediasource_youtube\definition Source instance. */
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
        $this->assertInstanceOf(\mediasource_youtube\definition::class, $this->source);
    }

    /**
     * Tests get_source_type
     *
     * @return void
     */
    public function test_get_source_type() {
        $this->assertEquals('youtube', $this->source->get_source_type());
    }

    /**
     * Tests get_reference
     *
     * @return void
     */
    public function test_get_reference() {
        $this->assertEquals('FHCYHldJi_g', $this->source->get_reference());
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
        $stream->title = 'Duke Dumont - I Got U';

        $expected = "./get_youtube.sh -id 'abc12' -vid 'FHCYHldJi_g' -title 'Duke Dumont - I Got U'";
        $this->assertEquals($expected, $this->source->get_job($stream));
    }

    /**
     * Data provider for test_get_references
     *
     * @return array
     */
    public function get_references_provider() {
        return array(
            array('https://www.youtube.com/v/FHCYHldJi_g'),
            array('http://www.youtube.com/watch?v=FHCYHldJi_g&list=RDpUjE9H8QlA4#t=0'),
            array('https://www.youtube.com/watch?feature=player_detailpage&v=FHCYHldJi_g'),
            array('http://m.youtube.com/watch?v=FHCYHldJi_g'),
            array('http://youtube.com/watch?v=FHCYHldJi_g'),
            array('http://youtube.com/embed/FHCYHldJi_g'),
            array('https://youtu.be/FHCYHldJi_g'),
            array('https://www.youtube-nocookie.com/embed/FHCYHldJi_g'),
        );
    }

    /**
     * Tests source matching against URL
     *
     * @param string $url
     * @return void
     *
     * @dataProvider get_references_provider
     */
    public function test_get_references($url) {
        $instance = new \mediasource_youtube\definition($url);

        $expected = 'FHCYHldJi_g';
        $this->assertEquals($expected, $instance->get_reference());
    }
}
