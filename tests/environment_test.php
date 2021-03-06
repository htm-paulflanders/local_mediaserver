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
 * @id         $Id: environment_test.php 4481 2016-12-06 15:59:50Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/environmentlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');

/**
 * Unit test for plugin environment methods
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_environment_testcase extends advanced_testcase {

    /**
     * Test setup
     *
     * @return void
     */
    protected function setUp() {
        $this->resetAfterTest(true);

        $this->setAdminUser();
    }

    /**
     * Test environment_web_service method
     *
     * @return void
     */
    public function test_environment_web_service() {
        global $DB, $USER;

        $result = new environment_results('custom_check');

        // Disable web services should fail the environment check.
        set_config('enablewebservices', 0);

        $result = local_mediaserver_environment_web_service($result);
        $this->assertInstanceOf('environment_results', $result);
        $this->assertFalse($result->getStatus());

        // Enable web services, configure host.
        set_config('enablewebservices', 1);
        set_config('host', 'localhost', 'local_mediaserver');

        // Add token.
        $service = $DB->get_record('external_services',
            array('component' => 'local_mediaserver', 'shortname' => 'local_mediaserver_ws', 'enabled' => 1));
        external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $USER->id, context_system::instance(), 0, '127.0.0.1');

        // Add user.
        $serviceuser = new stdClass();
        $serviceuser->externalserviceid = $service->id;
        $serviceuser->userid = $USER->id;
        (new webservice())->add_ws_authorised_user($serviceuser);

        $success = local_mediaserver_environment_web_service($result);
        $this->assertNull($success);

        // Change to invalid host.
        set_config('host', 'invalid.tld', 'local_mediaserver');

        $result = local_mediaserver_environment_web_service($result);
        $this->assertInstanceOf('environment_results', $result);
        $this->assertFalse($result->getStatus());
    }
}
