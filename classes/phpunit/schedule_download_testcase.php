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
 * @id         $Id: schedule_download_testcase.php 4434 2016-10-17 14:13:39Z pholden $
 */

namespace local_mediaserver\phpunit;

defined('MOODLE_INTERNAL') || die();

abstract class schedule_download_testcase extends \advanced_testcase {

    /**
     * Helper method for creating mock schedule instance
     *
     * @param string $class
     * @param array $methodreturns Array of ['methodname' => 'returnvalue'] to mock
     * @return object
     */
    protected function mockScheduleInstance($class, array $methodreturns) {
        $this->assertTrue(class_exists($class));

        $methods = array_keys($methodreturns);
        $schedule = $this->getMockBuilder($class)->setMethods($methods)->getMock();

        foreach ($methods as $method) {
            $schedule->expects($this->once())->method($method)->will(
                $this->returnValue($methodreturns[$method])
            );
        }

        return $schedule;
    }

    /**
     * Helper method to return programs from schedule instance (normally a protected property).
     *
     * @param object $schedule
     * @return array
     */
    protected function getSchedulePrograms($schedule) {
        $programs = new \ReflectionProperty($schedule, 'programs');
        $programs->setAccessible(true);

        return $programs->getValue($schedule);
    }

    /**
     * Call the download method of a schedule class, receiving fixture file instead of querying remote endpoint
     *
     * @param string $class
     * @param string $fixture
     * @param int $start
     * @param int $finish
     * @return array
     */
    public function download($class, $fixture, $start = 0, $finish = 0) {
        $this->assertFileExists($fixture);

        // Create schedule instance to mock the 'request' method.
        $schedule = $this->mockScheduleInstance($class, array('request' => file_get_contents($fixture)));
        $schedule->download(null, $start, $finish);

        return $this->getSchedulePrograms($schedule);
    }
}
