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
 * @id         $Id: local_mediaserver.php 4791 2018-06-27 16:17:39Z pholden $
 */

defined('MOODLE_INTERNAL') || die();

$string['addcontent'] = 'Add content';
$string['addcontentdone'] = 'Added new media content from {$a}';
$string['addcontentinfo'] = 'Media info';
$string['addcontenturl'] = 'Media URL';
$string['addcontenturl_help'] = 'Enter the URL of the media you want to capture. Content from some sources will be automatically saved';
$string['app'] = 'RTMP server app';
$string['app_desc'] = 'Name of the target app on media server';
$string['categories'] = 'Categories';
$string['category'] = 'Category';
$string['categorydelete'] = 'This will move all items in the category into it\'s parent';
$string['categoryname'] = 'Category name';
$string['categoryparent'] = 'Parent';
$string['channel'] = 'Channel';
$string['channelbegin'] = 'Broadcast start';
$string['channelcategory'] = 'Channel category';
$string['channelend'] = 'Broadcast end';
$string['channelname'] = 'Channel name';
$string['channelname_help'] = 'Must match the broadcast DVB name';
$string['channels'] = 'Channels';
$string['channelschoose'] = 'Choose channels';
$string['channelschoose_help'] = 'Select the channels you want to display in the media guide';
$string['deleteoldlistings'] = 'Delete old listing data after';
$string['episodes'] = 'Episodes';
$string['episodelast'] = 'Last episode';
$string['errorcreatingjob'] = 'Couldn\'t create new job';
$string['errorinvalidhost'] = 'Invalid RTMP server host';
$string['errormediaexistsdone'] = 'Media item already exists, click <a href="{$a}">here</a> to view';
$string['errormediaexistsqueue'] = 'Media item already exists in queue';
$string['errornoserieslink'] = 'Programme has no series link information';
$string['errornotuners'] = 'There are no tuners available to record this programme';
$string['errornotunique'] = 'Field must be unique';
$string['errorprogramexpired'] = 'Programme recording window has expired';
$string['errorrecordingexists'] = 'Programme already scheduled to record';
$string['errorseriesformat'] = 'Missing episode information';
$string['errorseriesexists'] = 'Programme already series linked';
$string['event_media_added'] = 'Media added';
$string['event_media_completed'] = 'Media completed';
$string['event_media_viewed'] = 'Media viewed';
$string['event_series_added'] = 'Series link added';
$string['event_series_updated'] = 'Series link updated';
$string['favourite'] = 'Favourite';
$string['favourites'] = 'Favourites';
$string['frames'] = 'Frames';
$string['genre'] = 'Genre';
$string['host'] = 'RTMP server host';
$string['host_desc'] = 'Address of the media server';
$string['loadingflash'] = 'Loading Flash content...';
$string['media_completed_message'] = 'Hi {$a->name},

Your recent addition to the media server is now available at the following URL:

{$a->url}';
$string['mediaserver:add'] = 'Add new content';
$string['mediaserver:addrootcategories'] = 'Add root categories';
$string['mediaserver:channels'] = 'Configure EPG channels';
$string['mediaserver:edit'] = 'Edit any existing content';
$string['mediaserver:reports'] = 'View content reports';
$string['mediaserver:view'] = 'View content';
$string['mediaserver:viewepg'] = 'View EPG listings';
$string['mediaview'] = 'View media';
$string['messageprovider:notification'] = 'Notification of completed media content';
$string['pluginname'] = 'Media server';
$string['privacy:metadata:core_comment'] = 'Comments made on media items';
$string['privacy:metadata:local_mediaserver:stream_table'] = 'Stores media items added by users';
$string['privacy:metadata:local_mediaserver:title'] = 'The title of the stream';
$string['privacy:metadata:local_mediaserver:description'] = 'A description of the stream';
$string['privacy:metadata:local_mediaserver:done'] = 'Whether the stream is ready';
$string['privacy:metadata:local_mediaserver:streamsubmitted'] = 'The timestamp when the stream was added';
$string['privacy:metadata:local_mediaserver:favourite_table'] = 'Stores a users favourite programmes';
$string['privacy:metadata:local_mediaserver:program'] = 'The title of the programme';
$string['privacy:metadata:local_mediaserver:userid'] = 'The ID of the user';
$string['privacy:metadata:local_mediaserver:series_table'] = 'Stores series linked programmes';
$string['privacy:metadata:local_mediaserver:series'] = 'The series number';
$string['privacy:metadata:local_mediaserver:finished'] = 'Whether the series has finished';
$string['privacy:metadata:local_mediaserver:submitted'] = 'The timestamp when the series link was created';
$string['privacy:metadata:preference:local_mediaserver_channels'] = 'User\'s preferred EPG channels';
$string['programformatepisode'] = 'Episode {$a}';
$string['programformatseries'] = 'Series {$a}';
$string['programpopupheading'] = '{$a->time} ({$a->duration})';
$string['programscheduled'] = 'Programme is scheduled to be recorded';
$string['programtitle'] = 'Programme title';
$string['queue'] = 'Queue';
$string['record'] = 'Record';
$string['recordcancel'] = 'Cancel recording';
$string['recordcancel_confirm'] = 'Are you sure you want to cancel the selected recording?';
$string['recordseries'] = 'Series link';
$string['recordseries_confirm'] = 'Series link is available for this programme. Do you want to enable now?<br /><br />

