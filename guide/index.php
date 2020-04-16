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
 * @id         $Id: index.php 4250 2016-04-18 10:14:04Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$time = optional_param('t', 0, PARAM_INT);

// Normalize time, round down to hour.
if ($time == 0) {
    $time = time();
}
$time = local_mediaserver_time_round($time);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_pagetype('local-mediaserver-guide-index');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/index.php', array('t' => $time)));
$PAGE->set_title(get_string('sourceepg', 'local_mediaserver'));
$PAGE->set_button(local_mediaserver_guide_search_form());

// Initialise javascript module, for displaying program info when hovering over it.
$PAGE->requires->js_call_amd('local_mediaserver/guide', 'init');

// Create renderer instance to define page/block layout.
$renderer = $PAGE->get_renderer('local_mediaserver', 'guide');
$renderer->add_block_region();

// Add page blocks.
$block = new \local_mediaserver\block\calendar($time);
$renderer->add_block($block);

$block = new \local_mediaserver\block\today($time);
$renderer->add_block($block);

echo $OUTPUT->header();

echo $renderer->start_main_region();

$heading = local_mediaserver_local_time($time);
echo $OUTPUT->heading($heading, 3);

echo $renderer->output_times($time);

// End time is four hours after current time, minus one second.
$timeend = ($time + (4 * HOURSECS) - 1);

$channels = local_mediaserver_user_channels();
foreach ($channels as $channel) {
    $programs = local_mediaserver_channel_listing($channel->id, $time, $timeend);

    echo $renderer->output_channel_programs($channel, $programs, $time, $timeend);
}

$mychannels = new local_mediaserver_url('/local/mediaserver/guide/mychannels.php');
echo $OUTPUT->single_button($mychannels, get_string('channelschoose', 'local_mediaserver'), 'get', array('class' => 'continuebutton'));

echo $renderer->end_main_region();

echo $renderer->output_blocks();

echo $OUTPUT->footer();
