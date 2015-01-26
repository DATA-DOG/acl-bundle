<?php

namespace AclBundle\Entity;

trait GroupTrait
{
    /**
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\Column(type="integer")
     * @Doctrine\ORM\Mapping\GeneratedValue
     */
    protected $id;

    /**
     * @Doctrine\ORM\Mapping\Column(length=64)
     */
    protected $name;

    /**
     * @Doctrine\ORM\Mapping\ManyToMany(targetEntity="Policy")
     * @Doctrine\ORM\Mapping\JoinTable(name="acl_group_policies")
     */
    protected $policies;

    public function setPolicies(\Doctrine\Common\Collections\ArrayCollection $policies)
    {
        $this->policies = $policies;
        return $this;
    }

    public function getPolicies()
    {
        return $this->policies;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }
}
