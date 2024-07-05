<?php
declare(strict_types=1);

namespace Foundation;

use StoragePhpClient\Foundation\ApiParam;
use PHPUnit\Framework\TestCase;

final class ApiParamTest extends TestCase
{
    public function testNoBody()
    {
        $apiParam = new class extends ApiParam {
            public function getMethod(): string
            {
                return 'GET';
            }

            public function getPath(): string
            {
                return '/path';
            }
        };
        $this->assertInstanceOf(ApiParam::class, $apiParam);
        $this->assertSame('GET', $apiParam->getMethod());
        $this->assertSame('/path', $apiParam->getPath());
        $this->assertSame([], $apiParam->getHeaders());
        $this->assertSame([], $apiParam->getQueryParams());
        $this->assertNull($apiParam->getBody());
    }

    public function testJsonBody()
    {
        $jsonApiParam = new class() extends ApiParam implements \JsonSerializable {
            public function getMethod(): string
            {
                return 'POST';
            }

            public function getPath(): string
            {
                return '/post';
            }

            /**
             * @return string[]
             */
            public function jsonSerialize(): array
            {
                return ['key' => 'value'];
            }
        };
        $this->assertInstanceOf(ApiParam::class, $jsonApiParam);
        $this->assertSame('POST', $jsonApiParam->getMethod());
        $this->assertSame('/post', $jsonApiParam->getPath());
        $this->assertSame([], $jsonApiParam->getHeaders());
        $this->assertSame([], $jsonApiParam->getQueryParams());
        $this->assertJson(json_encode($jsonApiParam->getBody()));
    }
}
