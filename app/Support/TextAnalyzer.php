<?php

namespace App\Support;

class TextAnalyzer
{
    private const STOPWORDS = [
        // Português
        'a', 'o', 'as', 'os', 'de', 'do', 'da', 'dos', 'das', 'e', 'em', 'no', 'na', 'nos', 'nas',
        'um', 'uma', 'uns', 'umas', 'para', 'por', 'com', 'sem', 'que', 'se', 'ao', 'aos', 'à', 'às',
        'como', 'mais', 'menos', 'muito', 'muita', 'muitos', 'muitas', 'ser', 'estar', 'ter', 'há',
        'sobre', 'entre', 'até', 'após', 'antes', 'durante', 'também', 'ou', 'já', 'foi', 'são', 'é',
        // Inglês
        'a', 'an', 'the', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'with', 'without', 'from', 'by',
        'is', 'are', 'was', 'were', 'be', 'been', 'being', 'it', 'its', 'that', 'this', 'these', 'those',
        'as', 'at', 'about', 'into', 'over', 'under', 'after', 'before', 'between', 'than', 'then',
        'very', 'more', 'most', 'less', 'least', 'can', 'could', 'should', 'would', 'will', 'just',
    ];

    public static function normalizeText(?string $text): string
    {
        $text = mb_strtolower((string) $text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    public static function tokenize(string $normalizedText): array
    {
        if ($normalizedText === '') {
            return [];
        }

        return preg_split('/\s+/u', $normalizedText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    public static function removeStopwords(array $tokens): array
    {
        $filtered = array_filter($tokens, function (string $token): bool {
            return mb_strlen($token, 'UTF-8') > 2
                && ! in_array($token, self::STOPWORDS, true);
        });

        return array_values(array_unique($filtered));
    }
}
