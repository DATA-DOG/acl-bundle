<?php

namespace AclBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AclBundle\DependencyInjection\Compiler\AclProviderPass;

class AclBundle extends Bundle
{
    public function build(ContainerBuilder $c)
    {
        $c->addCompilerPass(new AclProviderPass());
    }
}
