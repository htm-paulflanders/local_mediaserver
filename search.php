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
 * @id         $Id: search.php 4535 2017-02-09 14:54:10Z pholden $
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/searchlib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$search = required_param('search', PARAM_NOTAGS);
$page = optional_param('page', 0, PARAM_INT);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:view', $context);

$browse = new local_mediaserver_url('/local/mediaserver/index.php');

$strsearch = get_string('search');
$strtitle = get_string('pluginname', 'local_mediaserver') . ': ' . $strsearch;

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-search');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/search.php', array('search' => $search, 'page' => $page)));
$PAGE->set_title($strtitle);

$action = new local_mediaserver_url($PAGE->url->out_omit_querystring());
$PAGE->set_button(local_mediaserver_search_form($action, 'get', $search));

$PAGE->navbar->add($strsearch);
navigation_node::override_active_url($browse);

echo $OUTPUT->header();

$tokenreplacements = array('title:' => 'subject:', 'source:' => 'instance:');
$search = trim(strtr($search, $tokenreplacements));
if (! $search) {
    print_error('missingparam', '', '', 'search');
}

$parser = new search_parser();
$lexer = new search_lexer($parser);

if ($lexer->parse($search)) {
    $parsetree = $parser->get_parsed_array();

    // Data fields contain searchable data in addition to the stream title (meta field).
    $separator = "'|'";
    $programfields = $DB->sql_concat_join($separator, array('p.title', 'p.episodetitle', 'p.description'));
    $datafields = $DB->sql_concat_join($separator, array('s.description', 'COALESCE(' . $programfields . ', ' . $separator . ')'));

    // Time field is the program start time for recordings, otherwise it is the time the stream was created.
    $timefield = 'COALESCE(p.timebegin, s.submitted)';
    list($select, $conditions) = local_mediaserver_search_generate_sql(
        $parsetree, $datafields, 's.title', 's.userid', 'u.id', 'u.firstname', 'u.lastname', $timefield, 's.source'
    );

    $fields = 's.*, ' . get_all_user_name_fields(true, 'u');

    $sql = "SELECT $fields
              FROM {local_mediaserver_stream} s
              JOIN {user} u ON u.id = s.userid
         LEFT JOIN {local_mediaserver_recording} r ON r.stream = s.id
         LEFT JOIN {local_mediaserver_program} p ON p.id = r.program
             WHERE done = :done AND $select
          ORDER BY s.title, s.id";

    $conditions['done'] = 1;

    $sqlcount = str_replace($fields, 'COUNT(*)', $sql);
    $totalcount = $DB->count_records_sql($sqlcount, $conditions);

    $notificationtype = ($totalcount == 0
        ? \core\output\notification::NOTIFY_ERROR
        : \core\output\notification::NOTIFY_SUCCESS
    );
    echo $OUTPUT->notification(get_string('searchfound', 'local_mediaserver', number_format($totalcount)), $notificationtype);

    if ($totalcount > 0) {
        $streams = $DB->get_records_sql($sql, $conditions, $page * LOCAL_MEDIASERVER_MEDIA_PAGING, LOCAL_MEDIASERVER_MEDIA_PAGING);

        // If user can add content, we need to provide a form to move streams.
        if ($canaddcontent = has_capability('local/mediaserver:add', $context)) {
            echo html_writer::start_tag('form', array('action' => $browse->out(true), 'method' => 'post'));
        }

        foreach ($streams as $stream) {
            echo local_mediaserver_stream_preview($stream);
        }

        if ($canaddcontent) {
            echo html_writer::start_tag('div', array('class' => 'continuebutton'));
            echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('move')));
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('form');
        }

        $pagingbar = new paging_bar($totalcount, $page, LOCAL_MEDIASERVER_MEDIA_PAGING, $PAGE->url);
        echo $OUTPUT->render($pagingbar);
    }
}

echo $OUTPUT->footer();
