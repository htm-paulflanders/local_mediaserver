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
 * @id         $Id: favourite.php 4558 2017-03-02 13:50:39Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');

$id = required_param('id', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_LOCALURL);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$program = $DB->get_record('local_mediaserver_program', array('id' => $id), '*', MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/index.php'));

require_sesskey();

if (! $DB->record_exists('local_mediaserver_favourite', array('userid' => $USER->id, 'title' => $program->title))) {
    $record = new stdClass;
    $record->userid = $USER->id;
    $record->title = $program->title;

    $DB->insert_record('local_mediaserver_favourite', $record);
}

// Send user back to where they came from.
redirect(new local_mediaserver_url($returnurl, null, 'p' . $program->id));
