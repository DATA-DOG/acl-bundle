<?php

namespace AclBundle\Resource\Transformer;

use AclBundle\Resource\TransformerInterface;
use AclBundle\Util;
use Doctrine\Common\Persistence\ManagerRegistry;

class DoctrineTransformer implements TransformerInterface
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function transform($object)
    {
        $class = get_class($object);
        $id = $this->doctrine->getManagerForClass($class)->getUnitOfWork()->getSingleIdentifierValue($object);
        if (null === $id) {
            throw new \RuntimeException("Given object of \"{$class}\" is not managed by unit of work.");
        }
        return implode('.', [Util::classToResource($class), $id]);
    }

    public function supports($object)
    {
        return null !== $this->doctrine->getManagerForClass(get_class($object));
    }
}
