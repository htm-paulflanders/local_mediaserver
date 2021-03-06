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
 * @id         $Id: channels.php 4301 2016-06-06 14:33:29Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:channels', $context);

$strchannels = get_string('channels', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strchannels;

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-guide-channels');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/channels.php'));
$PAGE->set_title($strtitle);

echo $OUTPUT->header();

$schedulesource = get_config('local_mediaserver', 'schedulesource');

$table = new flexible_table('local_mediaserver');
$table->define_columns(array('icon', 'name', 'config', 'hourbegin', 'hourend', 'actions'));
$table->define_headers(array(get_string('icon'), get_string('channelname', 'local_mediaserver'), get_string('channelconfig', $schedulesource), get_string('channelbegin', 'local_mediaserver'), get_string('channelend', 'local_mediaserver'), null));
$table->define_baseurl($PAGE->url);
$table->setup();

$channels = $DB->get_records('local_mediaserver_channel', null, 'sortorder');

foreach ($channels as $channel) {
    $icon = local_mediaserver_channel_icon($channel);
    $configuration = local_mediaserver_channel_configuration($channel->id);

    $hourbegin = sprintf('%02d:00', $channel->hourbegin);
    $hourend   = sprintf('%02d:00', $channel->hourend);

    $editurl = new local_mediaserver_url('/local/mediaserver/guide/channeledit.php', array('id' => $channel->id));
    $editicon = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit'), 'core', array('class' => 'iconsmall')));

    $classname = (local_mediaserver_channel_disabled($channel) ? 'dimmed_text' : '');

    $table->add_data(array($icon, $channel->name, $configuration, $hourbegin, $hourend, $editicon), $classname);
}

$table->print_html();

echo $OUTPUT->single_button(new local_mediaserver_url('/local/mediaserver/guide/channeledit.php'), get_string('add'), 'get', array('class' => 'continuebutton'));

echo $OUTPUT->footer();
