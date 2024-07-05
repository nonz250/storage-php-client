<?php
declare(strict_types=1);

namespace StoragePhpClient\Foundation;

use JsonSerializable;

/**
 * @internal
 */
abstract class ApiParam
{
    /**
     * HTTPリクエストメソッド
     *
     * {@link RequestMethodInterface} で定義されている定数の中から返す。
     */
    abstract public function getMethod(): string;

    /**
     * リクエストパス
     *
     * APIエンドポイントのパス部分の文字列を返す。
     */
    abstract public function getPath(): string;

    /**
     * リクエストヘッダー配列
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * リクエストボディ
     */
    public function getBody(): ?JsonSerializable
    {
        if ($this instanceof JsonSerializable) {
            return $this;
        }

        return null;
    }

    /**
     * クエリパラメーター配列
     * @return array<string, string>
     */
    public function getQueryParams(): array
    {
        return [];
    }
}
