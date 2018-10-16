<?php

declare(strict_types=1);

namespace Reliv\ServeStatic;

use Psr\Container\ContainerInterface;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\MiddlewarePipeInterface;
use function Zend\Stratigility\path;

class ServeStaticMiddlewarePipeFactory
{
    /**
     * Load config and instantiate middleware
     *
     * Example config:
     * 'serve_static' => [
     *      '/fun-module/assets' => [
     *          'fileSystemAssetDirectory' => __DIR__ . '/../vendor/fund-module/public',
     *          'publicCachePath' => __DIR__ . '/../public/fun-module/assets',
     *          'headers' => [],
     *      ]
     *  ]
     *
     * @param ContainerInterface $container
     * @return MiddlewarePipeInterface
     */
    public function __invoke(ContainerInterface $container): MiddlewarePipeInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['serve_static']) ? $config['serve_static'] : [];

        $middleware = new MiddlewarePipe();
        foreach ($config as $uriPath => $options) {
            if (!array_key_exists('fileSystemAssetDirectory', $options)) {
                throw new \InvalidArgumentException('key "fileSystemAssetDirectory" missing in config');
            }

            $fileSystemAssetDirectory = $options['fileSystemAssetDirectory'];
            unset($options['fileSystemAssetDirectory']);

            $middleware->pipe(path($uriPath, new ServeStaticMiddleware(
                $fileSystemAssetDirectory,
                $options
            )));
        }

        return $middleware;
    }
}
