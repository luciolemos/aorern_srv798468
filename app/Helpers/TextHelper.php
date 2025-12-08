<?php

namespace App\Helpers;

class TextHelper
{
    public static function excerpt(?string $content, int $limit = 160): string
    {
        if (!$content) {
            return '';
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', strip_tags($content)));
        if ($normalized === '') {
            return '';
        }

        if (mb_strlen($normalized) <= $limit) {
            return $normalized;
        }

        $truncated = mb_substr($normalized, 0, $limit);
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > ($limit * 0.6)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, " ,.;:-") . '...';
    }
}
