<?php
/**
 * Created on Jul 20, 2012
 *
 * @author Gurvinder Singh
 */

require('../../config.php');

require_login();
$bookmarkurl = required_param('bookmarkurl', PARAM_URL);
$newtitle = required_param('title', PARAM_TEXT);

if (!($preferences = get_user_preferences('user_bookmarks'))) {
    print_error('nobookmarksforuser','admin');
}

$bookmarks = preg_split('/(?<!\\\\),/', $preferences);

$bookmarkupdated = false;

foreach($bookmarks as $index => $bookmark) {
    $tempBookmark = explode('|', $bookmark, 2);
    if ($tempBookmark[0] == $bookmarkurl) {
        $newtitle = str_replace(',', '\\,', $newtitle);
        $newBookmark = $bookmarkurl . "|" . $newtitle;
        $bookmarks[$index] = $newBookmark;
        $bookmarkupdated = true;
    }
}

if ($bookmarkupdated == false) {
    print_error('nonexistentbookmark','admin');
    die;
}

$bookmarkstring = implode(',', $bookmarks);
set_user_preference('user_bookmarks', $bookmarkstring);

header("Location: " . $CFG->wwwroot . $bookmarkurl);
die;
