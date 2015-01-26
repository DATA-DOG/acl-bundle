<?php

namespace AclBundle\Entity;

trait PolicyTrait
{
    /**
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\Column(type="integer")
     * @Doctrine\ORM\Mapping\GeneratedValue
     */
    protected $id;

    /**
     * @Doctrine\ORM\Mapping\Column(length=255)
     */
    protected $resource;

    /**
     * @Doctrine\ORM\Mapping\Column(type="boolean")
     */
    protected $allow;

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setAllow($allow)
    {
        $this->allow = $allow;
        return $this;
    }

    public function getAllow()
    {
        return $this->allow;
    }

    public function getId()
    {
        return $this->id;
    }
}
