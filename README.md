# ACL management bundle

## Configuration

This is the default **ACL** bundle configuration:

``` yaml
acl:
  default_allowed: false # means that by default all ACL resources are denied
```

## ACL resource

A resource is basically represented by a string.

    $acl->allowed("app.resource.string", "action");

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

By default **ACL** bundle comes with these providers:

- **ControllerActionProvider** - reads controller ACL annotations.
- **BundleConfigurationProvider** - looks for configurations in the bundles.
- ... to be added

## ACL access providers

ACL access providers, must provide a resource map accessible by currently logged in user.

@TODO:

## Tests

Tested with phpunit. To run all tests:

    composer install
    bin/phpunit

