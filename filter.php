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
 * Skype icons filter
 *
 * @package    filter_skypeicons
 * @copyright  2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_skypeicons extends moodle_text_filter {

    /**
     * @var array global configuration for this filter
     *
     * Note: this has been borrowed from {@link filter_emoticon} because
     * right now it's impossible to extend from that class. If some day
     * we plan proper support for multiple emoticon filters... and it's possible
     * to extend from that class, then method could be deleted safely.
     */
    protected static $globalconfig;

    /**
     * @var bool filter in case sensitive mode. Some day, this may be a filter setting.
     */
    protected $casesensitive = true;

    /**
     * @var bool filter in full match mode. Some day, this may be a filter setting.
     */
    protected $fullmatch = true;

    /**
     * @var array images provided by the filter.
     */
    protected $images = array(
        'angel', 'angry', 'bandit', 'beer', 'bigsmile', 'blush', 'bow', 'brokenheart',
        'bug', 'cake', 'call', 'cash', 'clapping', 'coffee', 'cool', 'crying', 'dance',
        'devil', 'doh', 'drink', 'drunk', 'dull', 'envy', 'evilgrin', 'flower', 'giggle',
        'handshake', 'headbang', 'heidy', 'heart', 'hi', 'hug', 'inlove', 'itwasntme',
        'kiss', 'lipssealed', 'mail', 'makeup', 'middlefinger', 'mmm', 'mooning', 'movie',
        'muscle', 'music', 'nerd', 'ninja', 'no', 'party', 'phone', 'pizza', 'puke',
        'rain', 'rock', 'sadsmile', 'skype', 'sleepy', 'smile', 'smoke', 'speechless',
        'star', 'sun', 'surprised', 'sweating', 'talking', 'thinking', 'time', 'toivo',
        'tongueout', 'wait', 'wink', 'wondering', 'worried', 'yawn');

    /**
     * @var array some aliases pointing to original images (key is alias, value is image).
     */
    protected $imagealiases = array(
        'bear' => 'hug',
        'wave' => 'hi',
        'flex' => 'muscle',
        'squirrel' => 'heidy',
        'hiedy' => 'heidy',
        'banghead' => 'headbang',
        'mm' => 'mmm',
        'pi' => 'pizza');

    /**
     * Apply the filter to the text
     *
     * Note this filter is very similar to the {@link filter_emoticons} filter
     * that uses the {@link emoticon_manager} and ideally such manager should be
     * able to support multiple icon sets of emoticon filters, providing a nice
     * integration with editor and so on. But right now, it's all stored into a
     * global variable $CFG->emoticons and, although hackable, better keep it
     * unmodified. If some day its API is expanded, then this filter could become,
     * simply, an emoticon provider.
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
        global $OUTPUT;

        // Simple lang based cache to store the filter objects by request.
        // TODO: Move this static cache to use the MUC.
        static $emoticonslist = array();

        if (!isset($options['originalformat'])) {
            // If the format is not specified, we are probably called by {@see format_string()}
            // in that case, it would be dangerous to replace text with the image because it could
            // be stripped. therefore, we do nothing.
            return $text;
        }

        if (!in_array($options['originalformat'], explode(',', $this->get_global_config('formats')))) {
            // If the format is not one of the configured formats where
            // the filter is configured to work, we do nothing.
            return $text;
        }

        if (strpos($text, '(') === false) {
            // All the icons in this filter have opening parenthesis so,
            // if the texts does not contain any, we do nothing.
            return $text;
        }

        // Get language. We'll be catching by them.
        $lang = current_language();

        // Let's cache all the filtering objects for a given language.
        if (!isset($emoticonslist[$lang])) {
            // Use emoticon manager facilities (some).
            $manager = get_emoticon_manager();

            // Create all the standard filter objects for each image.
            foreach ($this->images as $image) {
                // Create the emoticon object.
                $emoticon = new stdClass();
                $emoticon->text = $image;
                $emoticon->imagename = $image;
                $emoticon->imagecomponent = 'filter_skypeicons';
                $emoticon->altidentifier = $emoticon->text;
                $emoticon->altcomponent = $emoticon->imagecomponent;
                // Define the search and replace strings.
                $search = '(' . $image . ')';
                $replace = $OUTPUT->render($manager->prepare_renderable_emoticon($emoticon));
                // Nasty hack to split the calculated img tag into start, search and end. We cannot
                // pass the $replace alone because it's cleaned by strip_tags().
                if (preg_match("~(.*)($emoticon->imagename)(\" />)~", $replace, $matches)) {
                    // Create the filter object.
                    $filter = new filterobject($search, $matches[1], $matches[3], $this->casesensitive, $this->fullmatch, $matches[2]);
                    $emoticonslist[$lang][$search] = $filter;
                }
            }

            // Now process the aliases, creating standard filter objects too.
            foreach ($this->imagealiases as $alias => $image) {
                // If the alias is already defined, skip.
                if (in_array($alias, $this->images)) {
                    continue;
                }
                // Create the emoticon object.
                $emoticon = new stdClass();
                $emoticon->text = $alias;
                $emoticon->imagename = $image;
                $emoticon->imagecomponent = 'filter_skypeicons';
                $emoticon->altidentifier = $emoticon->text;
                $emoticon->altcomponent = $emoticon->imagecomponent;
                // Define the search and replace strings.
                $search = '(' . $alias . ')';
                $replace = $OUTPUT->render($manager->prepare_renderable_emoticon($emoticon));
                // Nasty hack to split the calculated img tag into start, search and end. We cannot
                // pass the $replace alone because it's cleaned by strip_tags().
                if (preg_match("~(.*)($emoticon->imagename)(\" />)~", $replace, $matches)) {
                    // Create the filter object.
                    $filter = new filterobject($search, $matches[1], $matches[3], $this->casesensitive, $this->fullmatch, $matches[2]);
                    $emoticonslist[$lang][$search] = $filter;
                }
            }

            if (!empty($emoticonslist[$lang])) {
                // Remove dupes, just in case.
                $emoticonslist[$lang] = filter_remove_duplicates($emoticonslist[$lang]);
            }
        }
        // Define the list of tags where we are not going to filter. Note this
        // is the same than the default used by filter_phrases(), but we take out
        // the <a> tag, so it will be possible to replace within link texts.
        $ignoretagsopen  = array(
            '<head>' , '<nolink>' , '<span class="nolink">',
            '<script(\s[^>]*?)?>', '<textarea(\s[^>]*?)?>',
            '<select(\s[^>]*?)?>');
        $ignoretagsclose = array(
            '</head>', '</nolink>', '</span>',
            '</script>', '</textarea>', '</select>');
        // Let's filter all the filter objects, overriding ignore tags.
        $text = filter_phrases($text, $emoticonslist[$lang], $ignoretagsopen, $ignoretagsclose, true);

        return $text;
    }

    ////////////////////////////////////////////////////////////////////////////
    // internal implementation starts here
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the global filter setting
     *
     * If the $name is provided, returns single value. Otherwise returns all
     * global settings in object. Returns null if the named setting is not
     * found.
     *
     * Note: this has been borrowed from {@link filter_emoticon} because
     * right now it's impossible to extend from that class. If some day
     * we plan proper support for multiple emoticon filters... and it's possible
     * to extend from that class, then method could be deleted safely.
     *
     * @param mixed $name optional config variable name, defaults to null for all
     * @return string|object|null
     */
    protected function get_global_config($name=null) {
        $this->load_global_config();
        if (is_null($name)) {
            return self::$globalconfig;

        } elseif (array_key_exists($name, self::$globalconfig)) {
            return self::$globalconfig->{$name};

        } else {
            return null;
        }
    }

    /**
     * Makes sure that the global config is loaded in $this->globalconfig
     *
     * Note: this has been borrowed from {@link filter_emoticon} because
     * right now it's impossible to extend from that class. If some day
     * we plan proper support for multiple emoticon filters... and it's possible
     * to extend from that class, then method could be deleted safely.
     *
     * @return void
     */
    protected function load_global_config() {
        if (is_null(self::$globalconfig)) {
            self::$globalconfig = get_config(get_class($this));
        }
    }
}
