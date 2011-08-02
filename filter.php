<?PHP
      // Allows Skype icons

function skypeicons_filter($courseid, $text) {

    global $CFG;

    if (!empty($CFG->formatstring)) {
        return $text;
    }

/// Do a quick check using stripos to avoid unnecessary work
    if (strpos($text, '(') === false) {
        return $text;
    }

    static $e = array();
    static $img = array();
    static $emoticons = null;

    if (is_null($emoticons)) {
        $images = array('angel', 'angry', 'bandit', 'beer', 'bigsmile', 'blush', 'bow', 'brokenheart', 'bug', 'cake', 'call', 'cash', 'clapping', 'coffee', 'cool', 'crying', 'dance', 'devil', 'doh', 'drink', 'drunk', 'dull', 'envy', 'evilgrin', 'flower', 'giggle', 'handshake', 'headbang', 'heidy', 'heart', 'hi', 'hug', 'inlove', 'itwasntme', 'kiss', 'lipssealed', 'mail', 'makeup', 'middlefinger', 'mmm', 'mooning', 'movie', 'muscle', 'music', 'nerd', 'ninja', 'no', 'party', 'phone', 'pizza', 'puke', 'rain', 'rock', 'sadsmile', 'skype', 'sleepy', 'smile', 'smoke', 'speechless', 'star', 'sun', 'surprised', 'sweating', 'talking', 'thinking', 'time', 'toivo', 'tongueout', 'wait', 'wink', 'wondering', 'worried', 'yawn');

        // Standard ones
        foreach ($images as $image) {
            $emoticons[' ('.$image.')'] = $image;
        }

        // Aliases
        $emoticons[' (bear)'] = 'hug';
        $emoticons[' (wave)'] = 'hi';
        $emoticons[' (flex)'] = 'muscle';
        $emoticons[' (squirrel)'] = 'heidy';
        $emoticons[' (hiedy)'] = 'heidy';
        $emoticons[' (banghead)'] = 'headbang';
        $emoticons[' (mm)'] = 'mmm';
        $emoticons[' (pi)'] = 'pizza';
    }

    if (empty($img)) {  /// After the first time this is not run again
        $e= array();
        $img = array();
        foreach ($emoticons as $emoticon => $image){
            $e[] = $emoticon;
            $img[] = ' <img alt="'. $emoticon .'" title="'. $emoticon .'" width="19" height="19" src="'. $CFG->wwwroot .'/filter/skypeicons/pix/'. $image .'.gif" />';
        }
    }

    // Detect all the <script> zones to take out
    $excludes = array();
    preg_match_all('/<script language(.+?)<\/script>/is',$text,$list_of_excludes);

    // Take out all the <script> zones from text
    foreach (array_unique($list_of_excludes[0]) as $key=>$value) {
        $excludes['<+'.$key.'+>'] = $value;
    }
    if ($excludes) {
        $text = str_replace($excludes,array_keys($excludes),$text);
    }

/// this is the meat of the code - this is run every time
    $text = str_replace($e, $img, $text);

    // Recover all the <script> zones to text
    if ($excludes) {
        $text = str_replace(array_keys($excludes),$excludes,$text);
    }

    return $text;
}

