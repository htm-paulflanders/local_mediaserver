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
 * @id         $Id: media_source_test.php 4444 2016-10-28 09:27:51Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

use \local_mediaserver\media_source;

global $CFG;
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * Unit test for base media source class/creating jobs
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_media_source_testcase extends advanced_testcase {

    /**
     * Data provider for test_safe_job
     *
     * @return array
     */
    public function safe_job_provider() {
        return array(
            // Check script names are sanitized.
            array('foo.sh;id', null, 'foo.sh\;id'),
            array('foo.sh && id >out', null, 'foo.sh \&\& id \>out'),
            // Check script names containins quotes.
            array("fo''o.sh", null, "fo''o.sh"),
            array("fo'o.sh", null, "fo\'o.sh"),
            // Check arguments are quoted.
            array('foo.sh', array('bar' => 'baz'), "foo.sh -bar 'baz'"),
            array('foo.sh', array('bar' => "o'baz"), "foo.sh -bar 'o'\''baz'"),
        );
    }

    /**
     * Test sanitizing job data
     *
     * @param string $script
     * @param array $arguments
     * @param string $expected
     * @return void
     *
     * @dataProvider safe_job_provider
     */
    public function test_safe_job($script, array $arguments = null, $expected) {
        $command = media_source::safe_job($script, (array) $arguments);

        $this->assertEquals($expected, $command);
    }

    /**
     * Test creating jobs
     *
     * @return void
     */
    public function test_create_job() {
        global $CFG;

        $filename = $content = __FUNCTION__;
        local_mediaserver_create_job($filename, $content);

        $expectedfile = $CFG->dataroot . '/local_mediaserver/' . $filename;
        $this->assertFileExists($expectedfile);

        $filecontent = file_get_contents($expectedfile);
        $this->assertEquals($content, $filecontent);
    }
}
