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
 * @id         $Id: upgrade.php 4299 2016-06-06 13:53:18Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_mediaserver_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013121300) {
        // Changing precision of field reference on table local_mediaserver_stream to (255).
        $table = new xmldb_table('local_mediaserver_stream');
        $field = new xmldb_field('reference', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'source');

        // Launch change of precision for field reference.
        $dbman->change_field_precision($table, $field);

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2013121300, 'local', 'mediaserver');
    }

    if ($oldversion < 2014020501) {
        // Define field category to be added to local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('category', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'datafile');

        // Conditionally launch add field category.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014020501, 'local', 'mediaserver');
    }

    if ($oldversion < 2014020502) {
        // Define field icon to be added to local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('icon', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'datafile');

        // Conditionally launch add field icon.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014020502, 'local', 'mediaserver');
    }

    if ($oldversion < 2014031100) {
        // Rename field category on table local_mediaserver_program to genre.
        $table = new xmldb_table('local_mediaserver_program');
        $field = new xmldb_field('category', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'episodetitle');

        // Launch rename field category.
        $dbman->rename_field($table, $field, 'genre');

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014031100, 'local', 'mediaserver');
    }

    if ($oldversion < 2014031200) {
        $table = new xmldb_table('local_mediaserver_category');

        // Define index name_parent (unique) to be dropped form local_mediaserver_category.
        $index = new xmldb_index('name_parent', XMLDB_INDEX_UNIQUE, array('name', 'parent'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index parent (not unique) to be dropped form local_mediaserver_category.
        $index = new xmldb_index('parent', XMLDB_INDEX_NOTUNIQUE, array('parent'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define field parent to be dropped from local_mediaserver_category.
        $field = new xmldb_field('parent');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field name to be added to local_mediaserver_category.
        $field = new xmldb_field('path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'name');
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define index parent (not unique) to be added to local_mediaserver_category.
        $index = new xmldb_index('path', XMLDB_INDEX_NOTUNIQUE, array('path'));
        if (! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014031200, 'local', 'mediaserver');
    }

    if ($oldversion < 2014033100) {
        // Define key channel (foreign) to be added to local_mediaserver_program.
        $table = new xmldb_table('local_mediaserver_program');
        $key = new xmldb_key('channel', XMLDB_KEY_FOREIGN, array('channel'), 'local_mediaserver_channel', array('id'));

        // Launch add key channel.
        $dbman->add_key($table, $key);

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014033100, 'local', 'mediaserver');
    }

    if ($oldversion < 2014033101) {
        // Define index genre (not unique) to be dropped from local_mediaserver_program.
        $table = new xmldb_table('local_mediaserver_program');
        $index = new xmldb_index('genre', XMLDB_INDEX_NOTUNIQUE, array('genre'));

        // Conditionally launch drop index genre.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014033101, 'local', 'mediaserver');
    }

    if ($oldversion < 2014040100) {
        // Define field category to be dropped from local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('category');

        // Conditionally launch drop field category.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014040100, 'local', 'mediaserver');
    }

    if ($oldversion < 2014040700) {
        // Define field sortorder to be added to local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'name');

        // Conditionally launch add field sortorder.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014040700, 'local', 'mediaserver');
    }

    if ($oldversion < 2014070700) {
        // Define field finished to be added to local_mediaserver_series.
        $table = new xmldb_table('local_mediaserver_series');
        $field = new xmldb_field('finished', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'format');

        // Conditionally launch add field finished.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014070700, 'local', 'mediaserver');
    }

    if ($oldversion < 2014091601) {
        // Define field depth to be added to local_mediaserver_category.
        $table = new xmldb_table('local_mediaserver_category');
        $field = new xmldb_field('depth', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, 0, 'path');

        // Conditionally launch add field depth.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update category path & depth fields.
        $categories = $DB->get_records('local_mediaserver_category', null, 'id', 'id, path');
        foreach ($categories as $category) {
            $category->path .= $category->id;
            $category->depth = substr_count($category->path, '/');

            $DB->update_record('local_mediaserver_category', $category);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014091601, 'local', 'mediaserver');
    }

    if ($oldversion < 2014100100) {
        // Define index userid (not unique) to be dropped form local_mediaserver_favourite.
        $table = new xmldb_table('local_mediaserver_favourite');
        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch drop index userid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define key userid (foreign) to be added to local_mediaserver_favourite.
        $key = new xmldb_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Launch add key userid.
        $dbman->add_key($table, $key);

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014100100, 'local', 'mediaserver');
    }

    if ($oldversion < 2014100101) {
        // Define index channel_title_series (not unique) to be dropped form local_mediaserver_program.
        $table = new xmldb_table('local_mediaserver_program');
        $index = new xmldb_index('channel_title_series', XMLDB_INDEX_NOTUNIQUE, array('channel', 'title', 'series'));

        // Conditionally launch drop index channel_title_series.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index title_series (not unique) to be added to local_mediaserver_program.
        $index = new xmldb_index('title_series', XMLDB_INDEX_NOTUNIQUE, array('title', 'series'));

        // Conditionally launch add index title_series.
        if (! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014100101, 'local', 'mediaserver');
    }

    if ($oldversion < 2014100200) {
        // Define index finished (not unique) to be added to local_mediaserver_series.
        $table = new xmldb_table('local_mediaserver_series');
        $index = new xmldb_index('finished', XMLDB_INDEX_NOTUNIQUE, array('finished'));

        // Conditionally launch add index finished.
        if (! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014100200, 'local', 'mediaserver');
    }

    if ($oldversion < 2014100300) {
        // Define index depth (not unique) to be added to local_mediaserver_category.
        $table = new xmldb_table('local_mediaserver_category');
        $index = new xmldb_index('depth', XMLDB_INDEX_NOTUNIQUE, array('depth'));

        // Conditionally launch add index depth.
        if (! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014100300, 'local', 'mediaserver');
    }

    if ($oldversion < 2014100600) {
        // Define index name_path (unique) to be added to local_mediaserver_category.
        $table = new xmldb_table('local_mediaserver_category');
        $index = new xmldb_index('name_path', XMLDB_INDEX_UNIQUE, array('name', 'path'));

        // Conditionally launch add index name_path.
        if (! $dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014100600, 'local', 'mediaserver');
    }

    if ($oldversion < 2014101500) {
        // Define index name (unique) & datafile (unique) to be added to local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');

        $fields = array('name', 'datafile');
        foreach ($fields as $field) {
            $index = new xmldb_index($field, XMLDB_INDEX_UNIQUE, array($field));

            // Conditionally launch add index $field.
            if (! $dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014101500, 'local', 'mediaserver');
    }

    if ($oldversion < 2014102400) {
        // Rename field created on table local_mediaserver_series to submitted.
        $table = new xmldb_table('local_mediaserver_series');
        $field = new xmldb_field('created', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'finished');

        // Launch rename field created.
        $dbman->rename_field($table, $field, 'submitted');

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014102400, 'local', 'mediaserver');
    }

    if ($oldversion < 2014110300) {
        // Rename job file directory in dataroot.
        @rename($CFG->dataroot . '/media', $CFG->dataroot . '/local_mediaserver');

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014110300, 'local', 'mediaserver');
    }

    if ($oldversion < 2014121101) {
        $table = new xmldb_table('local_mediaserver_channel');

        // Define index atlasid (unique) to be dropped form local_mediaserver_channel.
        $index = new xmldb_index('datafile', XMLDB_INDEX_UNIQUE, array('datafile'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Rename field datafile on table local_mediaserver_channel to atlasid.
        $field = new xmldb_field('datafile', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'sortorder');
        $dbman->rename_field($table, $field, 'atlasid');

        // Changing type of field atlasid on table local_mediaserver_channel to char.
        $field = new xmldb_field('atlasid', XMLDB_TYPE_CHAR, '4', null, XMLDB_NOTNULL, null, null, 'sortorder');
        $dbman->change_field_type($table, $field);

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014121101, 'local', 'mediaserver');
    }

    if ($oldversion < 2014121900) {
        // Define index atlasid (unique) to be dropped form local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $index = new xmldb_index('atlasid', XMLDB_INDEX_UNIQUE, array('atlasid'));

        // Conditionally launch drop index name.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2014121900, 'local', 'mediaserver');
    }

    if ($oldversion < 2015051900) {
        // Changing precision of field name on table local_mediaserver_category to (64).
        $table = new xmldb_table('local_mediaserver_category');

        // Conditionally launch drop index name_path.
        $index = new xmldb_index('name_path', XMLDB_INDEX_UNIQUE, array('name', 'path'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Launch change of precision for field name, re-create index.
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null, 'id');
        $dbman->change_field_precision($table, $field);
        $dbman->add_index($table, $index);

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2015051900, 'local', 'mediaserver');
    }

    if ($oldversion < 2015061500) {
        // Define field comments to be added to local_mediaserver_stream.
        $table = new xmldb_table('local_mediaserver_stream');
        $field = new xmldb_field('comments', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'description');

        // Conditionally launch add field comments.
        if (! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2015061500, 'local', 'mediaserver');
    }

    if ($oldversion < 2015062201) {
        // Changing precision of field atlasid on table local_mediaserver_channel to (32), rename to configuration.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('atlasid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, 'sortorder');

        // Launch change of precision for field atlasid.
        $dbman->change_field_precision($table, $field);

        // Launch rename field atlasid.
        $dbman->rename_field($table, $field, 'configuration');

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2015062201, 'local', 'mediaserver');
    }

    if ($oldversion < 2015082100) {
        // Define field icon to be dropped from local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('icon');

        // Conditionally launch drop field icon.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2015082100, 'local', 'mediaserver');
    }

    if ($oldversion < 2015082101) {
        // Rename all stored channel icons for consistency.
        $context = context_system::instance();
        $fs = get_file_storage();

        $files = $fs->get_area_files($context->id, 'local_mediaserver', 'channel', false, 'itemid', false);
        foreach ($files as $file) {
            if (strcmp($file->get_filename(), 'icon') !== 0) {
                $file->rename('/', 'icon');
            }
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2015082101, 'local', 'mediaserver');
    }

    if ($oldversion < 2016032401) {
        // Remove token configuration.
        unset_config('token', 'local_mediaserver');

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2016032401, 'local', 'mediaserver');
    }

    if ($oldversion < 2016060600) {
        // Convert existing channel configuration to new per-schedule source version.
        $schedulesource = get_config('local_mediaserver', 'schedulesource');

        $channels = $DB->get_records_menu('local_mediaserver_channel', null, 'sortorder', 'id, configuration');
        foreach ($channels as $id => $configuration) {
            set_config('channel' . $id, $configuration, $schedulesource);
        }

        // Define field configuration to be dropped from local_mediaserver_channel.
        $table = new xmldb_table('local_mediaserver_channel');
        $field = new xmldb_field('configuration');

        // Conditionally launch drop field name.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Mediaserver savepoint reached.
        upgrade_plugin_savepoint(true, 2016060600, 'local', 'mediaserver');
    }

    return true;
}
