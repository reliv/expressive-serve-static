<?php

namespace Reliv\ServeStatic;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

class ServeStaticMiddleware implements MiddlewareInterface
{
    protected $fileSystemAssetDirectory;
    protected $options;

    public function __construct($fileSystemAssetDirectory, $options = [])
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

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $uriSubPath = $request->getUri()->getPath();

        // Ensure we have been given a file path to look for
        if (empty($uriSubPath)) {
            return $delegate->process($request);
        }

        $filePath = realpath($this->fileSystemAssetDirectory . $uriSubPath);

        // Ensure someone isn't using dots to go backward past the asset root folder
        if (!strpos($filePath, realpath($this->fileSystemAssetDirectory)) === 0) {
            return $delegate->process($request);
        }

        // Ensure the file exists and is not a directory
        if (!is_file($filePath)) {
            return $delegate->process($request);
        }

        $response = new Response();

        $body = $response->getBody();

        $content = file_get_contents($filePath);

        if ($this->options['publicCachePath'] !== null) {
            $writePath = $this->options['publicCachePath'] . $uriSubPath;
            $writeDir = dirname($writePath);
            if (!is_dir($writeDir)) {
                mkdir($writeDir, 0777, true);
            }
            file_put_contents($writePath, $content);
        }

        $body->write($content);

        $extentsion = pathinfo($filePath)['extension'];
        if (array_key_exists($extentsion, $this->options['contentTypes'])) {
            $response = $response->withHeader('content-type', $this->options['contentTypes'][$extentsion]);
        }

        foreach ($this->options['headers'] as $key => $value) {
            $response = $response->withHeader($key, $value);
        }

        return $response->withBody($body);
    }
}
