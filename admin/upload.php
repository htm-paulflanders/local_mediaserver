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
 * @id         $Id: upload.php 4558 2017-03-02 13:50:39Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-admin-upload');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/admin/upload.php'));
$PAGE->set_title(get_string('addcontent', 'local_mediaserver'));

$options = array('accepted_types' => array('video', 'audio'), 'subdirs' => 0, 'maxfiles' => 1, 'return_types' => FILE_INTERNAL);

$draftitemid = file_get_submitted_draft_itemid('uploadfile');
file_prepare_draft_area($draftitemid, $context->id, 'local_mediaserver', 'upload', null, $options);

$mform = new \local_mediaserver\form\media_upload(null, array($options));
if ($mform->is_cancelled()) {
    redirect(new local_mediaserver_url('/local/mediaserver/index.php'));
} else if ($data = $mform->get_data()) {
    // The reference field will be updated after processing file upload.
    $stream = local_mediaserver_stream_add('upload', '', $data->title, $data->description, $data->category);

    // Process file upload.
    file_save_draft_area_files($draftitemid, $context->id, 'local_mediaserver', 'upload', $stream->id, $options);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'local_mediaserver', 'upload', $stream->id, 'filename', false);
    $file = reset($files);

    $DB->set_field('local_mediaserver_stream', 'reference', $file->get_filename(), array('id' => $stream->id));

    $sourcename = get_string('sourceupload', 'local_mediaserver');
    $message = get_string('addcontentdone', 'local_mediaserver', $sourcename);

    redirect(new local_mediaserver_url('/local/mediaserver/admin/queue.php'), $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
