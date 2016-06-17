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
 * Version details
 *
 * @package    block
 * @subpackage block_user_bookmarks
 * @copyright  Jonas R�egge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

if ($bookmarkurl = htmlspecialchars_decode($_GET["bookmarkurl"]) and $title = $_GET["title"] and confirm_sesskey()) {

    /**
     * This gets the user_bookmarks
     */
    if (get_user_preferences('user_bookmarks')) {
        $bookmarks = preg_split('/(?<!\\\\),/', get_user_preferences('user_bookmarks'));

        if (in_array(($bookmarkurl . "|" . $title), $bookmarks)) {
            print_error(get_string('error:bookmarkalreadyexists', 'block_user_bookmarks'), 'admin');
            die;
        }

    } else {
        $bookmarks = array();
    }

    //adds the bookmark at end of array
    $title = str_replace(',', '\\,', $title);
    $bookmarks[] = $bookmarkurl . "|" . $title;
    $bookmarks = implode(',', $bookmarks);

    //adds to preferences table
    set_user_preference('user_bookmarks', $bookmarks);

    global $CFG;
    header("Location: " . $CFG->wwwroot . $bookmarkurl);
} else {
    print_error(get_string('error:invalidsection', 'block_user_bookmarks'), 'admin');
    die;
}

