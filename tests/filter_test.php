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
 * Skype icons filter phpunit tests
 *
 * @package    filter_skypeicons
 * @category   test
 * @copyright  2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/skypeicons/filter.php'); // Include the code to test.

/**
 * Skype icons filter testcase.
 */
class filter_skypeicons_testcase extends basic_testcase {

    public function test_filter_skypeicons() {
        // Some simple words, originally replaced with str_[i]replace(), now
        // processed by the better filter_phrases() stuff. Results are 99% the
        // original ones but now we avoid replacing into tags and links.
        $texts = array(
            // Some non-matching ones.
            '(noexists)' => '(noexists)', // Non-existing.
            'angel' => 'angel',
            'A(angel)' => 'A(angel)', // Non-full matching.
            '(angel)A' => '(angel)A',
            '(Angel)' => '(Angel)',   // Non-case sensitive matching.

            '<p id="(angel)">' => '<p id="(angel)">', // Within tags.
            '<img src="(angel)" />' => '<img src="(angel)" />',
            '<a href="(angel)">hello</a>' => '<a href="(angel)">hello</a>',

            // And now, the matching ones.
            '(angel)' => '<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />',
            ' (angel) ' => ' <img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" /> ',
            '((angel))' => '(<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />)',
            '.(angel),' => '.<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />,',
            ':(angel);' => ':<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />;',
            '+(angel)=' => '+<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />=',
            '+(angel)' => '+<img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" />',
            '<b>(angel)</b>' => '<b><img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" /></b>',

            // Specially in the link texts they must be working (note this is different from default's filter_phrases()
            // behaviour, we are overriding ignore-tags.
            '<a href="hello">(angel)</a>' => '<a href="hello"><img class="emoticon" alt="angel" title="angel"'.
                    ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/angel" /></a>',

            // Also, verify lang strings work, if present ("yawn is the only one having lang string).
            ' (yawn) ' => ' <img class="emoticon" alt="yawn!" title="yawn!"'.
                          ' src="http://www.example.com/moodle/theme/image.php/_s/standard/filter_skypeicons/1/yawn" /> ',
        );

        $filter = new testable_filter_skypeicons();
        $options = array('originalformat' => FORMAT_HTML);

        foreach ($texts as $text => $expected) {
            $msg = "Testing text '$text':";
            $result = $filter->filter($text, $options);

            $this->assertEquals($expected, $result, $msg);
        }
    }
}

/**
 * Subclass for easier testing.
 */
class testable_filter_skypeicons extends filter_skypeicons {
    public function __construct() {
        $this->context = context_system::instance();
    }
}
