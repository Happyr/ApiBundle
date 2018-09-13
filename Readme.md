# Happyr Api Bundle

[![Latest Version](https://img.shields.io/github/release/Happyr/ApiBundle.svg?style=flat-square)](https://github.com/Happyr/ApiBundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/Happyr/ApiBundle/master.svg?style=flat-square)](https://travis-ci.org/Happyr/ApiBundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Happyr/ApiBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/ApiBundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/Happyr/ApiBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/ApiBundle)
[![Total Downloads](https://img.shields.io/packagist/dt/happyr/api-bundle.svg?style=flat-square)](https://packagist.org/packages/happyr/api-bundle)


### To Install

Run the following in your project root, assuming you have composer set up for your project
```sh
composer require happyr/api-bundle
```

Add the bundle to app/AppKernel.php

```php
$bundles = [
    // ...
    new Happyr\ApiBundle\HappyrApiBundle(),
];
```

## Security

Wsse is built in and can be enabled - disabled by default.

To enable and configure it, in config.yml add
```yaml
happyr_api:
    wsse:
        user_provider: "security.user.provider.concrete.in_memory"
        cache_service: "cache.provider.redis"
        lifetime: 300
        debug: false # Set to true to disable WSSE completely. You will always be authenticated. 
```

And in security.yml configure your provider where you store users to something like this
```yaml
security:
    providers:
        in_memory:
            memory:
                users:
                    username:
                        password: password
                        roles: ['ROLE_API_USER']
```

And under firewalls in security.yml, add a new firewall like so
```yaml
security:
    firewalls:
        main:
            pattern:   ^/api/
            stateless: true
            wsse:      true

```

## Exception listener

Exception listener is enabled by default. It will catch uncaught exceptions and return formatted json response.

Here is an example configuration:
```yaml
happyr_api:
  exception_listener:
    enabled: false # disables response listener 
    path_prefix: '/api/' # path prefix to enable listener on. By default its enabled for any path
```
