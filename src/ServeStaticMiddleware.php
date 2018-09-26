<?php

declare(strict_types=1);

namespace Reliv\ServeStatic;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class ServeStaticMiddleware implements MiddlewareInterface
{
    /** @var string */
    protected $fileSystemAssetDirectory;

    /** @var array */
    protected $options;

    /**
     * ServeStaticMiddleware constructor.
     * @param $fileSystemAssetDirectory
     * @param array $options
     */
    public function __construct(string $fileSystemAssetDirectory, array $options = [])
    {
        $this->options = array_merge(
            [
                'publicCachePath' => null,
                'headers' => [
                    //headerKey => headerValue
                ]
            ],
            $options
        );

        if (!array_key_exists('contentTypes', $this->options)) {
            $this->options['contentTypes'] = ContentTypes::DEFAULT_CONTENT_TYPES;
        }

        $this->fileSystemAssetDirectory = $fileSystemAssetDirectory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $uriSubPath = $request->getUri()->getPath();

        // Ensure we have been given a file path to look for
        if (empty($uriSubPath)) {
            return $handler->handle($request);
        }

        // Build filePath
        $filePath = realpath($this->fileSystemAssetDirectory . $uriSubPath);

        // Check for invalid path
        if ($filePath == false) {
            return $handler->handle($request);
        }

        // Ensure someone isn't using dots to go backward past the asset root folder
        if (!strpos($filePath, realpath($this->fileSystemAssetDirectory)) === 0) {
            return $handler->handle($request);
        }

        // Write to publicCachePath if configured
        if ($this->options['publicCachePath'] !== null) {
            $writePath = $this->options['publicCachePath'] . $uriSubPath;
            $writeDir = dirname($writePath);
            if (!is_dir($writeDir)) {
                mkdir($writeDir, 0777, true);
            }
            copy($filePath, $writePath);
        }

        // Ensure the file exists and is not a directory
        if (!is_file($filePath)) {
            return $handler->handle($request);
        }

        // Build response as stream
        $body = new Stream($filePath);
        $response = new Response();
        $response = $response->withBody($body);

        // Add content type if known
        $extension = pathinfo($filePath)['extension'];
        if (array_key_exists($extension, $this->options['contentTypes'])) {
            $response = $response->withHeader('content-type', $this->options['contentTypes'][$extension]);
        }

        // Add additional configured headers
        foreach ($this->options['headers'] as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}
