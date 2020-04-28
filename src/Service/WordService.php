<?php

namespace App\Service;

class WordService
{

    /**
     * @param string $message
     * @return array<string>
     */
    public function getProcessedMessage(string $message): array
    {
        $exploded = $this->split($message);
        $normalized = $this->normalizeWords($exploded);
        return array_unique($normalized);
    }

    /**
     * @param string $message
     * @return array<string>
     */
    public function split(string $message): array
    {
        return explode(" ", $message);
    }

    /**
     * @param array<string> $strings
     * @return array<string>
     */
    public function normalizeWords(array $strings): array
    {
        return array_map(
            fn ($string) => $this->normalizeWord($string),
            $strings
        );
    }

    public function normalizeWord(string $word): string
    {
        if ($word === "))") return $word;
        $word = mb_ereg_replace("[^A-Za-zА-Яа-я\-\'\)]", " ", $word);
        $word = mb_ereg_replace("\W{2,}", " ", (string) $word);
        $word = trim((string) $word);
        return mb_strtolower($word);
    }

    public function escapeSwearWord(string $swearWord): string
    {
        $wl = mb_strlen($swearWord);
        /** @var int */
        $replaceUpTo = round($wl / 2);
        $dotsAmount = mt_rand(1, $replaceUpTo);
        for ($i = 0; $i < $dotsAmount; $i++) {
            $pos = mt_rand(0, $wl - 1);
            /** @var array<string> */
            $wordEx = mb_str_split($swearWord);
            $wordEx[$pos] = "*";
            $swearWord = implode("", $wordEx);
        }
        return $swearWord;
    }

    public function checkIfWordIsOk(string $word): bool
    {
        if ($word === "))") return true;
        if (empty($word)) return false;
        if (mb_strlen($word) < 3) return false;
        return true;
    }
}
