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
 * @id         $Id: links.php 4637 2017-07-05 12:35:38Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$search = optional_param('search', '', PARAM_NOTAGS);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

$strseries = get_string('recordseries', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strseries;

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-guide-links');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/links.php', array('search' => $search)));
$PAGE->set_title($strtitle);

$action = new local_mediaserver_url($PAGE->url->out_omit_querystring());
$PAGE->set_button(local_mediaserver_search_form($action, 'get', $search, 'seriessearch'));

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($strseries, 'recordseriesdetailed', 'local_mediaserver', '', '', 3);

$table = new \local_mediaserver\output\links_table($search);
$table->define_baseurl($PAGE->url);

if ($search) {
    $count = $DB->count_records_sql($table->countsql, $table->countparams);

    $notifyclass = ($count == 0
        ? \core\output\notification::NOTIFY_ERROR
        : \core\output\notification::NOTIFY_SUCCESS
    );

    echo $OUTPUT->notification(get_string('searchfound', 'local_mediaserver', $count), $notifyclass);
}

$table->out(LOCAL_MEDIASERVER_MEDIA_PAGING, false);

echo $OUTPUT->footer();
