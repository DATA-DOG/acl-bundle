<?php

namespace AclBundle\Resource;

use Prophecy\PhpUnit\ProphecyTestCase;

class BuilderTest extends ProphecyTestCase
{
    protected function setup()
    {
        parent::setup();

        $this->builder = new Builder([
            false, // denied access by default
            null, // null cache prefix means no cache class
            true, // true for debug mode
        ]);

        $this->provider = $this->prophesize('AclBundle\Resource\ProviderInterface');
    }

    /**
     * @test
     */
    function it_should_build_an_empty_tree_without_providers()
    {
        $this->assertSame([], $this->builder->tree()->all());
    }

    /**
     * @test
     */
    function it_should_load_resources_from_provider()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            'some.resource.edit',
        ]);

        $expected = [
            'some' => [
                'resource' => [
                    'edit' => false
                ]
            ]
        ];
        $this->assertSame($expected, $this->builder->tree()->all());
    }


    /**
     * @test
     */
    function it_should_load_multiple_resources_from_provider()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            'some.resource.edit',
            'some.entity.edit',
            'some.entity.view',
            'some.entity.delete',
            'app.res.main.see',
        ]);

        $expected = [
            'app' => [
                'res' => [
                    'main' => [
                        'see' => false
                    ]
                ]
            ],
            'some' => [
                'entity' => [
                    'delete' => false,
                    'edit' => false,
                    'view' => false,
                ],
                'resource' => [
                    'edit' => false
                ],
            ],
        ];
        $this->assertSame($expected, $this->builder->tree()->all());
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    function it_should_not_allow_utf_chars_in_resource_names()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            'some.non.ascįį.edit',
        ]);

        $this->builder->tree();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    function it_should_not_allow_dashes_in_resource_names()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            'has-dash.edit',
        ]);

        $this->builder->tree();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    function it_should_not_allow_dot_in_the_beggining_of_resource_name()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            '.starts.with.dot',
        ]);

        $this->builder->tree();
    }

    /**
     * @test
     * @expectedException UnexpectedValueException
     */
    function it_should_not_allow_dot_in_the_end_of_resource_name()
    {
        $this->builder->provider($this->provider->reveal());
        $this->provider->resources()->willReturn([
            'ends.with.dot.',
        ]);

        $this->builder->tree();
    }
}
