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
 * @copyright  2018 Paul Holden (pholden@greenhead.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @id         $Id: provider.php 4834 2018-09-18 13:04:04Z pholden $
 */

namespace local_mediaserver\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection,
    \core_privacy\local\request\contextlist,
    \core_privacy\local\request\approved_contextlist,
    \core_privacy\local\request\transform,
    \core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\user_preference_provider {

    use \core_privacy\local\legacy_polyfill;

    /** The user preference containing a users channels. */
    const PREFERRED_CHANNELS = 'local_mediaserver_channels';

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function _get_metadata(collection $collection) {
        $collection->add_database_table('local_mediaserver_stream', [
            'userid' => 'privacy:metadata:local_mediaserver:userid',
            'title' => 'privacy:metadata:local_mediaserver:title',
            'description' => 'privacy:metadata:local_mediaserver:description',
            'done' => 'privacy:metadata:local_mediaserver:done',
            'submitted' => 'privacy:metadata:local_mediaserver:streamsubmitted',
        ], 'privacy:metadata:local_mediaserver:stream_table');

        $collection->add_database_table('local_mediaserver_favourite', [
            'userid' => 'privacy:metadata:local_mediaserver:userid',
            'title' => 'privacy:metadata:local_mediaserver:program',
        ], 'privacy:metadata:local_mediaserver:favourite_table');

        $collection->add_database_table('local_mediaserver_series', [
            'userid' => 'privacy:metadata:local_mediaserver:userid',
            'title' => 'privacy:metadata:local_mediaserver:program',
            'series' => 'privacy:metadata:local_mediaserver:series',
            'finished' => 'privacy:metadata:local_mediaserver:finished',
            'submitted' => 'privacy:metadata:local_mediaserver:submitted',
        ], 'privacy:metadata:local_mediaserver:series_table');

        $collection->add_subsystem_link('core_comment', [], 'privacy:metadata:core_comment');

        $collection->add_user_preference(
            self::PREFERRED_CHANNELS,
            'privacy:metadata:preference:local_mediaserver_channels'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid($userid) {
        $contextlist = new contextlist();

        // All plugin data lives in the system context.
        $contextlist->add_system_context();

        return $contextlist;
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param int $userid
     */
    public static function _export_user_preferences($userid) {
        global $DB;

        $preference = get_user_preferences(self::PREFERRED_CHANNELS, null, $userid);

        $ids = explode(',', clean_param($preference, PARAM_SEQUENCE));
        list($select, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);

        $channels = $DB->get_records_select_menu('local_mediaserver_channel', "id $select", $params, 'sortorder', 'id, name');
        if (count($channels) > 0) {
            $channelnames = implode(', ', $channels);

            writer::export_user_preference(
                'local_mediaserver',
                self::PREFERRED_CHANNELS,
                $channelnames,
                get_string('privacy:metadata:preference:local_mediaserver_channels', 'local_mediaserver')
            );
        }
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @return void
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if ($contextlist->count() == 0) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        $context = \context_system::instance();
        $basecontext = [get_string('pluginname', 'local_mediaserver')];

        // Export user streams.
        $records = $DB->get_records('local_mediaserver_stream', ['userid' => $userid]);
        foreach ($records as $stream) {
            $contextpath = array_merge($basecontext, [$stream->id]);

            $data = (object) [
                'title' => $stream->title,
                'description' => $stream->description,
                'finished' => transform::yesno($stream->done),
                'timecreated' => transform::datetime($stream->submitted),
            ];

            writer::with_context($context)->export_data($contextpath, $data);
        }

        // Export user favourite programmes.
        $favourites = $DB->get_records_menu('local_mediaserver_favourite', ['userid' => $userid], 'title', 'id, title');

        writer::with_context($context)->export_related_data($basecontext, 'favourites', array_values($favourites));

        // Export user series linked programmes.
        $seriesdata = [];

        $records = $DB->get_records('local_mediaserver_series', ['userid' => $userid], 'title, series');
        foreach ($records as $series) {
            $seriesdata[] = (object) [
                'title' => $series->title,
                'series' => $series->series,
                'finished' => transform::yesno($series->finished),
                'timecreated' => transform::datetime($series->submitted),
            ];
        }

        writer::with_context($context)->export_related_data($basecontext, 'series', $seriesdata);

        // Export user comments.
        $sql = 'SELECT DISTINCT(s.id)
                  FROM {local_mediaserver_stream} s
                  JOIN {comments} c ON c.contextid = :contextid AND c.component = :component AND c.itemid = s.id
                 WHERE c.userid = :userid';

        $params = [
            'contextid' => $context->id,
            'component' => 'local_mediaserver',
            'userid' => $userid,
        ];

        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $record) {
            $contextpath = array_merge($basecontext, [$record->id]);
            \core_comment\privacy\provider::export_comments($context, 'local_mediaserver', 'stream', $record->id, $contextpath, true);
        }

        $recordset->close();
    }

    /**
     * Delete all user data in the specified context.
     *
     * @param context $context
     * @return void
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        // Delete all favourites.
        $DB->delete_records('local_mediaserver_favourite');

        \core_comment\privacy\provider::delete_comments_for_all_users($context, 'local_mediaserver');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @return void
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        // Delete user favourites.
        $DB->delete_records('local_mediaserver_favourite', ['userid' => $contextlist->get_user()->id]);

        \core_comment\privacy\provider::delete_comments_for_user($contextlist, 'local_mediaserver');
    }
}
