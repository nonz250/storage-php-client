<?php
declare(strict_types=1);

namespace Client;

use PHPUnit\Framework\TestCase;
use StoragePhpClient\Client\Client;
use StoragePhpClient\Client\ClientInterface;

final class ClientTest extends TestCase
{
    public function test__construct(): ClientInterface
    {
        $client = new Client();
        $this->assertInstanceOf(ClientInterface::class, $client);
        return $client;
    }
}
