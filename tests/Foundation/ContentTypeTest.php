<?php
declare(strict_types=1);

namespace Foundation;

use PHPUnit\Framework\TestCase;
use StoragePhpClient\Foundation\ContentType;

final class ContentTypeTest extends TestCase
{
    public function testContentType(): void
    {
        $this->assertTrue(ContentType::FORM_URLENCODED->equals(ContentType::FORM_URLENCODED));
        $this->assertFalse(ContentType::FORM_URLENCODED->equals(ContentType::JSON));
        $this->assertFalse(ContentType::JSON->equals(ContentType::FORM_URLENCODED));
    }
}
