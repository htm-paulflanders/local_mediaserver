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
 * @id         $Id: external_test.php 4751 2018-05-31 13:32:55Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use \local_mediaserver\external;

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/files/externallib.php');

/**
 * Unit test for plugin external functions
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_external_testcase extends externallib_advanced_testcase {

    /** @var stdClass test user. */
    private $user;

    /**
     * Test setup, reset DB and create test user
     *
     * @return void
     */
    protected function setUp() {
        $this->resetAfterTest(true);

        $this->user = $this->getDataGenerator()->create_user();
        $this->context = context_user::instance($this->user->id, MUST_EXIST);

        $this->setUser($this->user);
    }

    /**
     * Test update_series method
     *
     * @return void
     */
    public function test_update_series() {
        global $DB;

        $series = new stdClass;
        $series->userid = $this->user->id;
        $series->category = 1;
        $series->title = 'Something';
        $series->series = 1;
        $series->format = 'Something %episode';
        $series->finished = 0;
        $series->submitted = time();

        $series->id = $DB->insert_record('local_mediaserver_series', $series);

        $context = context_system::instance();

        // Ensure user can update their own series.
        $roleid = self::assignUserCapability('local/mediaserver:add', $context->id);
        $sink = $this->redirectEvents();

        $finished = true;
        $result = external::clean_returnvalue(external::update_series_returns(),
            external::update_series($series->id, $finished)
        );

        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertInstanceOf('\local_mediaserver\event\series_updated', $event);
        $this->assertEquals($context, $event->get_context());
        $this->assertEquals($series->id, $event->objectid);
        $this->assertEquals($this->user->id, $event->relateduserid);
        $this->assertEquals($finished, $event->other['finished']);
        $this->assertEventLegacyLogData(null, $event);

        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_mediaserver_series', array('id' => $series->id, 'finished' => $finished)));

        // Ensure they can't update other users series.
        $useridupdate = ($this->user->id + 1);
        $DB->set_field('local_mediaserver_series', 'userid', $useridupdate, array('id' => $series->id));

        $notfinished = !$finished;
        try {
            external::update_series($series->id, $notfinished);
            $this->fail('dml_missing_record_exception expected');
        } catch (moodle_exception $ex) {
            $this->assertInstanceOf('dml_missing_record_exception', $ex);

            // Make sure the series wasn't updated.
            $this->assertEquals($finished, $DB->get_field('local_mediaserver_series', 'finished', array('id' => $series->id)));
        }

        // Ensure user can update streams of other users with appropriate capability.
        self::assignUserCapability('local/mediaserver:edit', $context->id, $roleid);

        $sink = $this->redirectEvents();

        $result = external::clean_returnvalue(external::update_series_returns(),
            external::update_series($series->id, $notfinished)
        );

        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertInstanceOf('\local_mediaserver\event\series_updated', $event);
        $this->assertEquals(context_system::instance(), $event->get_context());
        $this->assertEquals($series->id, $event->objectid);
        $this->assertEquals($useridupdate, $event->relateduserid);
        $this->assertEquals($notfinished, $event->other['finished']);
        $this->assertEventLegacyLogData(null, $event);

        $this->assertTrue($result);
        $this->assertTrue($DB->record_exists('local_mediaserver_series', array('id' => $series->id, 'finished' => $notfinished)));

        // Ensure invalid series can't be updated.
        try {
            external::update_series($series->id + 1, $finished);
            $this->fail('dml_missing_record_exception expected');
        } catch (moodle_exception $ex) {
            $this->assertInstanceOf('dml_missing_record_exception', $ex);
        }
    }

    /**
     * Test media_complete & media_update methods
     *
     * @return void
     */
    public function test_media_complete_and_update() {
        global $DB;
    
        $systemcontext = context_system::instance();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_mediaserver');
        $stream = $generator->create_stream(array('source' => 'url', 'reference' => 'http://initech.com/okthen.mp4', 'title' => 'Office Space'));

        // Create some draft frame files.
        $draftid = file_get_unused_draft_itemid();

        for ($i = 1; $i <= 2; $i++) {
            $fixture = __DIR__ . '/fixtures/f' . $i . '.png';

            $filename = basename($fixture);
            $content = base64_encode(file_get_contents($fixture));

            core_files_external::upload($this->context->id, 'user', 'draft', $draftid, '/', $filename, $content, null, null);
        }

        // Catch triggered events.
        $sink = $this->redirectEvents();

        $result = external::clean_returnvalue(external::media_complete_returns(),
            external::media_complete($stream->code, $draftid)
        );

        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $event = reset($events);

        $this->assertInstanceOf('\local_mediaserver\event\media_completed', $event);
        $this->assertEquals($systemcontext, $event->get_context());
        $this->assertEquals($stream->id, $event->objectid);
        $this->assertEquals($this->user->id, $event->relateduserid);
        $this->assertEventLegacyLogData(null, $event);

        // Pass event to observer method.
        $sink = $this->redirectMessages();

        \local_mediaserver\observers::media_completed($event);

        $messages = $sink->get_messages();
        $sink->close();

        $this->assertCount(1, $messages);
        $message = reset($messages);

        $this->assertEquals(\core_user::get_support_user()->id, $message->useridfrom);
        $this->assertEquals($this->user->id, $message->useridto);
        $this->assertEquals($stream->title, $message->subject);
        $this->assertEquals($stream->title, $message->smallmessage);

        $expectedurl = new \local_mediaserver\url('/local/mediaserver/view.php', array('id' => $stream->id));
        $this->assertEquals($expectedurl, $message->contexturl);

        // Ensure external call was successful and updated stream record.
        $this->assertTrue($result);
        $this->assertEquals(1, $DB->get_field('local_mediaserver_stream', 'done', array('id' => $stream->id)));

        // Let's see if frames were saved correctly.
        $fs = get_file_storage();
        $frames = $fs->get_area_files($systemcontext->id, 'local_mediaserver', 'frame', $stream->id, 'filename', false);
        $this->assertCount(2, $frames);

        list($f1, $f2) = array_values($frames);
        $this->assertEquals('f1.png', $f1->get_filename());
        $this->assertEquals(86158, $f1->get_filesize());

        $this->assertEquals('f2.png', $f2->get_filename());
        $this->assertEquals(17219, $f2->get_filesize());

        // Let's update the frames (we'll need a new draft area).
        $newdraftid = file_get_unused_draft_itemid();

        for ($i = 1; $i <= 2; $i++) {
            $filename = 'f' . $i . '.png';
            $content = base64_encode('Frame ' . $i);

            core_files_external::upload($this->context->id, 'user', 'draft', $newdraftid, '/', $filename, $content, null, null);
        }

        $result = external::clean_returnvalue(external::media_update_returns(),
            external::media_update($stream->code, $newdraftid)
        );

        $this->assertTrue($result);

        // Let's see if frames were updated correctly.
        $frames = $fs->get_area_files($systemcontext->id, 'local_mediaserver', 'frame', $stream->id, 'filename', false);
        $this->assertCount(2, $frames);

        list($f1, $f2) = array_values($frames);
        $this->assertEquals('f1.png', $f1->get_filename());
        $this->assertEquals(7, $f1->get_filesize());

        $this->assertEquals('f2.png', $f2->get_filename());
        $this->assertEquals(7, $f2->get_filesize());
    }

    /**
     * Test media_complete method with invalid frames
     *
     * @return void
     */
    public function test_media_complete_invalid_frames() {
        global $DB;

        $generator = $this->getDataGenerator()->get_plugin_generator('local_mediaserver');
        $stream = $generator->create_stream(array('source' => 'youtube', 'reference' => 'Fy3rjQGc6lA', 'title' => 'Office Space'));

        // Create some invalid draft frame files.
        $draftfile = core_files_external::clean_returnvalue(core_files_external::upload_returns(),
            core_files_external::upload($this->context->id, 'user', 'draft', 0, '/', 'f1.png', 'I\'m a little teapot', null, null)
        );

        $draftid = $draftfile['itemid'];

        try {
            external::media_complete($stream->code, $draftid);
            $this->fail('invalid_parameter_exception expected');
        } catch (moodle_exception $ex) {
            $this->assertInstanceOf('invalid_parameter_exception', $ex);
            $this->assertContains('draft area invalid file count', $ex->getMessage());
        }

        // Add another (invalid) file to the same user draft filearea.
        core_files_external::upload($this->context->id, 'user', 'draft', $draftid, '/', 'nowai.jpg', 'No wai!', null, null);

        try {
            external::media_complete($stream->code, $draftid);
            $this->fail('invalid_parameter_exception expected');
        } catch (moodle_exception $ex) {
            $this->assertInstanceOf('invalid_parameter_exception', $ex);
            $this->assertContains('draft area invalid file names', $ex->getMessage());
        }
    }
}
