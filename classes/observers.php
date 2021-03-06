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
 * @id         $Id: observers.php 4700 2018-02-05 15:10:40Z pholden $
 */

namespace local_mediaserver;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mediaserver/lib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class observers {

    /**
     * Media completed
     *
     * @param \local_mediaserver\event\media_completed $event
     * @return void
     */
    public static function media_completed(\local_mediaserver\event\media_completed $event) {
        $stream = $event->get_record_snapshot('local_mediaserver_stream', $event->objectid);

        $message = new \core\message\message();
        $message->component = 'local_mediaserver';
        $message->name = 'notification';
        $message->notification = 1;
        $message->courseid = SITEID;
        $message->userfrom = \core_user::get_support_user();
        $message->userto = \core_user::get_user($stream->userid, '*', MUST_EXIST);

        // Make sure we have a valid user/e-mail.
        \core_user::require_active_user($message->userto, true, true);
        if (! validate_email($message->userto->email)) {
            return;
        }

        // Link back to the media.
        $url = local_mediaserver_stream_url($stream);

        $message->contexturlname = get_string('mediaview', 'local_mediaserver');
        $message->contexturl = $url->out(true);

        // Construct message content.
        $message->subject = $stream->title;
        $message->smallmessage = $stream->title;

        $viewfullnames = has_capability('moodle/site:viewfullnames', $event->get_context(), $message->userto);

        $options = new \stdClass;
        $options->name = fullname($message->userto, $viewfullnames);
        $options->url = $message->contexturl;

        $message->fullmessage = get_string('media_completed_message', 'local_mediaserver', $options);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '';

        message_send($message);
    }
}
