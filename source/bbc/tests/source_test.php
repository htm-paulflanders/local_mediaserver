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
 * @id         $Id: source_test.php 4603 2017-05-04 14:28:43Z pholden $
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
class mediasource_bbc_source_testcase extends advanced_testcase {

    /** @const string Source URL. */
    const SOURCE_URL = 'http://www.bbc.co.uk/news/world-europe-32925637';

    /** @var \mediasource_bbc\definition Source instance. */
    private $source;

    /**
     * Create source instance
     *
     * @return void
     */
    public function setUp() {
        $this->resetAfterTest(true);

        $this->source = local_mediaserver_source_reference(self::SOURCE_URL);
    }

    /**
     * Tests source instance type
     *
     * @return void
     */
    public function test_instance() {
        $this->assertInstanceOf(\mediasource_bbc\definition::class, $this->source);
    }

    /**
     * Tests get_source_type
     *
     * @return void
     */
    public function test_get_source_type() {
        $this->assertEquals('bbc', $this->source->get_source_type());
    }

    /**
     * Data provider for test_get_reference
     *
     * @return array
     */
    public function get_reference_provider() {
        return array(
            array(self::SOURCE_URL),
            array(self::SOURCE_URL . '?foo=bar'),
            array('http://www.bbc.co.uk/news/av/world-europe-32925637'),
            array('http://www.bbc.co.uk/news/av/world-europe-32925637/sepp_blatter_disgrace'),
        );
    }

    /**
     * Tests get_reference
     *
     * @param string $url
     * @return void
     *
     * @dataProvider get_reference_provider
     */
    public function test_get_reference($url) {
        $expected = 'world-europe-32925637';
        $instance = new \mediasource_bbc\definition($url);

        $this->assertEquals($expected, $instance->get_reference());
    }

    /**
     * Tests stream upgrade to match source
     *
     * @return void
     */
    public function test_stream_upgrade() {
        global $CFG, $DB;

        $stream = local_mediaserver_stream_add(LOCAL_MEDIASERVER_SOURCE_DEFAULT, self::SOURCE_URL, 'Fifa: It\'s corrupt', '', 1, 1);

        $sourcetype = $this->source->get_source_type();
        local_mediaserver_source_upgrade($sourcetype, '^https?://www.bbc.co.uk/news/');

        // Get upgraded stream record.
        $stream = $DB->get_record('local_mediaserver_stream', array('id' => $stream->id), '*', MUST_EXIST);
        $this->assertEquals($sourcetype, $stream->source);
        $this->assertEquals('world-europe-32925637', $stream->reference);

        // Upgrade job should be created.
        $streamjob = $CFG->dataroot . '/local_mediaserver/' . $stream->code . '.job';
        $this->assertFileExists($streamjob);
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
        $stream->title = 'Fifa: It\'s Corrupt';

        $expected = "./get_bbc_news.sh -id 'abc12' -vid 'world-europe-32925637' -title 'Fifa: It'\''s Corrupt'";
        $this->assertEquals($expected, $this->source->get_job($stream));
    }
}
