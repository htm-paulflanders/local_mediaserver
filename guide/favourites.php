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
 * @id         $Id: favourites.php 4558 2017-03-02 13:50:39Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$delete = optional_param('delete', 0, PARAM_INT);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:viewepg', $context);

$strfavourites = get_string('favourites', 'local_mediaserver');
$strtitle = get_string('sourceepg', 'local_mediaserver') . ': ' . $strfavourites;

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-guide-favourites');
$PAGE->set_url(new local_mediaserver_url('/local/mediaserver/guide/favourites.php'));
$PAGE->set_title($strtitle);

if ($delete) {
    require_sesskey();

    $DB->delete_records('local_mediaserver_favourite', array('id' => $delete, 'userid' => $USER->id));

    redirect($PAGE->url);
}

echo $OUTPUT->header();

$table = new flexible_table('local_mediaserver');
$table->define_columns(array('title', 'search', 'delete'));
$table->define_headers(array(get_string('programtitle', 'local_mediaserver'), null, null));
$table->define_baseurl($PAGE->url);
$table->setup();

$favourites = $DB->get_records('local_mediaserver_favourite', array('userid' => $USER->id), 'title');
foreach ($favourites as $favourite) {
    $title = s($favourite->title);

    // Find more programs with same title.
    if (str_word_count($favourite->title) == 1) {
        $searchstr = 'title:' . $favourite->title;
    } else {
        $searchstr = '"' . $favourite->title . '"';
    }

    $searchurl = local_mediaserver_guide_search_url(array('search' => $searchstr));
    $search = $OUTPUT->single_button($searchurl, get_string('search'));

    // Remove programme favourite.
    $removeurl = clone($PAGE->url);
    $removeurl->params(array(
        'delete' => $favourite->id,
    ));
    $remove = $OUTPUT->single_button($removeurl, get_string('delete'));

    $table->add_data(array($title, $search, $remove));
}

$table->print_html();

echo $OUTPUT->footer();
