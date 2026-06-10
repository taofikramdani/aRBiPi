<?php

namespace App\Support;

use Illuminate\Support\Str;

class AiContentFormatter
{
    public static function toHtml(?string $content): string
    {
        if (blank($content)) {
            return '';
        }

        $content = str_replace(["\r\n", "\r"], "\n", trim($content));

        // AI responses occasionally return valid Markdown without useful line breaks.
        $content = preg_replace('/\s*\*\*([^*\n]{2,100}:)\*\*\s*/u', "\n\n### $1\n\n", $content);
        $content = preg_replace('/\s*---\s*/u', "\n\n---\n\n", $content);
        $content = preg_replace('/[ \t]+\*[ \t]+(?=\S)/u', "\n- ", $content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
