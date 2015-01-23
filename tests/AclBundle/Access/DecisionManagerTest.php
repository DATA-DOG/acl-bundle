<?php

namespace AclBundle\Access;

use Prophecy\PhpUnit\ProphecyTestCase;
use AclBundle\Resource\DefaultTree;

class DecisionManagerTest extends ProphecyTestCase
{
    protected function setup()
    {
        parent::setup();

        $provider = $this->prophesize('AclBundle\Resource\ProviderInterface');
        $builder = $this->prophesize('AclBundle\Resource\Builder');

        $builder->tree()->willReturn(new DefaultTree([
            'resource' => [
                'edit' => false,
                'view' => false,
            ],
            'app' => [
                'edit' => false,
            ]
        ], false));
        $builder->validate(['resource.view', 'app'])->shouldBeCalled();

        $provider->resources()->willReturn([
            'resource.view',
            'app',
        ]);

        $this->acl = new DecisionManager($builder->reveal());
        $this->acl->provider($provider->reveal());
    }

    /**
     * @test
     */
    function it_should_allow_access_to_provided_resource()
    {
        $this->assertTrue($this->acl->isAllowed('view', 'resource'), 'Expected resource.view to be allowed');
        $this->assertTrue($this->acl->isAllowed('edit', 'app'), 'Expected app.edit to be allowed');
    }

    /**
     * @test
     */
    function it_should_deny_restricted_resources()
    {
        $this->assertFalse($this->acl->isAllowed('edit', 'resource'), 'Expected resource.edit to be denied');
    }
}
