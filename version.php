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
 * @id         $Id: version.php 4766 2018-06-13 08:25:51Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_mediaserver';
$plugin->release   = '6.0';
$plugin->version   = 2018061300;
$plugin->requires  = 2017051506; // Moodle 3.3.6 onwards.
$plugin->maturity  = MATURITY_STABLE;
