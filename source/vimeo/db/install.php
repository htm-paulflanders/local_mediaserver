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
 * @id         $Id: install.php 3603 2015-05-20 08:23:11Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This method is called upon plugin installation.
 *
 * @return bool
 */
function xmldb_mediasource_vimeo_install() {
    global $CFG;

    require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

    local_mediaserver_source_upgrade('vimeo', '^https?://vimeo.com/');

    return true;
}
