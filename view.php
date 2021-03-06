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
 * @id         $Id: view.php 4749 2018-05-31 11:01:58Z pholden $
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/lib/rsslib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$id = required_param('id', PARAM_INT);
$time = optional_param('t', 0, PARAM_INT);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:view', $context);

$stream = $DB->get_record('local_mediaserver_stream', array('id' => $id, 'done' => 1), '*', MUST_EXIST);

$pageurl = local_mediaserver_stream_url($stream);
if ($time) {
    $pageurl->param('t', $time);
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-view');
$PAGE->set_url($pageurl);
$PAGE->set_title(get_string('pluginname', 'local_mediaserver') . ': ' . s($stream->title));

$action = new local_mediaserver_url('/local/mediaserver/search.php');
$PAGE->set_button(local_mediaserver_search_form($action));

navigation_node::override_active_url(new local_mediaserver_url('/local/mediaserver/index.php'));

// Populate navigation breadcrumbs.
$category = $DB->get_record('local_mediaserver_category', array('id' => $stream->category), '*', MUST_EXIST);

$parents = local_mediaserver_category_breadcrumbs($category);
foreach ($parents as $parent) {
    $PAGE->navbar->add($parent->name, new local_mediaserver_url('/local/mediaserver/index.php', array('id' => $parent->id)));
}

// Administration block settings.
if (local_mediaserver_user_can_edit($stream)) {
    $container = $PAGE->settingsnav->add(s($stream->title), null, navigation_node::TYPE_CONTAINER);
    $container->add(get_string('editsettings'), new local_mediaserver_url('/local/mediaserver/admin/edit.php', array('id' => $stream->id)));
}

$playlist = new moodle_url(rss_get_url($context->id, $USER->id, 'local_mediaserver', $stream->id));

// Initialise javascript module, for loading flash player.
$pluginconfig = get_config('local_mediaserver');

$PAGE->requires->js('/media/player/flowplayerflash/flowplayer/flowplayer-3.2.13.js', true);
$PAGE->requires->js_call_amd('local_mediaserver/player', 'init', array($pluginconfig->host, $pluginconfig->app, $time));

echo $OUTPUT->header();
echo $OUTPUT->heading(s($stream->title), 3);

$renderer = $PAGE->get_renderer('core');
echo $renderer->render_from_template('local_mediaserver/player', array('playlist' => $playlist->out_as_local_url()));

$dateformat = get_string('strftimedatetime', 'langconfig');
$author = core_user::get_user($stream->userid, '*', MUST_EXIST);
$fullname = fullname($author, has_capability('moodle/site:viewfullnames', $context));

$authorlink = html_writer::link(new moodle_url('/user/profile.php', array('id' => $author->id)), $fullname);
$authorsearch = new local_mediaserver_url('/local/mediaserver/search.php', array('search' => 'userid:' . $author->id));

$a = new stdClass;
$a->name = $authorlink . $OUTPUT->action_icon($authorsearch, new pix_icon('i/search', get_string('search'), 'core', array('class' => 'iconsmall')));
$a->date = userdate($stream->submitted, $dateformat);

$streaminfo  = $OUTPUT->heading(get_string('streaminfo', 'local_mediaserver', $a), 4);
$streaminfo .= html_writer::tag('p', format_text($stream->description, FORMAT_MOODLE, array('para' => false)));

if ($stream->source == 'epg') {
    $sql = 'SELECT p.*, c.name AS channel
              FROM {local_mediaserver_program} p
              JOIN {local_mediaserver_recording} r ON r.program = p.id
              JOIN {local_mediaserver_channel} c ON c.id = p.channel
             WHERE r.stream = :stream';

    $program = $DB->get_record_sql($sql, array('stream' => $stream->id), MUST_EXIST);

    $streaminfo .= html_writer::tag('p', format_text($program->description, FORMAT_MOODLE, array('para' => false)));

    $strdate = local_mediaserver_local_time($program->timebegin, $dateformat);
    $strdate .= ' (' . format_time($program->timeend - $program->timebegin) . ')';

    $list = array(
        $program->title,
        $program->channel . ', ' . $strdate,
    );

    if ($strseries = local_mediaserver_series_information($program)) {
        array_splice($list, 1, 0, $strseries);
    }

    $streaminfo .= html_writer::alist($list);
}

echo html_writer::tag('div', $streaminfo, array('class' => 'coursebox'));

// Display commenting interface.
if ($CFG->usecomments && $stream->comments) {
    require_once($CFG->dirroot . '/comment/lib.php');

    comment::init();

    $options = new stdClass;
    $options->context = $context;
    $options->component = 'local_mediaserver';
    $options->area = 'stream';
    $options->itemid = $stream->id;
    $options->showcount = true;

    $comment = new comment($options);
    echo $comment->output(true);
}

// Trigger media viewed event.
$event = \local_mediaserver\event\media_viewed::create(array(
    'objectid' => $stream->id,
));
$event->add_record_snapshot('local_mediaserver_stream', $stream);
$event->trigger();

echo $OUTPUT->footer();
