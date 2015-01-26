<?php

namespace AclBundle\Resource\Transformer;

use AclBundle\Resource\TransformerInterface;

class Transformator
{
    private $transformers = [];

    public function __construct()
    {
        $transformers = array_map(function(TransformerInterface $trans) {
            return $trans;
        }, func_get_args());
    }

    public function transform($object)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($object)) {
                return $transformer->transform($object);
            }
        }
        throw new \RuntimeException("There was no transformer to transform " . get_class($object));
    }
}
