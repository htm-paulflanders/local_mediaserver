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
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @id         $Id: privacy_test.php 4833 2018-09-14 12:19:50Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\request\writer,
    \core_privacy\local\request\approved_contextlist,
    \local_mediaserver\privacy\provider;

/**
 * Unit tests for plugin implementation of the Privacy API
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_privacy_testcase extends \core_privacy\tests\provider_testcase {

    /** @var local_mediaserver_generator plugin generator. */
    private $generator;

    /** @var stdClass test user. */
    private $user;

    /*
     * Test setup
     *
     * @return void
     */
    public function setUp() {
        $this->resetAfterTest(true);

        $this->generator = $this->getDataGenerator()->get_plugin_generator('local_mediaserver');

        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
    }

    /**
     * Tests provider get_contexts_for_userid method
     *
     * @return void
     */
    public function test_get_contexts_for_userid() {
        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $this->assertCount(1, $contextlist);

        $expected = context_system::instance();
        $this->assertSame($expected, $contextlist->current());
    }

    /**
     * Test provider export_user_preferences method
     *
     * @return void
     */
    public function test_export_user_preferences() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/user/editlib.php');

        $ids[] = $DB->insert_record('local_mediaserver_channel', (object) ['name' => 'Foo', 'sortorder' => 1, 'hourbegin' => 0, 'hourend' => 24]);
        $ids[] = $DB->insert_record('local_mediaserver_channel', (object) ['name' => 'Bar', 'sortorder' => 3, 'hourbegin' => 0, 'hourend' => 24]);
        $ids[] = $DB->insert_record('local_mediaserver_channel', (object) ['name' => 'Baz', 'sortorder' => 2, 'hourbegin' => 0, 'hourend' => 24]);

        $preference = implode(',', $ids);
        useredit_update_user_preference([
            'id' => $this->user->id,
            'preference_' . provider::PREFERRED_CHANNELS => $preference,
        ]);

        provider::export_user_preferences($this->user->id);

        $writer = writer::with_context(context_system::instance());
        $this->assertTrue($writer->has_any_data());

        $preferences = $writer->get_user_preferences('local_mediaserver');

        $expected = 'Foo, Baz, Bar';
        $this->assertEquals($expected, $preferences->{provider::PREFERRED_CHANNELS}->value);
    }

    /**
     * Test provider export_user_data method exports streams
     *
     * @return void
     */
    public function test_export_user_data_streams() {
        $stream = $this->generator->create_stream(['source' => 'url', 'reference' => 'http://initech.com/okthen.mp4',
            'title' => 'Office Space', 'description' => 'Look here']);

        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $approvedcontextlist = new approved_contextlist($this->user, 'local_mediaserver', $contextlist->get_contextids());

        provider::export_user_data($approvedcontextlist);

        $context = $contextlist->current();
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $contextpath = [get_string('pluginname', 'local_mediaserver'), $stream->id];
        $data = $writer->get_data($contextpath);

        $this->assertEquals($stream->title, $data->title);
        $this->assertEquals($stream->description, $data->description);
        $this->assertEquals(get_string('no'), $data->finished);
        $this->assertNotEmpty($data->timecreated);
    }

    /**
     * Test provider export_user_data method exports favourite programmes
     *
     * @return void
     */
    public function test_export_user_data_favourites() {
        global $DB;

        $favourite = (object) ['userid' => $this->user->id, 'title' => 'Peppa Pig'];
        $DB->insert_record('local_mediaserver_favourite', $favourite);

        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $approvedcontextlist = new approved_contextlist($this->user, 'local_mediaserver', $contextlist->get_contextids());

        provider::export_user_data($approvedcontextlist);

        $context = $contextlist->current();
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $contextpath = [get_string('pluginname', 'local_mediaserver')];
        $data = $writer->get_related_data($contextpath, 'favourites');

        $this->assertCount(1, $data);
        $this->assertEquals($favourite->title, reset($data));
    }

    /**
     * Test provider export_user_data method exports series linked programmes
     *
     * @return void
     */
    public function test_export_user_data_series() {
        global $DB;

        $record = (object) ['userid' => $this->user->id, 'category' => 0, 'title' => 'Peppa Pig', 'series' => 1,
            'format' => 'xx', 'finished' => 0, 'submitted' => time()];
        $DB->insert_record('local_mediaserver_series', $record);

        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $approvedcontextlist = new approved_contextlist($this->user, 'local_mediaserver', $contextlist->get_contextids());

        provider::export_user_data($approvedcontextlist);

        $context = $contextlist->current();
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());

        $contextpath = [get_string('pluginname', 'local_mediaserver')];
        $data = $writer->get_related_data($contextpath, 'series');

        $this->assertCount(1, $data);

        $series = reset($data);
        $this->assertEquals($record->title, $series->title);
        $this->assertEquals($record->series, $series->series);
        $this->assertEquals(get_string('no'), $series->finished);
        $this->assertNotEmpty($series->timecreated);
    }

    /**
     * Test provider export_user_data method user comments
     *
     * @return void
     */
    public function test_export_user_data_comments() {
        $streamone = $this->generator->create_stream(array('source' => 'url', 'reference' => 'http://initech.com/okthen.mp4', 'title' => 'Office Space', 'comments' => 1));
        $commentone = $this->generator->create_comment(array('streamid' => $streamone->id, 'comment' => 'Hello there'));

        // Create two more comments for test user on a new stream, wait a second between each to ensure they are ordered consistently.
        $streamtwo = $this->generator->create_stream(array('source' => 'youtube', 'reference' => '_z-1fTlSDF0', 'title' => 'Singing', 'comments' => 1));
        $commenttwo = $this->generator->create_comment(array('streamid' => $streamtwo->id, 'comment' => 'Happy birthday!'));

        $this->waitForSecond();
        $commentthree = $this->generator->create_comment(array('streamid' => $streamtwo->id, 'comment' => 'Yea'));

        // And a comment from another user (shouldn't be exported).
        $usertwo = $this->getDataGenerator()->create_user();
        $this->setUser($usertwo);
        $commentfour = $this->generator->create_comment(array('streamid' => $streamone->id, 'comment' => 'Me too'));

        // Switch back to test user.
        $this->setUser($this->user);

        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $approvedcontextlist = new approved_contextlist($this->user, 'local_mediaserver', $contextlist->get_contextids());

        provider::export_user_data($approvedcontextlist);

        $context = $contextlist->current();
        $writer = writer::with_context($context);

        $contextpath = array(get_string('pluginname', 'local_mediaserver'), $streamone->id, get_string('commentsubcontext', 'core_comment'));
        $data = $writer->get_data($contextpath);
        $this->assertTrue($writer->has_any_data());

        $this->assertCount(1, $data->comments);

        $expected = strip_tags($commentone->content);
        $this->assertEquals($expected, $data->comments[0]->content);

        // Get comments for second stream.
        $contextpath = array(get_string('pluginname', 'local_mediaserver'), $streamtwo->id, get_string('commentsubcontext', 'core_comment'));
        $data = $writer->get_data($contextpath);
        $this->assertTrue($writer->has_any_data());

        $this->assertCount(2, $data->comments);

        // Comments are ordered by time created descending, so we should get the last one first.
        $expected = strip_tags($commentthree->content);
        $this->assertEquals($expected, $data->comments[0]->content);

        $expected = strip_tags($commenttwo->content);
        $this->assertEquals($expected, $data->comments[1]->content);
    }

    /**
     * Tests provider delete_data_for_all_users_in_context method
     *
     * @return void
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        $DB->insert_record('local_mediaserver_favourite', (object) ['userid' => $this->user->id, 'title' => 'Peppa Pig']);

        $stream = $this->generator->create_stream(array('source' => 'url', 'reference' => 'http://initech.com/okthen.mp4', 'title' => 'Office Space', 'comments' => 1));
        $this->generator->create_comment(array('streamid' => $stream->id, 'comment' => 'Hello there'));

        $context = context_system::instance();

        provider::delete_data_for_all_users_in_context($context);

        $this->assertFalse($DB->record_exists('local_mediaserver_favourite', []));
        $this->assertFalse($DB->record_exists('comments', ['contextid' => $context->id, 'component' => 'local_mediaserver']));
    }

    /**
     * Tests provider delete_data_for_user method
     *
     * @return void
     */
    public function test_delete_data_for_user() {
        global $DB;

        $DB->insert_record('local_mediaserver_favourite', (object) ['userid' => $this->user->id, 'title' => 'Peppa Pig']);

        $stream = $this->generator->create_stream(array('source' => 'url', 'reference' => 'http://initech.com/okthen.mp4', 'title' => 'Office Space', 'comments' => 1));
        $this->generator->create_comment(array('streamid' => $stream->id, 'comment' => 'Hello there'));

        // Create a second user, ensure their data is preserved.
        $usertwo = $this->getDataGenerator()->create_user();
        $DB->insert_record('local_mediaserver_favourite', (object) ['userid' => $usertwo->id, 'title' => 'Paw Patrol']);

        $this->setUser($usertwo);
        $commenttwo = $this->generator->create_comment(array('streamid' => $stream->id, 'comment' => 'Happy birthday!'));

        // Switch back to test user.
        $this->setUser($this->user);

        $contextlist = provider::get_contexts_for_userid($this->user->id);
        $context = $contextlist->current();

        $approvedcontextlist = new approved_contextlist($this->user, 'local_mediaserver', $contextlist->get_contextids());
        provider::delete_data_for_user($approvedcontextlist);

        $this->assertFalse($DB->record_exists('local_mediaserver_favourite', ['userid' => $this->user->id]));
        $this->assertFalse($DB->record_exists('comments',
            ['contextid' => $context->id, 'component' => 'local_mediaserver', 'userid' => $this->user->id]));

        // Data for second user should still exist.
        $this->assertTrue($DB->record_exists('local_mediaserver_favourite', ['userid' => $usertwo->id]));
        $this->assertTrue($DB->record_exists('comments', ['id' => $commenttwo->id]));
    }
}
