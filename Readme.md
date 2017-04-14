# Happyr Api Bundle

[![Latest Version](https://img.shields.io/github/release/Happyr/api-bundle.svg?style=flat-square)](https://github.com/Happyr/api-bundle/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/Happyr/api-bundle.svg?style=flat-square)](https://travis-ci.org/Happyr/api-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Happyr/api-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/api-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/Happyr/api-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/Happyr/api-bundle)
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
        user_provider: "@security.user.provider.in_memory.user" # The @-sign is needed
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
