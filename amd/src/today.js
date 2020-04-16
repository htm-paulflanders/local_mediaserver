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
 * @id         $Id: today.js 4721 2018-03-26 09:07:58Z pholden $
 */

define(['local_mediaserver/guide'],
        function(Guide) {

    /** Element selectors. */
    var SELECTORS = {
        PROGRAMS: '.block_fake li a'
    };

    return /** @alias module:local_mediaserver/today */ {
        // Public variables and functions.
        /**
         * Initialize programs in Today block
         *
         * @method init
         * @private
         */
        init: function() {
            Guide.init(SELECTORS.PROGRAMS);
        }
    };
});
