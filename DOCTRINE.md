# Doctrine backend for ACL

The example will provide these features:
- access policy groups
- user specific policies to take precedence over groups
- policy provider using DBAL connection

## Entities

Policy group, allows to group policies.

``` php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="acl_groups")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=64)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Policy", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinTable(name="acl_group_policies")
     */
    private $policies;

    public function __construct($name, ArrayCollection $policies = null)
    {
        $this->name = $name;
        $this->policies = $policies ?: new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPolicies(ArrayCollection $policies)
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
}
```

Policy.

```php
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="acl_policies")
 */
class Policy
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=255)
     */
    private $resource;

    /**
     * @ORM\Column(type="boolean")
     */
    private $granted;

    public function __construct($resource, $granted)
    {
        $this->resource = $resource;
        $this->granted = $granted;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setGranted($granted)
    {
        $this->granted = $granted;
        return $this;
    }

    public function getGranted()
    {
        return $this->granted;
    }
}
```

Add relations to your **User** entity:

``` php
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    // ....

    /**
     * @ORM\ManyToMany(targetEntity="Policy")
     * @ORM\JoinTable(name="acl_user_policies")
     */
    private $policies;

    /**
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="acl_user_groups")
     */
    private $policyGroups;

    public function __construct()
    {
        $this->policies = new ArrayCollection();
        $this->policyGroups = new ArrayCollection();
    }

    public function setPolicies(ArrayCollection $policies)
    {
        $this->policies = $policies;
        return $this;
    }

    public function getPolicies()
    {
        return $this->policies;
    }

    public function setPolicyGroups(ArrayCollection $policyGroups)
    {
        $this->policyGroups = $policyGroups;
        return $this;
    }

    public function getPolicyGroups()
    {
        return $this->policyGroups;
    }
}
```

**NOTE:** for only DBAL, the schema could be created.

## Policy provider

In order to load these policies for a user, we need to register a policy provider:

``` php
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use AclBundle\Access\PolicyProviderInterface;

class AppPolicyProvider implements PolicyProviderInterface
{
    private $connection;
    private $context;
    private $anonymous;

    public function __construct(Connection $conn, SecurityContextInterface $context, array $anonymous = [])
    {
        $this->connection = $conn;
        $this->context = $context;
        $this->anonymous = $anonymous; // anonymous user policies if needed
    }

    public function policies()
    {
        if (!$token = $this->context->getToken()) {
            return $this->anonymous;
        }

        if (!$token instanceof TokenInterface) {
            return $this->anonymous;
        }

        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return $this->anonymous;
        }

        $sqlGroupPolicies = <<<SQL
SELECT p.resource, p.granted
FROM acl_user_groups AS ug
INNER JOIN acl_group_policies AS gp ON ug.group_id = gp.group_id
INNER JOIN acl_policies AS p ON gp.policy_id = p.id
WHERE ug.user_id = ?
SQL;

        $sqlUserPolicies = <<<SQL
SELECT p.resource, p.granted
FROM acl_user_policies AS up
INNER JOIN acl_policies AS p ON up.policy_id = p.id
WHERE up.user_id = ?
SQL;
        $groupPolicies = $this->connection->fetchAll($sqlGroupPolicies, [$user->getId()]);
        ksort($groupPolicies); // will ensure that branch policies will go first

        $userPolicies = $this->connection->fetchAll($sqlUserPolicies, [$user->getId()]);
        ksort($userPolicies); // will ensure that branch policies will go first

        $policies = [];
        // the merge will override permissions in case if the same policy resource
        // user specific policies will take precedence
        foreach (array_merge($groupPolicies, $userPolicies) as $policy) {
            $policies[$policy['resource']] = (bool)$policy['granted'];
        }

        return $policies;
    }
}
```

Register this provider as a tagged service:

``` yaml
services:
  app.acl_policy.provider:
    class: AppPolicyProvider
    arguments: [@doctrine.dbal.default_connection, @security.context]
    tags:
      - { name: acl.policy.provider }
```

## Add some policies

Given we have some ACL resources defined.
``` yaml
# file: AppBundle/Resources/config/acl_resources.yml
resources:
  - app.user.edit
  - app.user.view
  - app.user.remove
  - app.user.add
```

Then we can create policy groups for users:
``` php
$em = $container->get('doctrine')->getManager();

$grantAppUser = new Policy('app.user', true);
$denyAppUserRemove = new Policy('app.user.remove', false);

$users = new Group('Users', new ArrayCollection([
    $grantAppUser,
    $denyAppUserRemove,
]));

$admins = new Group('Admins', new ArrayCollection([
    $grantAppUser,
]));

$em->persist($users);
$em->persist($admins);

$luke = $em->getRepository('AppBundle:User')->findOneByUsername('luke');
$luke->getPolicyGroups()->add($users);

$specialUser = $em->getRepository('AppBundle:User')->findOneByUsername('special');
$specialUser->getPolicyGroups()->add($users);
$specialUser->getPolicies()->add(new Policy('app.user.remove', true));

$em->flush();
```


