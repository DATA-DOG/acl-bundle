<?php

namespace AclBundle\Resource\Transformer;

use AclBundle\Resource\TransformerInterface;
use AclBundle\Util;

class DefaultTransformer implements TransformerInterface
{
    public function transform($object)
    {
        return Util::classToResource($object);
    }

    public function supports($object)
    {
        return true;
    }
}
