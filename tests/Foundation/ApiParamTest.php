<?php
declare(strict_types=1);

namespace Foundation;

use StoragePhpClient\Foundation\ApiParam;
use PHPUnit\Framework\TestCase;

final class ApiParamTest extends TestCase
{
    public function test__construct()
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
    }
}
