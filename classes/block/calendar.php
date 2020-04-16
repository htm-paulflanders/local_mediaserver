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
 * @id         $Id: calendar.php 4094 2016-02-04 15:40:32Z pholden $
 */

namespace local_mediaserver\block;

defined('MOODLE_INTERNAL') || die();

class calendar extends \block_contents {

    /**
     * Class constructor; initialize block title and contents
     *
     * @param int $time Unix timestamp
     */
    public function __construct($time = null) {
        parent::__construct();

        list($icon, $popup) = local_mediaserver_calendar_popup($time);

        $this->title = userdate($time, '%B %Y') . $icon;
        $this->content = $this->get_calendar($time) . $popup;
    }

    /**
     * Generate calendar content
     *
     * @param int $time Unit timestamp
     * @return string
     */
    private function get_calendar($time) {
        global $PAGE;

        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $date = $calendar->timestamp_to_date_array($time);

        $daysinweek = $calendar->get_num_weekdays();
        $minwday = $calendar->get_starting_weekday();
        $maxwday = $minwday + ($daysinweek - 1);

        $table = new \html_table();
        $table->attributes['class'] = 'calendartable small-text';

        // Day names in table heading.
        $days = $calendar->get_weekdays();
        for ($i = $minwday; $i <= $maxwday; ++$i) {
            $day = $days[$i % $daysinweek];

            $table->head[] = \html_writer::tag('abbr', $day['shortname'], array('title' => $day['fullname']));
        }

        // Days of month.
        $startwday = dayofweek(1, $date['mon'], $date['year']);
        if ($startwday < $minwday) {
            $startwday += $daysinweek;
        }

        $link = $PAGE->url;

        $row = new \html_table_row();

        // Padding from previous month.
        $maxdaysprevious = $calendar->get_num_days_in_month($date['year'], $date['mon'] - 1);
        $day = ($maxdaysprevious - $startwday) + $minwday + 1;

        for ($i = $minwday; $i < $startwday; ++$i, ++$day) {
            $cell = new \html_table_cell();

            $link->param('t', mktime(0, 0, 0, $date['mon'] - 1, $day, $date['year']));

            $cell->text = \html_writer::link($link, $day);
            $cell->attributes['class'] = 'daypadding';

            $row->cells[] = $cell;
        }

        // Current month.
        $maxdays = $calendar->get_num_days_in_month($date['year'], $date['mon']);
        $dayofweek = $startwday;

        for ($day = 1; $day <= $maxdays; ++$day, ++$dayofweek) {
            // Start a new new table row for each week.
            if ($dayofweek > $maxwday) {
                $table->data[] = $row;
                $row = new \html_table_row();

                $dayofweek = $minwday;
            }

            $cell = new \html_table_cell();

            $link->param('t', mktime(0, 0, 0, $date['mon'], $day, $date['year']));
            $cell->text = \html_writer::link($link, $day);

            if ($date['mday'] == $day) {
                $cell->attributes['class'] .= 'dayselected';
            }

            $row->cells[] = $cell;
        }

        // Padding to following month.
        $day = 1;

        for ($i = $dayofweek; $i <= $maxwday; ++$i, ++$day) {
            $cell = new \html_table_cell();

            $link->param('t', mktime(0, 0, 0, $date['mon'] + 1, $day, $date['year']));

            $cell->text = \html_writer::link($link, $day);
            $cell->attributes['class'] = 'daypadding';

            $row->cells[] = $cell;
        }

        $table->data[] = $row;

        return \html_writer::table($table);
    }
}
