<?php

namespace App\Services;

use App\Models\Book;
use App\Support\TextAnalyzer;
use Illuminate\Support\Collection;

class RelatedBooksService
{
    public function findRelatedBooks(Book $book, int $limit = 4): Collection
    {
        $sourceTokens = $this->extractDescriptionTokens($book);
        if (empty($sourceTokens)) {
            return collect();
        }

        $candidates = Book::query()
            ->with(['authors', 'publisher'])
            ->whereKeyNot($book->id)
            ->whereNotNull('bibliography')
            ->get();

        $scored = $candidates->map(function (Book $candidate) use ($sourceTokens): ?Book {
            $candidateTokens = $this->extractDescriptionTokens($candidate);
            if (empty($candidateTokens)) {
                return null;
            }

            $score = $this->calculateSimilarityScore($sourceTokens, $candidateTokens);
            if ($score <= 0) {
                return null;
            }

            $candidate->setAttribute('related_score', $score);

            return $candidate;
        })->filter();

        return $scored
            ->sortByDesc(fn (Book $candidate) => (float) $candidate->getAttribute('related_score'))
            ->take($limit)
            ->values();
    }

    private function extractDescriptionTokens(Book $book): array
    {
        // Mantém compatibilidade com o schema atual: usa bibliography como descrição textual.
        $description = $book->bibliography;
        $normalized = TextAnalyzer::normalizeText($description);
        $tokens = TextAnalyzer::tokenize($normalized);

        return TextAnalyzer::removeStopwords($tokens);
    }

    private function calculateSimilarityScore(array $bookATerms, array $bookBTerms): float
    {
        $sharedTerms = count(array_intersect($bookATerms, $bookBTerms));
        if ($sharedTerms <= 0) {
            return 0.0;
        }

        $denominator = sqrt(count($bookATerms) * count($bookBTerms));
        if ($denominator <= 0.0) {
            return 0.0;
        }

        return $sharedTerms / $denominator;
    }
}
