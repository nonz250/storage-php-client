<?php
declare(strict_types=1);

namespace Foundation;

use Fig\Http\Message\RequestMethodInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use JsonSerializable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use StoragePhpClient\Foundation\ApiParam;
use StoragePhpClient\Foundation\Credential;
use StoragePhpClient\Foundation\RequestFactory;

/**
 * @see RequestFactory
 *
 * @coversDefaultClass \StoragePhpClient\Foundation\RequestFactory
 */
final class RequestFactoryTest extends TestCase
{
    private const PATH = '/foo';

    private static Credential $credential;

    public static function setUpBeforeClass(): void
    {
        self::$credential = new Credential('https://foo:bar@example.com', 'nonce');
    }

    public function test__construct(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );
        $this->assertInstanceOf(RequestFactory::class, $requestFactory);
    }

    /**
     * @return void
     *
     * @covers ::buildUriString
     */
    public function testBuildUrlString(): void
    {
        $this->assertSame(
            'https://example.com/api/v1/test',
            RequestFactory::buildUriString('https://example.com', 'api/v1/test', [])
        );
        $this->assertSame(
            'https://example.com/api/v1/test',
            RequestFactory::buildUriString('https://example.com', '/api/v1/test', [])
        );
        $this->assertSame(
            'https://example.com/api/v1/test?foo=bar',
            RequestFactory::buildUriString('https://example.com', 'api/v1/test', ['foo' => 'bar'])
        );
        $this->assertSame(
            'https://example.com/api/v1/test?foo=bar&baz=qux',
            RequestFactory::buildUriString('https://example.com', 'api/v1/test', ['foo' => 'bar', 'baz' => 'qux'])
        );
    }

    /**
     * GETリクエストの作成を検証する.
     */
    public function testCreateGetRequest(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_GET, self::PATH);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_GET, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * リクエストヘッダーを含むGETリクエストの作成を検証する.
     */
    public function testCreateGetRequestWithHeader(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $header = ['key' => 'value'];
        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_GET, self::PATH);
        $apiParam->method('getHeaders')->willReturn($header);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_GET, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
        $this->assertSame($header['key'], $request->getHeaderLine('key'));
    }

    /**
     * クエリパラメーターを含むGETリクエストの作成を検証する.
     */
    public function testCreateGetRequestWithQuery(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $query = ['key' => 'value'];
        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_GET, self::PATH);
        $apiParam->method('getQueryParams')->willReturn($query);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_GET, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, $query),
            (string)$request->getUri()
        );
        $this->assertSame(RequestFactory::buildQueryString($query), $request->getUri()->getQuery());
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * POSTリクエストの作成を検証する.
     */
    public function testCreatePostRequest(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_POST, self::PATH);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_POST, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * リクエストボディを含むPOSTリクエストの作成を検証する.
     */
    public function testCreatePostRequestWithBody(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_POST, self::PATH);
        $apiParam->method('getBody')->willReturn(
            new class() implements JsonSerializable {
                /**
                 * @return array<string, string>
                 */
                public function jsonSerialize(): array
                {
                    return ['key' => 'value'];
                }
            }
        );

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_POST, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertJson($request->getBody()->getContents());
    }

    /**
     * PUTリクエストの作成を検証する.
     */
    public function testCreatePutRequest(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_PUT, self::PATH);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_PUT, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * PATCHリクエストの作成を検証する.
     */
    public function testCreatePatchRequest(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_PATCH, self::PATH);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_PATCH, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * DELETEリクエストの作成を検証する.
     */
    public function testCreateDeleteRequest(): void
    {
        $requestFactory = new RequestFactory(
            Psr17FactoryDiscovery::findRequestFactory(),
            $this->createStub(StreamFactoryInterface::class)
        );

        $apiParam = $this->createApiParamMock(RequestMethodInterface::METHOD_DELETE, self::PATH);

        $request = $requestFactory->create(self::$credential, $apiParam);

        $this->assertSame(RequestMethodInterface::METHOD_DELETE, $request->getMethod());
        $this->assertStringStartsWith(
            RequestFactory::buildUriString(self::$credential->getEndpoint(), self::PATH, []),
            (string)$request->getUri()
        );
        $this->assertSame('', $request->getHeaderLine('Content-Type'));
    }

    /**
     * {@link ApiParam のモックを作成する}.
     *
     * @param string $method
     * @param string $path
     *
     * @return ApiParam&MockObject
     */
    private function createApiParamMock(string $method, string $path): ApiParam&MockObject
    {
        $apiParam = $this->createMock(ApiParam::class);
        $apiParam->method('getMethod')->willReturn($method);
        $apiParam->method('getPath')->willReturn($path);

        return $apiParam;
    }
}
