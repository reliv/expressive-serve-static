<?php

namespace Reliv\ServeStaticTest;

require_once __DIR__ . '/../src/ContentTypes.php';
require_once __DIR__ . '/../src/ContentTypes.php';

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Reliv\ServeStatic\ServeStaticMiddleware;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class ServeStaticMiddlewareTest extends TestCase
{
    public function testRefusesToReturnFileThatIsOutsideTheAssetDirectoryForSecurity()
    {
        $unit = new ServeStaticMiddleware(__DIR__ . '/public-test');
        $request = new ServerRequest([], [], 'https://example.com/../secrets.php', 'GET');
        $responseFromDelegate = new Response();

        $mockDelagate = $this->getMockBuilder(DelegateInterface::class)->getMock();
        $mockDelagate->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn($responseFromDelegate);

        $responseFromUnit = $unit->process($request, $mockDelagate);

        $this->assertTrue($responseFromDelegate === $responseFromUnit);
    }

    public function testReturnsFileWhenFileExistsAndIsValid()
    {
        $unit = new ServeStaticMiddleware(__DIR__ . '/public-test');
        $request = new ServerRequest([], [], 'https://example.com/test.json', 'GET');
        $responseFromDelegate = new Response();

        $mockDelagate = $this->getMockBuilder(DelegateInterface::class)->getMock();
        $mockDelagate->expects($this->once())
            ->method('process')
            ->with($request)
            ->willReturn($responseFromDelegate);

        $responseFromUnit = $unit->process($request, $mockDelagate);

        $expectedFileContents = file_get_contents(__DIR__ . '/public-test/test.json');

        var_dump($request->getBody()->getContents());

        $this->assertEquals(
            $expectedFileContents,
            $request->getBody()->getContents()
        );

    }
}
