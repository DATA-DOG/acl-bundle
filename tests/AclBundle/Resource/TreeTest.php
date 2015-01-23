<?php

namespace AclBundle\Resource;

use Prophecy\PhpUnit\ProphecyTestCase;

class TreeTest extends ProphecyTestCase
{
    protected function setup()
    {
        parent::setup();

        $this->blank = new DefaultTree([], false);

        $this->full = new DefaultTree([
            'some' => [
                'entity' => [
                    'edit' => false,
                    'delete' => false,
                    'view' => false,
                ],
                'resource' => [
                    'edit' => false,
                ]
            ],
        ], false);

        $this->inverse = new DefaultTree([
            'app' => [
                'res' => [
                    'main' => [
                        'see' => true,
                    ]
                ]
            ],
            'some' => [
                'entity' => [
                    'delete' => true,
                ]
            ],
        ], true);
    }

    /**
     * @expectedException RuntimeException
     * @test
     */
    function it_should_fail_with_undefined_resource()
    {
        $this->blank->map(['some.resource']);
    }

    /**
     * @test
     */
    function it_should_map_access_resource_action()
    {
        $tree = $this->full->map(['some.resource.edit'])->all();
        $this->assertTrue($tree['some']['resource']['edit'], "Expected some.resource.edit action to turn to true");
    }

    /**
     * @test
     */
    function it_should_map_access_resource_recursive()
    {
        $tree = $this->full->map(['some'])->all();

        $this->assertTrue($tree['some']['resource']['edit'], "Expected some.resource.edit action to turn to true");
        $this->assertTrue($tree['some']['entity']['edit'], "Expected some.entity.edit action to turn to true");
        $this->assertTrue($tree['some']['entity']['delete'], "Expected some.entity.delete action to turn to true");
        $this->assertTrue($tree['some']['entity']['view'], "Expected some.entity.view action to turn to true");
    }

    /**
     * @test
     */
    function it_should_map_access_resource_action_on_inverse_tree()
    {
        $tree = $this->inverse->map(['some.entity.delete'])->all();

        $this->assertFalse($tree['some']['entity']['delete'], "Expected some.entity.delete action to turn to false");
    }
}
