<?php

namespace Reliv\ExpressiveServeStatic;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

class ServeStaticMiddleware implements MiddlewareInterface
{
    protected $fileSystemPath;
    protected $options;

    public function __construct($fileSystemPath, $options)
    {
        $this->options = array_merge(
            [
                'extensionToHeaders' => [
                    '_default' => [
                        'content-type' => 'text/plain'
                    ],
                    'css' => [
                        'content-type' => 'text/css'
                    ],
                    'html' => [
                        'content-type' => 'text/html'
                    ],
                    'js' => [
                        'content-type' => 'application/javascript'
                    ],
                ],
                'notFoundResponseBody' => '404 - Not Found'
            ],
            $options
        );
        $this->fileSystemPath = $fileSystemPath;
    }

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $fileName = $request->getUri()->getPath();

        if (empty($fileName)) {
            return $this->options['notFoundResponse'];
        }

        $filePath = $this->fileSystemPath . '/' . $fileName;

        $filePath = realpath($filePath);

        // make sure file is real and is secure
        if (!strpos($filePath, realpath($this->fileSystemPath)) === 0 || !is_file($filePath)) {
            return new HtmlResponse($this->options['notFoundResponseBody'], 404);
        }

        $response = new Response();

        $body = $response->getBody();

        $body->write(file_get_contents($filePath));

        $headersToAdd = $this->options['extensionToHeaders'][pathinfo($fileName)['extension']];

        foreach ($headersToAdd as $headerKey => $value) {
            $response = $response->withHeader($headerKey, $value);
        }

        return $response->withBody($body);
    }
}
