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
 * @id         $Id: series.php 4558 2017-03-02 13:50:39Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$id = required_param('id', PARAM_INT);
$returnurl = required_param('returnurl', PARAM_LOCALURL);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

$program = $DB->get_record('local_mediaserver_program', array('id' => $id), '*', MUST_EXIST);

$guide = new local_mediaserver_url('/local/mediaserver/guide/index.php');

$strrecord = get_string('recordseries', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strrecord;

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('local-mediaserver-guide-series');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/series.php', array('id' => $program->id, 'returnurl' => $returnurl)));
$PAGE->set_title($strtitle);

$PAGE->navbar->add($strrecord);
navigation_node::override_active_url($guide);

// Don't series link the same program multiple times.
if ($DB->record_exists('local_mediaserver_series', array('title' => $program->title, 'series' => $program->series))) {
    print_error('errorseriesexists', 'local_mediaserver', $guide);
}

// This is the URL user is sent to after submitting form.
$return = new local_mediaserver_url($returnurl, null, 'p' . $program->id);

$mform = new \local_mediaserver\form\series(null, array($program, $return->out_as_local_url()));
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    $series = new stdClass;
    $series->userid = $USER->id;
    $series->category = $data->category;
    $series->title = $program->title;
    $series->series = $program->series;
    $series->format = $data->format;
    $series->finished = 0;
    $series->submitted = time();

    $series->id = $DB->insert_record('local_mediaserver_series', $series);

    // Trigger series added event.
    $event = \local_mediaserver\event\series_added::create(array(
        'objectid' => $series->id,
    ));
    $event->add_record_snapshot('local_mediaserver_series', $series);
    $event->trigger();

    // Now schedule any program recordings from this series.
    if ($count = local_mediaserver_series_record($series)) {
        $message = get_string('recordseriesdone', 'local_mediaserver', $count);
    } else {
        $message = get_string('changessaved');
    }

    redirect($return, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$heading = $program->title . ' (' . get_string('programformatseries', 'local_mediaserver', $program->series) . ')';
echo $OUTPUT->heading_with_help($heading, 'recordseries', 'local_mediaserver', '', '', 3);

$mform->display();

echo $OUTPUT->footer();
