<?php

namespace App\Helpers;

class TextHelper
{
    public static function excerpt(?string $content, int $limit = 160): string
    {
        if (!$content) {
            return '';
        }

        // Adiciona espaçamento entre blocos antes de remover tags para evitar junção de frases
        $spacedContent = preg_replace('/<\/(p|h[1-6]|div|section|article|li)>/i', '</$1> ', $content);
        $spacedContent = preg_replace('/<(br|hr)\s*\/?\s*>/i', ' ', $spacedContent);

        $normalized = trim(preg_replace('/\s+/u', ' ', strip_tags($spacedContent)));
        if ($normalized === '') {
            return '';
        }

        $normalized = html_entity_decode($normalized, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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

    public static function stripLeadingHeading(?string $content): string
    {
        if (!$content) {
            return '';
        }

        $pattern = '/<h[1-6][^>]*>.*?<\/h[1-6]>\s*/is';
        $cleaned = preg_replace($pattern, '', $content, 1);

        return ltrim($cleaned ?? $content);
    }

    public static function firstParagraphExcerpt(?string $content, int $limit = 160): string
    {
        if (!$content) {
            return '';
        }

        if (preg_match('/<p[^>]*>.*?<\/p>/is', $content, $matches)) {
            $paragraph = $matches[0];
        } else {
            $paragraph = self::stripLeadingHeading($content);
        }

        return self::excerpt($paragraph, $limit);
    }
}
