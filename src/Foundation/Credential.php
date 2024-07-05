<?php
declare(strict_types=1);

namespace StoragePhpClient\Foundation;

use InvalidArgumentException;

final class Credential
{
    private const ALGORITHM_MAP = [
        'SHA-256' => 'sha256',
    ];

    private readonly string $endpoint;

    private readonly string $clientId;

    private readonly string $clientSecret;

    public function __construct(
        private readonly string $dsn,
        private readonly string $nonce,
        private readonly string $realm = 'Secret Zone',
        private int $nonceCount = 0,
        private readonly ?string $cnonce = null
    ) {
        ['endpoint' => $this->endpoint, 'authority' => $authority] = self::parseDsn($this->dsn);

        if (!str_contains($authority, ':')) {
            throw new InvalidArgumentException('Invalid DSN format');
        }
        [$this->clientId, $this->clientSecret] = explode(':', $authority);
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return [
            'dsn' => $this->dsn,
            'endpoint' => $this->endpoint,
            'clientId' => $this->clientId,
            'clientSecret' => '(secret)',
            'nonce' => $this->nonce,
            'realm' => $this->realm,
            'nonceCount' => $this->nonceCount,
            'cnonce' => $this->cnonce,
        ];
    }

    public function authorizationBasic(): string
    {
        return 'Basic ' . base64_encode("$this->clientId:$this->clientSecret");
    }

    /**
     * @see https://tex2e.github.io/rfc-translater/html/rfc7616.html
     *
     * @param string $method
     * @param string $path
     *
     * @return string
     */
    public function authorizationDigest(string $method, string $path): string
    {
        $algorithm = 'SHA-256'; // hash は SHA-256 のみ対応
        $hashAlgo = self::ALGORITHM_MAP[$algorithm] ?? '';
        $this->nonceCount++;
        $nc = sprintf('%08x', $this->nonceCount);
        $qop = 'auth'; // qop は auth のみ対応
        $cnonce = $this->cnonce ?? md5(uniqid());

        $A1 = hash($hashAlgo, "$this->clientId:" . $this->realm . ":$this->clientSecret");
        $A2 = hash($hashAlgo, "$method:$path");
        $validResponse = hash($hashAlgo, "$A1:" . $this->nonce . ":$nc:$cnonce:$qop:$A2");

        return sprintf(
            'Digest username="%s", realm="%s", nonce="%s", uri="%s", algorithm="%s", qop=%s, cnonce="%s", response="%s"',
            $this->clientId,
            $this->realm,
            $this->nonce,
            $path,
            $algorithm,
            $qop,
            $cnonce,
            $validResponse
        );
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $dsn
     *
     * @return array{endpoint: string, authority: string}
     */
    private static function parseDsn(string $dsn): array
    {
        if (preg_match('#\A(?<scheme>https?://)((?<authority>\S[^@]+)@)?(?<host>\S+)\z#', $dsn, $matches) === false) {
            return ['endpoint' => '', 'authority' => '']; // @codeCoverageIgnore
        }

        return [
            'endpoint' => sprintf('%s%s', $matches['scheme'] ?? '', $matches['host'] ?? ''),
            'authority' => $matches['authority'] ?? '',
        ];
    }
}
