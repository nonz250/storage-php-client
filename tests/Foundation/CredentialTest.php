<?php
declare(strict_types=1);

namespace Foundation;

use StoragePhpClient\Foundation\Credential;
use PHPUnit\Framework\TestCase;

final class CredentialTest extends TestCase
{
    public static function credentialProvider(): array
    {
        return [
            'case: normal' => [
                'dsn' => 'https://clientId:clientSecret@example.com',
                'clientId' => 'clientId',
                'clientSecret' => 'clientSecret',
                'nonce' => 'nonce',
                'algorithm' => 'sha256', // 固定
                'realm' => 'Secret Zone',
                'nonceCount' => 0,
                'qop' => 'auth', // 固定
                'cnonce' => 'cnonce',
            ],
        ];
    }

    /**
     * @dataProvider credentialProvider
     * @param string $dsn
     * @param string $clientId
     * @param string $clientSecret
     * @param string $nonce
     * @param string $algorithm
     * @param string $realm
     * @param int $nonceCount
     * @param string $qop
     * @param string $cnonce
     * @return void
     */
    public function test__construct(
        string $dsn,
        string $clientId,
        string $clientSecret,
        string $nonce,
        string $algorithm,
        string $realm,
        int $nonceCount,
        string $qop,
        string $cnonce
    ): void {
        $credential = new Credential(
            $dsn,
            $nonce,
            $realm,
            $nonceCount,
            $cnonce,
        );
        $expectedBasic = 'Basic ' . base64_encode("$clientId:$clientSecret");
        $this->assertSame($expectedBasic, $credential->authorizationBasic());

        $expectedMethod = 'GET';
        $expectedPath = '/foo';
        $nc = sprintf('%08x', $nonceCount + 1);

        // @see https://tex2e.github.io/rfc-translater/html/rfc7616.html
        $A1 = hash($algorithm, "$clientId:$realm:$clientSecret");
        $A2 = hash($algorithm, "$expectedMethod:$expectedPath");
        $validResponse = hash($algorithm, "$A1:$nonce:$nc:$cnonce:$qop:$A2");
        $expectedDigest = sprintf(
            'Digest username="%s", realm="%s", nonce="%s", uri="%s", algorithm="%s", qop=%s, cnonce="%s", response="%s"',
            $clientId,
            $realm,
            $nonce,
            $expectedPath,
            'SHA-256',
            $qop,
            $cnonce,
            $validResponse,
        );
        $this->assertSame($expectedDigest, $credential->authorizationDigest($expectedMethod, $expectedPath));
    }
}
