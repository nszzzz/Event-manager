<?php

namespace App\Services;

use App\Models\Faq_entries;
use Illuminate\Support\Str;

class MessageAssistantService
{
    public function generateReply(string $incomingContent): string
    {
        return $this->generateReplyResult($incomingContent)['content'];
    }

    /**
     * @return array{content: string, matched: bool}
     */
    public function generateReplyResult(string $incomingContent): array
    {
        $message = Str::lower(trim($incomingContent));

        if ($message === '') {
            return [
                'content' => $this->fallbackReply(),
                'matched' => false,
            ];
        }

        $entries = Faq_entries::query()
            ->where('is_active', true)
            ->get(['answer', 'keywords']);

        $bestScore = 0;
        $bestAnswer = null;

        foreach ($entries as $entry) {
            $keywords = $this->normalizeKeywords($entry->keywords);
            if ($keywords === []) {
                continue;
            }

            $score = $this->scoreMessageAgainstKeywords($message, $keywords);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestAnswer = $entry->answer;
            }
        }

        if ($bestAnswer !== null) {
            return [
                'content' => $bestAnswer,
                'matched' => true,
            ];
        }

        return [
            'content' => $this->fallbackReply(),
            'matched' => false,
        ];
    }

    private function scoreMessageAgainstKeywords(string $message, array $keywords): int
    {
        $score = 0;

        foreach ($keywords as $keyword) {
            $normalizedKeyword = Str::lower(trim((string) $keyword));
            if ($normalizedKeyword === '') {
                continue;
            }

            if (Str::contains($message, $normalizedKeyword)) {
                $score += mb_strlen($normalizedKeyword) + 5;
                continue;
            }

            $keywordTokens = $this->tokenize($normalizedKeyword);
            if ($keywordTokens === []) {
                continue;
            }

            $allTokensPresent = true;
            foreach ($keywordTokens as $token) {
                if (!Str::contains($message, $token)) {
                    $allTokensPresent = false;
                    break;
                }
            }

            if ($allTokensPresent) {
                $score += count($keywordTokens);
            }
        }

        return $score;
    }

    private function normalizeKeywords(mixed $rawKeywords): array
    {
        if (is_array($rawKeywords)) {
            return $rawKeywords;
        }

        if (is_string($rawKeywords)) {
            $decoded = json_decode($rawKeywords, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return array_filter(array_map('trim', explode(',', $rawKeywords)));
        }

        return [];
    }

    private function tokenize(string $value): array
    {
        $cleaned = preg_replace('/[^\pL\pN\s]/u', ' ', $value) ?? '';
        $tokens = preg_split('/\s+/u', trim($cleaned)) ?: [];
        return array_values(array_filter($tokens));
    }

    private function fallbackReply(): string
    {
        return 'I could not find a direct FAQ match. Please rephrase your question, or request a human agent.';
    }
}
