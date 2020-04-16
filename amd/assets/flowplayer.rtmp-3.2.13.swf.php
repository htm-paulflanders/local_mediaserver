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
 * @id         $Id: flowplayer.rtmp-3.2.13.swf.php 4626 2017-06-12 09:12:32Z pholden $
 */

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
define('NO_UPGRADE_CHECK', true);

require('../../../../config.php');
require_once($CFG->libdir . '/filelib.php');

// No URL params.
if (!empty($_GET) or !empty($_POST)) {
    send_file_not_found();
}

// Our referrers only, nobody else should embed these scripts.
if ($referrer = get_local_referer(true)) {
    $host = get_host_from_url($referrer);

    if ($host and strcasecmp($host, get_host_from_url($CFG->wwwroot)) !== 0) {
        send_file_not_found();
    }
}

// Fetch and decode the original content.
$filename = basename(__FILE__, '.php');

$content = file_get_contents(dirname(__FILE__) . '/' . $filename . '.bin');
$content = base64_decode($content);

// Send the original binary code.
send_file($content, $filename, null, 0, true);
