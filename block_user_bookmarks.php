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
 * User Bookmarks Block page.
 *
 * @package    block
 * @subpackage user_bookmarks
 * Version details
 * @copyright  2012 Moodle
 * @author     Authors of Admin Bookmarks:-
 *               2006 vinkmar
 *               2011 Rossiani Wijaya (updated)
 *             Authors of User Bookmarks This Version:-
 *               2013 Jonas Rueegge
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * The user bookmarks block class
 */
class block_user_bookmarks extends block_base {

    /** @var string */
    public $blockname = null;

    /** @var bool */
    protected $contentgenerated = false;

    /** @var bool|null */
    protected $docked = null;

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('blocktitle', 'block_user_bookmarks');
    }

    /**
     * All multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        if (has_capability('moodle/site:config', context_system::instance())) {
            return array('all' => true);
        } else {
            return array('site' => true);
        }
    }

    public function specialization() {
        if(!isset($this->config)){
            $this->config = new stdClass();
            $this->config->title = get_string('blocktitle', 'block_user_bookmarks');
        }
    }

    /**
     * Gets the content for this block
     * Needed Strings for Multilingual Support for this function
     * avaiable via get_string(); (@JR2013)
     */
    function get_content() {
        global $CFG, $PAGE, $OUTPUT;

        $this->config->title = get_string('blocktitle', 'block_user_bookmarks');
        // First check if we have already generated, don't waste cycles
        if ($this->contentgenerated === true) {
            return $this->content;
        }
        $this->contentgenerated = true;

        require_once($CFG->libdir.'/adminlib.php');
        $this->content = new stdClass();

        $noscript = '<noscript>'.get_string('error:noscript', 'block_user_bookmarks').'</noscript>';
        $javascript='    <script type="text/javascript">
    function updateBookmark(bookmarkURL, defaultTitle, sesskey, wwwroot) {
        var newBookmarkTitle = prompt(\''.get_string('editbookmarktitle', 'block_user_bookmarks').'\',defaultTitle);
        if (newBookmarkTitle == "" || newBookmarkTitle == null) {
        newBookmarkTitle = defaultTitle;
        }else {
        var redirectPage = wwwroot + "/blocks/user_bookmarks/update.php?bookmarkurl=" + escape(bookmarkURL)
                 + "&title=" + encodeURIComponent(newBookmarkTitle) + "&sesskey=" + sesskey;
        window.location = redirectPage;
        }
    }
    function deleteBookmark(bookmarkURL, sesskey, wwwroot) {
        var redirectPage = wwwroot + "/blocks/user_bookmarks/delete.php?bookmarkurl="
                 + escape(bookmarkURL) + "&sesskey=" + sesskey;
        window.location = redirectPage;
    }
    function addBookmark(bookmarkURL, defaultTitle, sesskey, wwwroot) {
        var newBookmarkTitle = prompt(\'' .get_string('enterbookmarktitle', 'block_user_bookmarks'). '\',defaultTitle);
        if (newBookmarkTitle == "" || newBookmarkTitle == null) {
               newBookmarkTitle = defaultTitle;
        } else {
            var redirectPage = wwwroot + "/blocks/user_bookmarks/create.php?bookmarkurl=" + escape(bookmarkURL)
                     + "&title=" + encodeURIComponent(newBookmarkTitle) + "&sesskey=" + sesskey;
            window.location = redirectPage;
        }
    }
    </script>';

        $bookmarks = $this->get_bookmark(get_user_preferences('user_bookmarks'));

        $bookmarkurls = array();
        $contents = array();

        // generating html for the bookmark
        $deletestr = get_string('deletebookmark', 'block_user_bookmarks');
        $editstr = get_string('editbookmark', 'block_user_bookmarks');
        foreach ($bookmarks as $bookmark) {
            // bookmark link
            $contentlink = html_writer::link($bookmark['url'], $bookmark['title']);

            // delete URL
            $bookmarkdeleteurl = new moodle_url('/blocks/user_bookmarks/delete.php', array('bookmarkurl'=>$bookmark['url']->out_as_local_url(false), 'sesskey'=>sesskey()));
            $deletelink = html_writer::link($bookmarkdeleteurl, $OUTPUT->pix_icon('delete', $deletestr, 'block_user_bookmarks', array('title' => $deletestr)));
            $editlink = '<a style="cursor: pointer;" onClick="updateBookmark(\''
               .$bookmark['url']->out_as_local_url(false).'\', \''.$bookmark['title'].'\', \''.sesskey().'\', \''.$CFG->wwwroot.'\');">'
               .$OUTPUT->pix_icon('edit', $editstr, 'block_user_bookmarks', array('title' => $editstr)).'</a>';

            //setting layout for the bookmark and its delete and edit buttons
            $contents[] = html_writer::tag('li', $contentlink . " ".$editlink." " . $deletelink);
            $bookmarkurls[]= html_entity_decode($bookmark['url']->out_as_local_url(false));
        }

        $this->content->text = html_writer::tag('ol', implode('', $contents), array('class' => 'list'));

        $this->content->footer = '';

        $this->page->settingsnav->initialise();
        $node = $this->page->settingsnav->get('root', navigation_node::TYPE_SETTING);

        $bookmarkurl = htmlspecialchars_decode(str_replace($CFG->wwwroot,'',$PAGE->url));
        $bookmarktitle = $PAGE->title;

        if (in_array($bookmarkurl, $bookmarkurls)) {
            //this prints out the link to unbookmark a page
            $this->content->footer = $javascript . $noscript . '
    <form style="cursor: hand;">
    <a style="cursor: pointer;" onClick="deleteBookmark(\''.$bookmarkurl.'\', \''.sesskey().'\', \''.$CFG->wwwroot.'\');"> ('
     .get_string('deletebookmarkthissite', 'block_user_bookmarks'). ') </a>
    </form>';
        } else {
            //this prints out link to bookmark a page
            $this->content->footer = $javascript . '
    <form>
    <a style="cursor: pointer;" onClick="addBookmark(\''.$bookmarkurl.'\', \''.$bookmarktitle.'\', \''.sesskey().'\', \''.$CFG->wwwroot.'\');">'
        . get_string('bookmarkpage', 'block_user_bookmarks'). '</a>
    </form>';
        }
        return $this->content;
    }

    private function get_bookmark($preferences) {
        global $CFG;

        if (!trim($preferences)) {
            return array();
        }

        $tempbookmarks = preg_split('/(?<!\\\\),/', $preferences);
        /// Accessibility: markup as a list.
        $bookmarks = array();
        foreach($tempbookmarks as $bookmark) {
            if (strpos($bookmark, '|')===false) {
                continue;
            }

            try {
                //the bookmarks are in the following format- url|title
                //so exploading the bookmark by "|" to get the url and title

                $tempBookmark = explode('|', $bookmark);

                $title = str_replace('\\,', ',', $tempBookmark[1]);
                $contenturl = new moodle_url($CFG->wwwroot . $tempBookmark[0]);
                $bookmarks[]= array('title' => $title, 'url' => $contenturl);
            } catch (Exception $ex) {
                // moodle_url throws an exception when the URL is not quite right
                // we ignore the exception for now
            }
        }
        return $bookmarks;
    }
}

