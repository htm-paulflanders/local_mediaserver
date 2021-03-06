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
 * @id         $Id: settings.php 3692 2015-06-24 12:35:37Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

$url = 'http://atlas.metabroadcast.com';
$setting = new admin_setting_configtext(
    'mediaschedule_atlas/apikey', get_string('apikey', 'mediaschedule_atlas'), get_string('apikey_desc', 'mediaschedule_atlas', $url), '', PARAM_ALPHANUM
);

$settings->add($setting);
