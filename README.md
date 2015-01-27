# ACL management bundle

ACL comes without any database requirements. It is bare **ACL** manager.
The bundle only registers **resource** and **access policy** providers.
See **DOCTRINE.md** which shows how to configure database for policy management.

- Has symfony profiler bar
- Does not depend on database
- Basic resource and policy concept

## Configuration
This is the default **ACL** bundle configuration:

``` yaml
acl:
  default_allowed: false # means that by default all ACL resources are denied
  resource:
    providers:
      config: true       # by default looks in bundles for ACL resources
      annotations: true  # looks for controller annotations
    transformers:
      doctrine: true     # transforms entities or document resources with an ID at the end
```

## ACL resource
A resource is basically represented by a string.

``` php
$acl->isGranted("action", "app.resource.string");
```

Would be **"app.resource.string.action"**. Action is concatenated. That way
it is easier to store and match resources.

- **app.resource.string** - is a resource acccess point.
- **action** - is any action that can be done with the resource.

## ACL resource providers
Providers are used to collect all ACL resources from bundles.
The ACL provider interface:

``` php
namespace AclBundle\Resource;

interface ProviderInterface
{
    /**
     * Get a list of available ACL resources
     *
     * @return array - ['resource.string.action', ...]
     */
    function resources();
}
```

All provider services must be tagged with **acl.resource.provider**. They should build
a resource map as required by interface.

### Bundle configuration
This type of ACL resource provider is enabled by default. It looks for configuration file:
**../VendorBundle/Resources/config/acl_resources.yml** and loads all resources from each bundle.

```yaml
resources:
  - app_bundle.entity.page.view
  - app_bundle.entity.page.edit
```

## ACL policy providers
ACL policy providers must implement **AclBundle\Access\PolicyProviderInterface** and implement
one method which return a list of policies, where key is a resource or resource branch and
value is boolean - whether the resource is granted or denied.

Given we have these resources:
```yaml
resources:
  - app.user.edit
  - app.user.view
  - app.user.remove
  - app.user.add
```

We can make policies for leaf actions:
```yaml
acl:
  access:
    policies:
      luke@skywalker.com:
        - { resource: app.user.edit, granted: true }
        - { resource: app.user.view, granted: true }
        - { resource: app.user.add,  granted: true }
```

Or we can do the same thing by granting access to the branch and denying leaf:
```yaml
acl:
  access:
    policies:
      luke@skywalker.com:
        - { resource: app.user,        granted: true }
        - { resource: app.user.remove, granted: false }
```

**NOTE:** The configuration above is the **ACL** bundle extension configuration. Which should be located in
kernel configuration directory.

### Config provider
For very simple use cases, config provider may be used. To enable it, acl configuration must contain
some accesses in the map:

``` yaml
acl:
  access:
    policies:
      admin:
        - { resource: app_bundle, allow: true }          # allow every action for all resources under app_bundle
      someusername:
        - { resource: some.resource, allow: true }       # allow all actions on some.resource
        - { resource: some.resource.edit, allow: false } # but deny - some.resource.edit
        - { another.resource.somewhere.create }          # default allowed
```

It will load this access map based on username of currently logged user from security context.
Though the user model must implement **Symfony\Component\Security\Core\User\UserInterface**

### ACL resource transformers

Sometimes it may be useful to transform an object to a specific resource with identifier for
deep permission checks. As an example we could have **form type** resources identified by name:

``` php
use AclBundle\Util;
use AclBundle\Resource\TransformerInterface;
use Symfony\Component\Form\FormTypeInterface;

class FormTransformer implements TransformerInterface
{
    public function supports($object)
    {
        return $object instanceof FormTypeInterface;
    }

    public function transform($object)
    {
        return 'form.' . Util::underscore($object->getName());
    }
}
```

This transformer service then may be registered with tag: **acl.resource.transformer**, it accepts a priority attribute.
When **acl** actions may be checked like:

``` php
$container->get('acl.access.decision_manager')->isGranted('edit', $formTypeObject);
```

**NOTE:** these resources must be provided, either through configuration or by resource provider service.

## Tests
Tested with phpunit. To run all tests:

    composer install
    bin/phpunit

