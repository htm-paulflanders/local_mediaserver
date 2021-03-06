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
 * @id         $Id: category_edit.php 4110 2016-02-10 15:02:17Z pholden $
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

$parentid = optional_param('parent', -1, PARAM_INT);
$categoryid = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$context = context_system::instance();

require_login(SITEID, false);
require_capability('local/mediaserver:add', $context);

$currenturl = new local_mediaserver_url('/local/mediaserver/admin/category_edit.php');

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_pagetype('local-mediaserver-admin-category_edit');
$PAGE->set_url($currenturl);
$PAGE->set_title(get_string('categories', 'local_mediaserver'));

navigation_node::override_active_url(new local_mediaserver_url('/local/mediaserver/index.php'));

// Creating a new category.
if ($parentid > -1) {
    if ($parentid) {
        $parent = $DB->get_record('local_mediaserver_category', array('id' => $parentid), '*', MUST_EXIST);
    } else {
        // Creating root category.
        require_capability('local/mediaserver:addrootcategories', $context);

        $parent = new stdClass;
        $parent->id = 0;
        $parent->path = '/';
    }

    // Create a dummy category object.
    $category = new stdClass;
}

// Editing an existing category.
if ($categoryid) {
    $category = $DB->get_record('local_mediaserver_category', array('id' => $categoryid), '*', MUST_EXIST);

    if ($category->depth > 1) {
        $parent = local_mediaserver_category_parent($category);
    } else {
        // Editing root category.
        require_capability('local/mediaserver:addrootcategories', $context);

        $parent = new stdClass;
        $parent->id = 0;
        $parent->path = '/';
    }

    if ($delete) {
        $returnurl = new local_mediaserver_url('/local/mediaserver/index.php', array('id' => $parent->id));

        if ($confirm) {
            require_sesskey();

            local_mediaserver_category_delete($category);

            redirect($returnurl);
        } else {
            $currenturl->params(array('id' => $category->id, 'delete' => 1, 'confirm' => 1));

            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('deletecategory', '', $category->name));
            echo $OUTPUT->confirm(get_string('categorydelete', 'local_mediaserver'), $currenturl, $returnurl);

            echo $OUTPUT->footer();
            die;
        }
    }
}

// If we don't have a category here, no valid arguments were passed to script.
if (empty($category)) {
    throw new invalid_parameter_exception();
}

$mform = new \local_mediaserver\form\category(null, array($category));
$mform->set_data(array('parent' => $parent->id));

$returnurl = new local_mediaserver_url('/local/mediaserver/index.php', array('id' => $parent->id));

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $category->id = $data->id;
    $category->name = trim($data->name);

    if ($category->id > 0) {
        $DB->update_record('local_mediaserver_category', $category);
    } else {
        // When inserting new category, we need it's ID to build complete path data.
        $category->id = $DB->insert_record('local_mediaserver_category', $category);

        $category->path = rtrim($parent->path, '/') . '/' . $category->id;
        $category->depth = substr_count($category->path, '/');

        $DB->update_record('local_mediaserver_category', $category);
    }

    redirect($returnurl);
}

$headingstring = ($categoryid ? 'editcategorythis' : 'addnewcategory');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string($headingstring));

$mform->display();

echo $OUTPUT->footer();
