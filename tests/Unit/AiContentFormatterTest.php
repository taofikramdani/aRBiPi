<?php

namespace Tests\Unit;

use App\Support\AiContentFormatter;
use PHPUnit\Framework\TestCase;

class AiContentFormatterTest extends TestCase
{
    public function test_it_formats_compact_ai_markdown_and_strips_html(): void
    {
        $html = AiContentFormatter::toHtml(
            'Pembuka. **Fokus Utama:** Pelajari konsep. * Latihan pertama. * Latihan kedua. <script>alert("x")</script>'
        );

        $this->assertStringContainsString('<h3>Fokus Utama:</h3>', $html);
        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('<li>Latihan pertama.</li>', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }
}
