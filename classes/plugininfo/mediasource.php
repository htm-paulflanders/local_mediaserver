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
 * @id         $Id: mediasource.php 2662 2014-02-14 13:05:04Z pholden $
 */

namespace local_mediaserver\plugininfo;

defined('MOODLE_INTERNAL') || die();

class mediasource extends \core\plugininfo\base {

    /**
     * Don't allow sub-plugins to be uninstalled
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return false;
    }
}
