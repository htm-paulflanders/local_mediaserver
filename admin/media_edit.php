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
 * @id         $Id: media_edit.php 4558 2017-03-02 13:50:39Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-admin-media_edit');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/admin/media_edit.php'));
$PAGE->set_title(get_string('addcontent', 'local_mediaserver'));

$mform = new \local_mediaserver\form\media_web(null);
if ($mform->is_cancelled()) {
    redirect(new local_mediaserver_url('/local/mediaserver/index.php'));
} else if ($data = $mform->get_data()) {
    // The source and reference fields may be reset by a media source plugin.
    $reference = $mform->normalize_url($data->externalurl);
    $stream = local_mediaserver_stream_add(LOCAL_MEDIASERVER_SOURCE_DEFAULT, $reference, $data->title, $data->description, $data->category);

    if ($source = local_mediaserver_source_reference($stream->reference)) {
        // The reference field matches a media source, update the stream record.
        $stream->source = $source->get_source_type();
        $stream->reference = $source->get_reference();

        $DB->update_record('local_mediaserver_stream', $stream);

        $sourcename = $source->get_name();
        $filename = $stream->code . '.job';

        local_mediaserver_create_job($filename, $source->get_job($stream));
    } else {
        $sourcename = get_string('sourceurl', 'local_mediaserver');
    }

    $message = get_string('addcontentdone', 'local_mediaserver', $sourcename);
    redirect(new local_mediaserver_url('/local/mediaserver/admin/queue.php'), $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
