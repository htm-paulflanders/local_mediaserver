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
 * @id         $Id: mychannels.php 4693 2018-02-05 10:26:00Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$channels = optional_param_array('channels', null, PARAM_INT);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$guide = new local_mediaserver_url('/local/mediaserver/guide/index.php');

$strchoose = get_string('channelschoose', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strchoose;

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('local-mediaserver-guide-mychannels');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/mychannels.php'));
$PAGE->set_title($strtitle);

$PAGE->navbar->add($strchoose);
navigation_node::override_active_url($guide);

if ($channels) {
    require_sesskey();

    $preference = implode(',', $channels);
    useredit_update_user_preference(array('id' => $USER->id,
        'preference_local_mediaserver_channels' => $preference));

    redirect($guide);
}

echo $OUTPUT->header();

echo $OUTPUT->heading_with_help($strchoose, 'channelschoose', 'local_mediaserver', '', '', 3);

$enabledchannels = local_mediaserver_enabled_channels();
$userchannels = local_mediaserver_user_channels();

$list = array();

foreach ($enabledchannels as $channel) {
    $userchannel = array_key_exists($channel->id, $userchannels);
    $icon = local_mediaserver_channel_icon($channel);

    $list[] = html_writer::checkbox('channels[]', $channel->id, $userchannel, $icon);
}

$action = clone($PAGE->url);
$action->params(array('sesskey' => sesskey()));

echo html_writer::start_tag('form', array('action' => $action->out_omit_querystring(), 'method' => 'post'));
echo html_writer::input_hidden_params($action);
echo html_writer::alist($list, array('class' => 'channels-choose'));
echo html_writer::start_tag('div', array('class' => 'continuebutton'));
echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('savechanges')));
echo html_writer::end_tag('div');
echo html_writer::end_tag('form');

echo $OUTPUT->footer();