Press \'No\' to record a single episode.';
$string['recordseries_help'] = 'Enabling series link will record all episodes from a series';
$string['recordseriesdetailed'] = 'Series linked programmes';
$string['recordseriesdetailed_help'] = 'Enabling series link will record all episodes from a series

Toggle the series icon to disable it (i.e. when it\'s finished)';
$string['recordseriesdone'] = 'Scheduled {$a} series episode(s) to be recorded';
$string['recordseriesformat'] = 'Title format';
$string['recordseriesformat_help'] = 'Enter a format for the title of each recording (requires at least %episode or %episodetitle), using the following properties:

* %title (programme title)
* %series (series number)
* %episode (episode number)
* %episodetitle (episode title - not always available)';
$string['schedulesource'] = 'Schedule source';
$string['schedulesource_desc'] = 'Select the plugin to use for downloading schedule data. Existing channels <strong>must</strong> be reconfigured when switching between plugins';
$string['scheduletask'] = 'Schedule update';
$string['search'] = 'Media search';
$string['searchfound'] = 'Search returned {$a} matching items';
$string['search_help'] = 'Perform a search by using as many of the following syntax options as necessary:

* safari lion<br />
    Match items that contain the text \'safari\' and \'lion\' (for example in million or lionness)
* title:safari<br />
    Match items that contain the text \'safari\' in the title
* +lion<br />
    Match items that contain the whole word \'lion\'
* "sea lion"<br />
    Match items that contain the whole phrase \'sea lion\'
* -lion<br />
    Ignore items that contain the text \'lion\'';
$string['searchownchannels'] = 'Preferred channels only';
$string['searchrecorded'] = 'Recordings only';
$string['search:media'] = 'Media items';
$string['series'] = 'Series';
$string['seriessearch'] = 'Series link search';
$string['seriessearch_help'] = 'Perform a search by using normal media search syntax, plus the following:

* datefrom/dateto:[timestamp]<br />
    Match series\' by date of last episode
* enabled:[0|1]<br />
    Match series\' according to their enabled state';
$string['shorturl'] = 'Short URL';
$string['shorturl_desc'] = 'Enter short version of base URL (requires apache mod_rewrite rule)';
$string['shortviewurl'] = 'Short media view URL';
$string['shortviewurl_desc'] = 'Use short URLs for viewing media items (requires apache mod_rewrite rule)';
$string['sourceepg'] = 'Guide';
$string['sourceupload'] = 'Upload';
$string['sourceurl'] = 'Online';
$string['streaminfo'] = 'Added by {$a->name}, {$a->date}';
$string['subplugintype_mediaschedule'] = 'Schedule source';
$string['subplugintype_mediaschedule_plural'] = 'Schedule sources';
$string['subplugintype_mediasource'] = 'Media source';
$string['subplugintype_mediasource_plural'] = 'Media sources';
$string['thisweek'] = 'This week';
$string['webservicenotconfigured'] = 'The media server web service configuration is incomplete';
