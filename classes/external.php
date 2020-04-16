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
 * @id         $Id: external.php 4673 2017-09-27 13:11:31Z pholden $
 */

namespace local_mediaserver;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/mediaserver/locallib.php');

class external extends \external_api {

    /**
     * Returns description of update_series parameters
     *
     * @return external_function_parameters
     */
    public static function update_series_parameters() {
        return new \external_function_parameters(array(
            'id' => new \external_value(PARAM_INT, 'Series ID', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
            'finished' => new \external_value(PARAM_BOOL, 'Finished state', VALUE_REQUIRED, false, NULL_NOT_ALLOWED),
        ));
    }

    /**
     * Return plugin update_series call
     *
     * @param string $id
     * @param boolean $finished
     * @return boolean
     */
    public static function update_series($id, $finished) {
        global $USER, $DB;

        $params = self::validate_parameters(self::update_series_parameters(), array('id' => $id, 'finished' => $finished));

        $context = \context_system::instance();
        self::validate_context($context);

        require_capability('local/mediaserver:add', $context);

        $args = array('id' => $params['id']);
        if (! has_capability('local/mediaserver:edit', $context)) {
            $args['userid'] = $USER->id;
        }

        $series = $DB->get_record('local_mediaserver_series', $args, '*', MUST_EXIST);
        $series->finished = $params['finished'];

        $DB->set_field('local_mediaserver_series', 'finished', $series->finished, array('id' => $series->id));

        // Trigger series updated event.
        $event = \local_mediaserver\event\series_updated::create(array(
            'objectid' => $series->id,
            'relateduserid' => $series->userid,
            'other' => array(
                'finished' => (int)$series->finished,
            ),
        ));
        $event->add_record_snapshot('local_mediaserver_series', $series);
        $event->trigger();

        return true;
    }

    /**
     * Returns description of update_series() result value
     *
     * @return external_value
     */
    public static function update_series_returns() {
        return new \external_value(PARAM_BOOL, 'Success');
    }

    /**
     * Returns description of jobs_retrieve parameters
     *
     * @return external_function_parameters
     */
    public static function jobs_retrieve_parameters() {
        return new \external_function_parameters(array(
            'createdsince' => new \external_value(PARAM_INT, 'Return jobs created since this time', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
        ));
    }

    /**
     * Return plugin jobs_retrieve call
     *
     * @param int $createdsince
     * @return array
     */
    public static function jobs_retrieve($createdsince) {
        global $DB;

        $params = self::validate_parameters(self::jobs_retrieve_parameters(), array('createdsince' => $createdsince));

        $context = \context_system::instance();
        self::validate_context($context);

        $result = array();

        // Create array of source plugin names.
        $plugins = array_map(function($plugin) {
            return $plugin->name;
        }, local_mediaserver_plugins('mediasource'));

        $streams = $DB->get_records_select('local_mediaserver_stream', 'done = 0 AND submitted > :createdsince', array('createdsince' => $params['createdsince']));
        foreach ($streams as $stream) {
            if (in_array($stream->source, $plugins)) {
                $result[] = \moodle_url::make_webservice_pluginfile_url(
                    $context->id, 'local_mediaserver', 'job', $stream->id, '/', $stream->code . '.job')->out(false);
            } else if ($stream->source == 'epg') {
                $result[] = \moodle_url::make_webservice_pluginfile_url(
                    $context->id, 'local_mediaserver', 'job', $stream->id, '/', $stream->code . '.rec')->out(false);
            }
        }

        return $result;
    }

    /**
     * Returns description of jobs_retrieve result value
     *
     * @return external_multiple_structure
     */
    public static function jobs_retrieve_returns() {
        return new \external_multiple_structure(
            new \external_value(PARAM_URL, 'File download URL')
        );
    }

    /**
     * Validate draftarea frames
     *
     * @param int $draftid
     * @return void
     *
     * @throws invalid_parameter_exception
     */
    private static function validate_draftarea($draftid) {
        global $USER;

        $usercontext = \context_user::instance($USER->id, MUST_EXIST);

        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid, 'filename', false);

        if (count($draftfiles) !== 2) {
            throw new \invalid_parameter_exception('draft area invalid file count');
        }

        list($f1, $f2) = array_values($draftfiles);
        if ($f1->get_filename() !== 'f1.png' or $f2->get_filename() !== 'f2.png') {
            throw new \invalid_parameter_exception('draft area invalid file names');
        }
    }

    /**
     * Returns description of media_complete parameters
     *
     * @return external_function_parameters
     */
    public static function media_complete_parameters() {
        return new \external_function_parameters(array(
            'code' => new \external_value(PARAM_ALPHANUM, 'Stream code', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
            'draftid' => new \external_value(PARAM_INT, 'Draft files ID', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
        ));
    }

    /**
     * Return plugin media_complete call
     *
     * @param string $code
     * @param int $draftid
     * @return boolean
     */
    public static function media_complete($code, $draftid) {
        global $DB;

        $params = self::validate_parameters(self::media_complete_parameters(), array('code' => $code, 'draftid' => $draftid));

        $context = \context_system::instance();
        self::validate_context($context);

        $stream = $DB->get_record('local_mediaserver_stream', array('code' => $params['code'], 'done' => 0), '*', MUST_EXIST);

        self::validate_draftarea($params['draftid']);
        file_save_draft_area_files($params['draftid'], $context->id, 'local_mediaserver', 'frame', $stream->id);

        // Flag stream as complete.
        $stream->done = 1;
        $DB->set_field('local_mediaserver_stream', 'done', $stream->done, array('id' => $stream->id));

        // Trigger media completed event.
        $event = \local_mediaserver\event\media_completed::create(array(
            'objectid' => $stream->id,
            'userid' => \core_user::SUPPORT_USER,
            'relateduserid' => $stream->userid,
        ));
        $event->add_record_snapshot('local_mediaserver_stream', $stream);
        $event->trigger();

        return true;
    }

    /**
     * Returns description of media_complete result value
     *
     * @return external_value
     */
    public static function media_complete_returns() {
        return new \external_value(PARAM_BOOL, 'Success');
    }

    /**
     * Returns description of media_update parameters
     *
     * @return external_function_parameters
     */
    public static function media_update_parameters() {
        return new \external_function_parameters(array(
            'code' => new \external_value(PARAM_ALPHANUM, 'Stream code', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
            'draftid' => new \external_value(PARAM_INT, 'Draft files ID', VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
        ));
    }

    /**
     * Return plugin media_update call
     *
     * @param string $code
     * @param int $draftid
     * @return boolean
     */
    public static function media_update($code, $draftid) {
        global $DB;

        $params = self::validate_parameters(self::media_update_parameters(), array('code' => $code, 'draftid' => $draftid));

        $context = \context_system::instance();
        self::validate_context($context);

        $stream = $DB->get_record('local_mediaserver_stream', array('code' => $params['code'], 'done' => 1), '*', MUST_EXIST);

        self::validate_draftarea($params['draftid']);
        file_save_draft_area_files($params['draftid'], $context->id, 'local_mediaserver', 'frame', $stream->id);

        return true;
    }

    /**
     * Returns description of media_update result value
     *
     * @return string
     */
    public static function media_update_returns() {
        return new \external_value(PARAM_BOOL, 'Success');
    }
}
