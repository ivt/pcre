<?php

namespace PCRETest;

use IVT\AssertionFailed;
use PCRE;

class PCRETest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        try {
            PCRE::test();
        }
        catch ( AssertionFailed $e ) {
            $this->fail( "PCRE::test() failed." );
        }
        return;
    }

    function testMatch()
    {
        $this->assertNotNull( PCRE::match( "^F", "Foo" ) );
        $this->assertNull( PCRE::match( "^F", "Goo" ) );
    }
}
