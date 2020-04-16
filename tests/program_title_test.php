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
 * @id         $Id: program_title_test.php 3927 2015-11-02 12:37:53Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

/**
 * Unit test for program title formatting methods
 *
 * @group greenhead
 * @group local_mediaserver
 */
class local_mediaserver_program_title_testcase extends advanced_testcase {

    /**
     * Test local_mediaserver_program_format_extract method
     *
     * @return void
     */
    public function test_format_extract() {
        $program = new stdClass;
        $program->title = 'The Simpsons';
        $program->series = 9;
        $program->episode = 4;
        $program->episodetitle = 'Treehouse of Horror VIII';

        $format = '%title - Series %series: %episode. %episodetitle';
        $fields = local_mediaserver_program_format_extract($program, $format);

        $expected = array('%title' => $program->title, '%series' => $program->series, '%episode' => $program->episode, '%episodetitle' => $program->episodetitle);
        $this->assertEquals($expected, $fields);
    }

    /**
     * Test local_mediaserver_program_format_extract method with invalid format string
     *
     * @return void
     */
    public function test_format_extract_invalid() {
        $program = new stdClass;
        $program->title = 'The Simpsons';

        $format = '%title - %invalid';
        $fields = local_mediaserver_program_format_extract($program, $format);

        $this->assertFalse($fields);
    }

    /**
     * Test methods for generating program titles, for each program we check:
     *      1)  The default format string is appropriate for program fields
     *      2)  The default title is also appropriate for program fields
     *      3)  Populating a title with a given format is correct
     *
     * @return void
     */
    public function test_program_populate() {
        // Minimum amount of program information.
        $program = new stdClass;
        $program->title = 'The Simpsons';
        $program->series = 0;
        $program->episode = 0;
        $program->episodetitle = '';

        $expected = '%title';
        $format = local_mediaserver_program_format_default($program);
        $this->assertEquals($expected, $format);

        $expected = 'The Simpsons';
        $this->assertEquals($expected, local_mediaserver_program_title_default($program));
        $this->assertEquals($expected, local_mediaserver_program_title_populate($program, $format));

        // Add initial series information.
        $program->series = 1;
        $program->episode = 4;

        $expected = '%title: Episode %episode';
        $format = local_mediaserver_program_format_default($program);
        $this->assertEquals($expected, $format);

        $expected = 'The Simpsons: Episode 4';
        $this->assertEquals($expected, local_mediaserver_program_title_default($program));
        $this->assertEquals($expected, local_mediaserver_program_title_populate($program, $format));

        // Bump series number.
        $program->series = 9;

        $expected = '%title - Series %series: Episode %episode';
        $format = local_mediaserver_program_format_default($program);
        $this->assertEquals($expected, $format);

        $expected = 'The Simpsons - Series 9: Episode 4';
        $this->assertEquals($expected, local_mediaserver_program_title_default($program));
        $this->assertEquals($expected, local_mediaserver_program_title_populate($program, $format));

        // Now add an episode title.
        $program->episodetitle = 'Treehouse of Horror VIII';

        $expected = '%title - Series %series: %episode. %episodetitle';
        $format = local_mediaserver_program_format_default($program);
        $this->assertEquals($expected, $format);

        $expected = 'The Simpsons - Series 9: 4. Treehouse of Horror VIII';
        $this->assertEquals($expected, local_mediaserver_program_title_default($program));
        $this->assertEquals($expected, local_mediaserver_program_title_populate($program, $format));

        // Remove series information.
        $program->series = 0;
        $program->episode = 0;

        $expected = '%title: %episodetitle';
        $format = local_mediaserver_program_format_default($program);
        $this->assertEquals($expected, $format);

        $expected = 'The Simpsons: Treehouse of Horror VIII';
        $this->assertEquals($expected, local_mediaserver_program_title_default($program));
        $this->assertEquals($expected, local_mediaserver_program_title_populate($program, $format));
    }

    /**
     * Test fallback when populating program title with invalid/missing placeholders in format string
     *
     * @return void
     */
    public function test_program_populate_fallback() {
        $program = new stdClass;
        $program->title = 'The Simpsons';
        $program->series = 4;
        $program->episode = 9;
        $program->episodetitle = '';

        $expected = 'The Simpsons - Series 4: Episode 9';

        // Format string contains an invalid placeholder.
        $format = '%title - Series %series: %episode. %invalid';
        $title = local_mediaserver_program_title_populate($program, $format);

        $this->assertEquals($expected, $title);

        // Format string contains a valid placeholder, but it's empty.
        $format = '%title - Series %series: %episode. %episodetitle';
        $title = local_mediaserver_program_title_populate($program, $format);

        $this->assertEquals($expected, $title);
    }
}
