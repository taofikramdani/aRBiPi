<?php

namespace App\Support;

class MaterialFileUrl
{
    public static function make(string $disk, string $path, ?string $storedUrl = null): ?string
    {
        if ($disk !== 'azure') {
            return $storedUrl;
        }

        $baseUrl = rtrim((string) config('filesystems.disks.azure.url'), '/');

        if ($baseUrl === '') {
            return $storedUrl;
        }

        $segments = [
            trim((string) config('filesystems.disks.azure.container'), '/'),
            trim((string) config('filesystems.disks.azure.prefix'), '/'),
            ltrim($path, '/'),
        ];

        foreach (array_filter($segments) as $segment) {
            if (! str_ends_with($baseUrl, '/'.$segment)) {
                $baseUrl .= '/'.$segment;
            }
        }

        return $baseUrl;
    }
}
