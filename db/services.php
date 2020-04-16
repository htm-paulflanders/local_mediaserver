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
 * @id         $Id: services.php 4673 2017-09-27 13:11:31Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_mediaserver_update_series' => array(
        'classname'     => '\\local_mediaserver\\external',
        'methodname'    => 'update_series',
        'description'   => 'Update series finished state',
        'type'          => 'write',
        'capabilities'  => 'local/mediaserver:add',
        'ajax'          => true,
    ),

    'local_mediaserver_jobs_retrieve' => array(
        'classname'     => '\\local_mediaserver\\external',
        'methodname'    => 'jobs_retrieve',
        'description'   => 'Get list of pending job files',
        'type'          => 'read',
        'loginrequired' => false,
    ),

    'local_mediaserver_media_complete' => array(
        'classname'     => '\\local_mediaserver\\external',
        'methodname'    => 'media_complete',
        'description'   => 'Notify media completion',
        'type'          => 'write',
        'loginrequired' => false,
    ),

    'local_mediaserver_media_update' => array(
        'classname'     => '\\local_mediaserver\\external',
        'methodname'    => 'media_update',
        'description'   => 'Update media frames',
        'type'          => 'write',
        'loginrequired' => false,
    ),
);

$services = array(
    'Media server web service' => array(
        'functions'     => array(
            'local_mediaserver_jobs_retrieve',
            'local_mediaserver_media_complete',
            'local_mediaserver_media_update',
        ),
        'restrictedusers' => 1,
        'enabled'       => 1,
        'shortname'     => 'local_mediaserver_ws',
        'uploadfiles'   => 1,
        'downloadfiles' => 0,
    ),
);
