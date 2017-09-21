# Serve Static
A Zend Expressive 2 middleware that serves static assets for you

Example usage:
```php
$app->pipe('/fun-module/assets', new Reliv\ExpressiveServeStatic\ServeStaticMiddleware(
    __DIR__ . '/../vendor/fund-module/public',
    ['publicCachePath' => __DIR__ . '/../public/fun-module/assets']
));
```
