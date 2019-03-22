# Expressive 3 and above NOT SUPPORTED
For for future support, including expressive 3 support, try this other project: https://github.com/fduarte42/zend-expressive-static-files

# Serve Static
A PSR-15 middleware that serves static assets for you

Example usage:
```php
$app->pipe('/fun-module/assets', new \Reliv\ServeStatic\ServeStaticMiddleware(
    __DIR__ . '/../vendor/fund-module/public',
    ['publicCachePath' => __DIR__ . '/../public/fun-module/assets']
));
```
