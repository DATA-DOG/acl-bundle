<?php

namespace AclBundle\Access;

use Prophecy\PhpUnit\ProphecyTestCase;
use AclBundle\Resource\DefaultTree;

class DecisionManagerTest extends ProphecyTestCase
{
    protected function setup()
    {
        parent::setup();

        $provider = $this->prophesize('AclBundle\Access\PolicyProviderInterface');
        $builder = $this->prophesize('AclBundle\Resource\Builder');
        $trans = $this->prophesize('AclBundle\Resource\Transformer\Transformator');

        $builder->tree()->willReturn(new DefaultTree([
            'resource' => [
                'edit' => false,
                'view' => false,
            ],
            'app' => [
                'edit' => false,
            ]
        ], false));

        $provider->policies()->willReturn([
            'resource.view' => true,
            'app' => true,
        ]);

        $this->acl = new DecisionManager($builder->reveal(), $trans->reveal());
        $this->acl->provider($provider->reveal());
    }

    /**
     * @test
     */
    function it_should_allow_access_to_provided_resource()
    {
        $this->assertTrue($this->acl->isGranted('view', 'resource'), 'Expected resource.view to be allowed');
        $this->assertTrue($this->acl->isGranted('edit', 'app'), 'Expected app.edit to be allowed');
    }

    /**
     * @test
     */
    function it_should_deny_restricted_resources()
    {
        $this->assertFalse($this->acl->isGranted('edit', 'resource'), 'Expected resource.edit to be denied');
    }
}
