# Api Bundle

This is shared stuff for the API

## Functions

The api bundle has an error handler and an exception listener. That is great because it transforms your exceptions to 
actual JSON responses.
If you only want this bundle for the wsse security, you can disable these in the config.yml.

```yaml
happyr_api:
    exception_listener:
        enabled: false
```

## Security

Wsse is built in and can be enabled - disabled by default.

To enable and configure it, in config.yml add
```yaml
happyr_api:
    wsse:
        user_provider: "@security.user.provider.in_memory.user" # The at sign is needed
        cache_service: "cache.provider.redis"
        lifetime: 300
```

And in security.yml configure your provider where you store users to something like this
```yaml
users:
    your_username:
        password: your_password
        roles: ['ROLE_STORE', 'ROLE_FETCH']
```

And under firewalls in security.yml, add a new firewall like so
```yaml
wsse_secured:
    pattern:   ^/
    stateless: true
    wsse:      true
```

## More exceptions

In config_dev.yml you should add the following in order to get nice error messages.

```json
twig:
    exception_controller: 'Happyr\ApiBundle\Controller\DebugExceptionController::showAction'
```
