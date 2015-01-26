<?php

namespace AclBundle\Entity;

trait UserTrait
{
    /**
     * @Doctrine\ORM\Mapping\ManyToMany(targetEntity="Policy")
     * @Doctrine\ORM\Mapping\JoinTable(name="acl_user_policies")
     */
    protected $policies;

    /**
     * @Doctrine\ORM\Mapping\ManyToMany(targetEntity="Group")
     * @Doctrine\ORM\Mapping\JoinTable(name="acl_user_groups")
     */
    protected $groups;

    public function setPolicies(\Doctrine\Common\Collections\ArrayCollection $policies)
    {
        $this->policies = $policies;
        return $this;
    }

    public function getPolicies()
    {
        return $this->policies;
    }

    public function setGroups(\Doctrine\Common\Collections\ArrayCollection $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
