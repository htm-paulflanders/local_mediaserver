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
 * @id         $Id: player.js 4749 2018-05-31 11:01:58Z pholden $
 */

define(['jquery', 'core/config'],
        function($, Config) {

    /** Element selectors. */
    var SELECTORS = {
        PLAYERS: '[data-role="local-mediaserver-player"]'
    };

    /** Flash plugins. */
    var PLUGINS = {
        FLOWPLAYER: Config.wwwroot + '/media/player/flowplayerflash/flowplayer/flowplayer-3.2.18.swf.php',
        CONTROLS: Config.wwwroot + '/media/player/flowplayerflash/flowplayer/flowplayer.controls-3.2.16.swf.php',
        RTMP: Config.wwwroot + '/local/mediaserver/amd/assets/flowplayer.rtmp-3.2.13.swf.php'
    };

    return /** @alias module:local_mediaserver/player */ {
        // Public variables and functions.
        /**
         * Initialize media server player UI
         *
         * @method init
         * @param {String} server
         * @param {String} application
         * @param {Int} time
         */
        init: function(server, application, time) {
            var autoplay = (time > 0);

            // Initialize flowplayer for each player element on page.
            $(SELECTORS.PLAYERS).each(function() {
                var container = $(this);

                /* globals flowplayer */
                flowplayer(container.attr('id'), PLUGINS.FLOWPLAYER, {
                    plugins: {
                        rtmp: {
                            url: PLUGINS.RTMP,
                            netConnectionUrl: 'rtmp://' + server + '/' + application,
                            durationFunc: 'getStreamLength'
                        },
                        controls: {
                            url: PLUGINS.CONTROLS,
                            fullscreen: true,
                            height: 30,
                            autoHide: true
                        }
                    },

                    playlist: Config.wwwroot + container.data('playlist'),

                    clip: {
                        provider: 'rtmp',
                        scaling: 'fit',
                        autoPlay: autoplay,

                        onStart: function(clip) {
                            // Check if seeking and the current clip is the RTMP stream.
                            if (autoplay && clip.provider === 'rtmp') {
                                this.seek(time);
                            }
                        }
                    }
                });
            });
        }
    };
});
