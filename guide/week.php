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
 * @id         $Id: week.php 4843 2018-10-26 15:33:30Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$time = optional_param('t', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

// Normalize time, round down to beginning of week.
if ($time == 0) {
    $time = time();
}

$dayofweek = (date('N', $time) - 1);
$time = local_mediaserver_time_round($time - (DAYSECS * $dayofweek), true);

// End of week, minus one second.
$date = usergetdate($time);
$timeend = mktime(23, 59, 59, $date['mon'], $date['mday'] + 6, $date['year']);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$strthisweek = get_string('thisweek', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strthisweek;

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('local-mediaserver-guide-week');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/week.php', array('t' => $time, 'page' => $page)));
$PAGE->set_title($strtitle);

echo $OUTPUT->header();

$lastweek = strtotime('previous week', $time);
$heading = html_writer::link(new local_mediaserver_url('/local/mediaserver/guide/week.php', array('t' => $lastweek)), $OUTPUT->larrow());

$heading .= userdate($time, get_string('strftimedaydate', 'langconfig'));

list($icon, $popup) = local_mediaserver_calendar_popup($time);
$heading .= $icon;

$nextweek = strtotime('next week', $time);
$heading .= html_writer::link(new local_mediaserver_url('/local/mediaserver/guide/week.php', array('t' => $nextweek)), $OUTPUT->rarrow());

echo $OUTPUT->heading($heading, 3, null, 'week-heading');
echo $popup;

$fields = local_mediaserver_program_fields();

// Return all program recordings or favourites.
$select = 'p.timebegin BETWEEN :timebegin AND :timeend AND COALESCE(r.id, f.id) IS NOT NULL';
$sql = local_mediaserver_program_select_sql($fields, $select);

$params = array('userid' => $USER->id, 'timebegin' => $time, 'timeend' => $timeend);

if ($programs = $DB->get_records_sql($sql, $params, $page * LOCAL_MEDIASERVER_MEDIA_PAGING, LOCAL_MEDIASERVER_MEDIA_PAGING)) {
    $renderer = $PAGE->get_renderer('local_mediaserver', 'guide');

    foreach ($programs as $program) {
        echo $renderer->output_program($program, $program->timebegin);
    }

    $sqlcount = str_replace($fields, 'COUNT(*)', $sql);
    $totalcount = $DB->count_records_sql($sqlcount, $params);

    $pagingbar = new paging_bar($totalcount, $page, LOCAL_MEDIASERVER_MEDIA_PAGING, $PAGE->url);
    echo $OUTPUT->render($pagingbar);
} else {
    echo $OUTPUT->heading(get_string('nothingtodisplay'), 3);
}

echo $OUTPUT->footer();
