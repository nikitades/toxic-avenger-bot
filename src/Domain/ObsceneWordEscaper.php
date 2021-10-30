<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

class ObsceneWordEscaper
{
    public function escape(string $word): string
    {
        if (strlen($word) < 3) {
            return $word;
        }

        $word = mb_str_split($word);
        $randChar = round(rand(1, count($word) - 2));
        $word[$randChar] = '?';

        return implode('', $word);
    }
}
