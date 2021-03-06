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
 * @id         $Id: popup_ajax.php 4557 2017-03-02 13:12:41Z pholden $
 */

define('AJAX_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

$id = required_param('id', PARAM_INT);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

echo $OUTPUT->header();

$userfields = get_all_user_name_fields(true, 'u');

$sql = 'SELECT s.title, s.submitted, s.userid, ' . $userfields . '
          FROM {local_mediaserver_stream} s
          JOIN {local_mediaserver_recording} r ON r.stream = s.id
          JOIN {local_mediaserver_program} p ON p.id = r.program
          JOIN {user} u ON u.id = s.userid
         WHERE p.id = :program';

$stream = $DB->get_record_sql($sql, array('program' => $id), MUST_EXIST);
$fullname = fullname($stream, has_capability('moodle/site:viewfullnames', $context));

$a = new stdClass;
$a->name = html_writer::link(new moodle_url('/user/profile.php', array('id' => $stream->userid)), $fullname);
$a->date = userdate($stream->submitted, get_string('strftimedatetime', 'langconfig'));

// The popup dialogue requires heading & text attributes.
$data = array(
    'heading' => s($stream->title),
    'text' => get_string('streaminfo', 'local_mediaserver', $a),
);

echo json_encode($data);

echo $OUTPUT->footer();
