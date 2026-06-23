<?php
declare(strict_types=1);

namespace StoragePhpClient\Foundation;

use JsonException;
use JsonSerializable;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

final class RequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param string $endpoint
     * @param string $path
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    public static function buildUriString(string $endpoint, string $path, array $queryParams): string
    {
        if ($path !== '' && mb_strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        $uri = $endpoint . $path;

        $queryString = self::buildQueryString($queryParams);

        if ($queryString !== '') {
            $uri .= '?' . $queryString;
        }

        return $uri;
    }

    /**
     * @param array $queryParams
     *
     * @return string
     */
    public static function buildQueryString(array $queryParams): string
    {
        if ($queryParams === []) {
            return '';
        }

        return http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param Credential $credential
     * @param ApiParam $apiParam
     *
     * @return RequestInterface
     */
    public function create(Credential $credential, ApiParam $apiParam): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
            $apiParam->getMethod(),
            self::buildUriString($credential->getEndpoint(), $apiParam->getPath(), $apiParam->getQueryParams()),
        );

        foreach ($apiParam->getHeaders() as $key => $value) {
            $request = $request->withHeader($key, $value);
        }

        $request = $request->withHeader('Authorization', $credential->authorizationDigest(
            $apiParam->getMethod(),
            $apiParam->getPath(),
        ));

        $body = $apiParam->getBody();

        if ($body instanceof JsonSerializable) {
            // 現状は application/json のみ
            try {
                $json = json_encode(
                    $body,
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_PRESERVE_ZERO_FRACTION
                    | JSON_PARTIAL_OUTPUT_ON_ERROR
                    | JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) { // @codeCoverageIgnore
                throw new RuntimeException('Failed to encode json.', $e->getCode(), $e); // @codeCoverageIgnore
            }

            if ($json === 'null') {
                // json 文字列化して "null" になるということは、リクエストボディなしを指す
                return $request; // @codeCoverageIgnore
            }

            $stream = $this->streamFactory->createStream($json);
            unset($json);

            $request = $request
                ->withHeader('Content-Type', ContentType::JSON->value)
                ->withBody($stream);
        }

        return $request;
    }
}
