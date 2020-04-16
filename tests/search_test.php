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
 * @id         $Id: search_test.php 4750 2018-05-31 13:27:33Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/search/tests/fixtures/testable_core_search.php');

/**
 * Unit test for base media server global search
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_search_testcase extends advanced_testcase {

    /** @var \local_mediaserver\search\media search area instance. */
    private $searcharea;

    /** @var stdClass test user. */
    private $user;

    /** @var stdClass test stream. */
    private $stream;

    /**
     * Test setup, reset DB and create search area
     *
     * @return void
     */
    public function setUp() {
        $this->resetAfterTest(true);

        set_config('enableglobalsearch', true);

        // Set \core_search::instance to the mock_search_engine as we don't require the search engine to be working to test this.
        testable_core_search::instance();

        $areaid = \core_search\manager::generate_areaid('local_mediaserver', 'media');
        $this->searcharea = \core_search\manager::get_search_area($areaid);

        $this->assertInstanceOf('\local_mediaserver\search\media', $this->searcharea);

        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);

        $generator = $this->getDataGenerator()->get_plugin_generator('local_mediaserver');
        $this->stream = $generator->create_stream(
            array('source' => 'url', 'reference' => 'http://initech.com/okthen.mp4', 'title' => 'Office Space', 'description' => '<p>This is cool</p>')
        );
    }

    /**
     * Test document indexing
     *
     * @return void
     */
    public function test_media_indexing() {
        $recordset = $this->searcharea->get_recordset_by_timestamp(0);
        $this->assertTrue($recordset->valid());

        $nrecords = 0;
        foreach ($recordset as $record) {
            $this->assertInstanceOf('stdClass', $record);

            $document = $this->searcharea->get_document($record);
            $this->assertInstanceOf('\core_search\document', $document);

            $nrecords++;
        }

        $recordset->close();
        $this->assertEquals(1, $nrecords);

        // No new records (the +2 is to prevent race conditions).
        $recordset = $this->searcharea->get_recordset_by_timestamp(time() + 2);
        $this->assertFalse($recordset->valid());

        $recordset->close();
    }

    /**
     * Test document access
     *
     * @return void
     */
    public function test_media_access() {
        global $DB;

        $this->assertEquals(\core_search\manager::ACCESS_DELETED, $this->searcharea->check_access(-123));

        // Can't access it yet, because it isn't done.
        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $this->searcharea->check_access($this->stream->id));

        $DB->set_field('local_mediaserver_stream', 'done', 1, array('id' => $this->stream->id));
        $this->assertEquals(\core_search\manager::ACCESS_GRANTED, $this->searcharea->check_access($this->stream->id));

        // Prohibit user from accessing media streams.
        $userrole = $DB->get_record('role', array('shortname' => 'user'), '*', MUST_EXIST);
        role_change_permission($userrole->id, context_system::instance(), 'local/mediaserver:view', CAP_PREVENT);

        $this->assertEquals(\core_search\manager::ACCESS_DENIED, $this->searcharea->check_access($this->stream->id));
    }

    /**
     * Test document contents
     *
     * @return void
     */
    public function test_media_document() {
        $document = $this->searcharea->get_document($this->stream);
        $this->assertInstanceOf('\core_search\document', $document);

        $expectedid = $this->searcharea->get_area_id() . '-' . $this->stream->id;
        $this->assertEquals($expectedid, $document->get('id'));

        $this->assertEquals(context_system::instance()->id, $document->get('contextid'));
        $this->assertEquals(SITEID, $document->get('courseid'));
        $this->assertEquals($this->stream->id, $document->get('itemid'));
        $this->assertEquals(content_to_text($this->stream->title, false), $document->get('title'));
        $this->assertEquals(content_to_text($this->stream->description, false), $document->get('content'));
        $this->assertEquals($this->stream->userid, $document->get('userid'));
        $this->assertEquals(\core_search\manager::NO_OWNER_ID, $document->get('owneruserid'));
        $this->assertEquals($this->stream->submitted, $document->get('modified'));
    }

    /**
     * Test document URL
     *
     * @return void
     */
    public function test_media_document_url() {
        $document = $this->searcharea->get_document($this->stream);

        $url = $this->searcharea->get_doc_url($document);
        $this->assertInstanceOf('\local_mediaserver\url', $url);

        $expected = new \local_mediaserver\url('/local/mediaserver/view.php', array('id' => $document->get('itemid')));
        $this->assertEquals($expected, $url);
    }

    /**
     * Test document context URL
     *
     * @return void
     */
    public function test_media_document_context_url() {
        $document = $this->searcharea->get_document($this->stream);

        $url = $this->searcharea->get_context_url($document);
        $this->assertInstanceOf('\local_mediaserver\url', $url);

        $expected = new \local_mediaserver\url('/local/mediaserver/index.php', array('id' => $this->stream->category));
        $this->assertEquals($expected, $url);
    }
}
