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
 * @id         $Id: admin_setting_schedulesource.php 4361 2016-07-29 11:03:31Z pholden $
 */

namespace local_mediaserver;

defined('MOODLE_INTERNAL') || die();

class admin_setting_schedulesource extends \admin_setting_configselect {

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $visiblename
     * @param string $description
     */
    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, '', null);
    }

    /**
     * Lazy-load array of enabled mediaschedule sub-plugins
     *
     * @return bool
     */
    public function load_choices() {
        if (is_array($this->choices)) {
            return true;
        }

        $plugins = local_mediaserver_plugins('mediaschedule');
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled()) {
                $this->choices[$plugin->type . '_' . $plugin->name] = $plugin->displayname;
            }
        }

        \core_collator::asort($this->choices);

        return true;
    }

    /**
     * Save a setting
     *
     * @param string $data
     * @return string empty of error string
     */
    public function write_setting($data) {
        if (! $this->load_choices() or empty($this->choices)) {
            return '';
        }

        // Default to first schedule source if $data doesn't exist.
        if (! array_key_exists($data, $this->choices)) {
            $data = key($this->choices);
        }

        $result = '';
        if (! $this->config_write($this->name, $data)) {
            $result = get_string('errorsetting', 'admin');
        }

        return $result;
    }
}
