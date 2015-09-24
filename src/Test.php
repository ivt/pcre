<?php

class PCRETest extends \PHPUnit_Framework_TestCase {
    function testReplace() {
        self::assertEquals(
            PCRE::replace(
                '(#\w+) (\w+)',
                '#foo bar #baz boo #bary bob',
                '$1 LOL',
                2
            ),
            '#foo LOL #baz LOL #bary bob'
        );
    }

    function testSplit() {
        self::assertEquals(
            PCRE::split(
                'ab*',
                'cabbcacbbcabbbcbcabb',
                4
            ),
            array('c', 'c', 'cbbc', 'cbcabb')
        );
    }

    function testNonMatchingSubGroups() {
        $match = PCRE::match('(a)(lol)?b', 'ab');
        self::assertEquals($match->has(0), true);
        self::assertEquals($match->has(1), true);
        self::assertEquals($match->has(2), false);

        $match = PCRE::match('(a)(lol)?(b)', 'ab');
        self::assertEquals($match->has(0), true);
        self::assertEquals($match->has(1), true);
        self::assertEquals($match->has(2), false);
        self::assertEquals($match->has(3), true);
    }

    function testMatch() {
        $this->assertNotNull(PCRE::match("^F", "Foo"));
        $this->assertNull(PCRE::match("^F", "Goo"));
    }
}
