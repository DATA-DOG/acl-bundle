# ACL management bundle

## Configuration
This is the default **ACL** bundle configuration:

``` yaml
acl:
  default_allowed: false # means that by default all ACL resources are denied
  resource_providers:
    bundle_configuration: true # by default looks in bundles for ACL resources
```

## ACL resource
A resource is basically represented by a string.

    $acl->isAllowed("action", "app.resource.string");

Would be **"app.resource.string.action"**. Action is concatenated. That way
it is easier to store and match resources.

- **app.resource.string** - is a resource acccess point.
- **action** - is any action that can be done with the resource bellow.

## ACL resource providers
Providers are used to collect all ACL resources from bundles.
The ACL provider interface:

``` php
<?php

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
**../VendorBundle/Resources/config/acl.yml** and loads all resources from each bundle.

```yaml
resources:
  - app_bundle.entity.page.view
  - app_bundle.entity.page.edit
```

## ACL access providers
ACL access providers, must provide a resource list accessible by currently logged in user.
Every provider must implement the same **AclBundle\Resource\ProviderInterface**.

### Config provider
For very simple use cases, config provider may be used. To enable it, acl configuration must contain
some accesses in the map:

``` yaml
acl:
  accesses:
    admin:
      - app_bundle
    someusername:
      - some.resource.view
      - another.resource.somewhere.create
```

It will load this access map based on username of currently logged user from security context.
Though the user model must implement **Symfony\Component\Security\Core\User\UserInterface**

## Tests
Tested with phpunit. To run all tests:

    composer install
    bin/phpunit

