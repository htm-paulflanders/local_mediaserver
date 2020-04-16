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
 * @id         $Id: channel.php 4842 2018-10-26 14:49:09Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$id = required_param('id', PARAM_INT);
$time = required_param('t', PARAM_INT);

// Normalize time, round down to beginning of day.
$time = local_mediaserver_time_round($time, true);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$channel = $DB->get_record('local_mediaserver_channel', array('id' => $id), '*', MUST_EXIST);

$strchannel = $channel->name;
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strchannel;

$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_pagetype('local-mediaserver-guide-channel');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/channel.php', array('id' => $id, 't' => $time)));
$PAGE->set_title($strtitle);
$PAGE->set_button(local_mediaserver_guide_search_form());

// Get the EPG navigation branch and add the $time URL argument.
$branch = $PAGE->navigation->find('epg', navigation_node::TYPE_CONTAINER);
$branch->action->param('t', $time);

// Create channel node inside EPG branch.
$node = $branch->add($strchannel);
$node->make_active();

// Create renderer instance to define page/block layout.
$renderer = $PAGE->get_renderer('local_mediaserver', 'guide');
$renderer->add_block_region();

// Add page blocks.
$block = new \local_mediaserver\block\calendar($time);
$renderer->add_block($block);

$block = new \local_mediaserver\block\today($time, $channel->id);
$renderer->add_block($block);

echo $OUTPUT->header();

echo $renderer->start_main_region();

$heading = userdate($time, get_string('strftimedaydate', 'langconfig'));
echo $OUTPUT->heading($heading, 3);

// End time is end of the day, minus one second.
$date = usergetdate($time);
$timeend = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);

// Programs will also include the last program from the previous day if it finished after midnight.
$programs = local_mediaserver_channel_listing($channel->id, $time, $timeend);

foreach ($programs as $program) {
    // Filter out previous days program.
    if ($program->timebegin < $time) {
        continue;
    }

    echo $renderer->output_program($program, $time);
}

echo $renderer->end_main_region();

echo $renderer->output_blocks();

echo $OUTPUT->footer();
