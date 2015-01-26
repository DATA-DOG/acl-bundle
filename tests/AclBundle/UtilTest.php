<?php

namespace AclBundle;

use Prophecy\PhpUnit\ProphecyTestCase;

class UtilTest extends ProphecyTestCase
{
    /**
     * @test
     */
    function it_should_convert_camelcased_words_to_underscored_lowercased()
    {
        $this->assertSame('camel_cased_class_name', Util::underscore('CamelCasedClassName'));
    }

    /**
     * @test
     */
    function it_should_convert_class_to_resource()
    {
        $this->assertSame(Util::classToResource('CamelCased\\Class\\Name'), 'camel_cased.class.name');
    }
}
