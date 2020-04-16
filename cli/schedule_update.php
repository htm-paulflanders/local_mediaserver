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
 * @id         $Id: schedule_update.php 4529 2017-02-09 10:45:10Z pholden $
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

// Ensure errors are well explained.
set_debugging(DEBUG_DEVELOPER, true);

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array(
    'help' => false,
    'channel' => false,
    'start' => time(),
    'finish' => false,
), array('h' => 'help', 'c' => 'channel', 's' => 'start', 'f' => 'finish'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] or empty($options['channel'])) {
    $help =
"Updates schedule data for a given channel.

Options:
-c, --channel         Channel ID (required)
-s, --start           Start time (defaults to now)
-f, --finish          Finish time (defaults to next task execution time)
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php local/mediaserver/cli/schedule_update.php --channel=123";

    cli_writeln($help);
    die;
}

// Only process enabled channels.
$select = 'id = :id AND (hourbegin > 0 OR hourend > 0)';
$channel = $DB->get_record_select('local_mediaserver_channel', $select, array('id' => $options['channel']), '*', MUST_EXIST);

// Start/finish times for schedule.
$start = $options['start'];
if (! $finish = $options['finish']) {
    $task = \core\task\manager::get_scheduled_task('\local_mediaserver\task\schedule_task');
    $finish = $task->get_next_scheduled_time();
}

// Don't continue if we already have schedule data.
$select = 'channel = :channel AND timebegin BETWEEN :start AND :finish';
$params = array('channel' => $channel->id, 'start' => $start, 'finish' => $finish - 1);
if ($DB->record_exists_select('local_mediaserver_program', $select, $params)) {
    $strdateformat = get_string('strftimedatetimeshort', 'langconfig');

    cli_error('Channel already has schedule data for that period '.
        '(' . userdate($start, $strdateformat) . ' to ' . userdate($finish, $strdateformat) . ')');
}

$programs = local_mediaserver_schedule_download($channel, $start, $finish);

$log = sprintf('Downloaded %d programmes for channel \'%s\'', count($programs), $channel->name);
cli_writeln($log);

foreach ($programs as $program) {
    $DB->insert_record('local_mediaserver_program', $program);
}

exit(0);
