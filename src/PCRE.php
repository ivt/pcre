<?php

class PCRE {
    /**
     * @param string $text
     * @return string
     */
    static function quote($text) {
        return preg_quote($text);
    }

    private static function check_preg_last_error() {
        $error = preg_last_error();

        if ($error !== PREG_NO_ERROR) {
            static $messages = array(
                PREG_NO_ERROR              => 'No errors',
                PREG_INTERNAL_ERROR        => 'Internal PCRE error',
                PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
                PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
                PREG_BAD_UTF8_ERROR        => 'Malformed UTF-8 data',
                PREG_BAD_UTF8_OFFSET_ERROR => 'The offset didn\'t correspond to the beginning of a valid UTF-8 code point',
            );

            $message = array_key_exists($error, $messages) ? $messages[$error] : 'Unknown error';

            throw new PCREException($message, $error);
        }
    }

    /**
     * @param string $regex
     * @param string $options
     * @return string
     * @throws PCREException
     */
    static function compose($regex, $options = '') {
        // Insert a "\" before each "#" preceded by an even number of "\"s
        $result = "#" . preg_replace("#(?<!\\\\)((\\\\\\\\)*)(\\#)#S", '$1\\\\$3', $regex) . "#$options";

        self::check_preg_last_error();

        return $result;
    }

    /**
     * @param string $regex
     * @param string $subject
     * @param string $options
     * @throws PCREException
     * @return null|PCREMatch
     */
    static function match($regex, $subject, $options = '') {
        $regex      = self::compose($regex, $options);
        $numMatches = preg_match($regex, $subject, $match, PREG_OFFSET_CAPTURE);

        self::check_preg_last_error();

        return $numMatches ? new PCREMatch($match) : null;
    }

    /**
     * @param string $regex
     * @param string $subject
     * @param string $options
     * @throws PCREException
     * @return PCREMatch[]
     */
    static function matchAll($regex, $subject, $options = '') {
        $regex = self::compose($regex, $options);
        preg_match_all($regex, $subject, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        self::check_preg_last_error();

        $result = array();
        foreach ($matches as $match)
            $result[] = new PCREMatch($match);
        return $result;
    }

    /**
     * @param string   $regex
     * @param string   $subject
     * @param string   $replacement
     * @param int|null $limit
     * @param string   $options
     * @throws PCREException
     * @return string
     */
    static function replace($regex, $subject, $replacement, $limit = null, $options = '') {
        $limit  = $limit === null ? -1 : max(0, $limit);
        $regex  = self::compose($regex, $options);
        $result = preg_replace($regex, $replacement, $subject, $limit);

        self::check_preg_last_error();

        if (!is_string($result))
            throw new PCREException(gettype($result) . ' is not a string');
        return $result;
    }

    /**
     * @param string   $regex
     * @param string   $subject
     * @param null|int $limit
     * @param string   $options
     * @throws PCREException
     * @return string[]
     */
    static function split($regex, $subject, $limit = null, $options = '') {
        $limit  = $limit === null ? -1 : max(1, $limit);
        $regex  = self::compose($regex, $options);
        $pieces = preg_split($regex, $subject, $limit);

        self::check_preg_last_error();

        if (!is_array($pieces))
            throw new PCREException(gettype($pieces) . ' is not an array');
        return $pieces;
    }
}

class PCREException extends \Exception {
}

class PCREMatch {
    private $subPatterns = array();

    function __construct(array $subPatterns) {
        // A sub pattern will exist in $subPatterns if it didn't match
        // only if a later sub pattern matched.
        //
        // Example:
        //   match (a)(lol)?b   against "ab" => ["ab", 0], ["a", 0]
        //   match (a)(lol)?(b) against "ab" => ["ab", 0], ["a", 0], ["", -1], ["b", 1]
        //
        // Remove those ones.
        foreach ($subPatterns as $k => $v)
            if ($v[1] == -1)
                unset($subPatterns[$k]);

        $this->subPatterns = $subPatterns;
    }

    /**
     * @param int $subPattern
     * @return int
     */
    function offset($subPattern = 0) {
        return $this->subPatterns[$subPattern][1];
    }

    /**
     * @param int $subPattern
     * @return string
     */
    function text($subPattern = 0) {
        return $this->subPatterns[$subPattern][0];
    }

    /**
     * @param int $subPattern
     * @return bool
     */
    function has($subPattern = 0) {
        return isset($this->subPatterns[$subPattern]);
    }

    function __toString() {
        return $this->text();
    }

    /**
     * @deprecated
     * @see has
     * @param int $subPattern
     * @return bool
     */
    function hasSubPattern($subPattern = 0) {
        return $this->has($subPattern);
    }
}

