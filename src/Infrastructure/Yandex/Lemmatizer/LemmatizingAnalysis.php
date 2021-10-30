<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer;

use LogicException;

class LemmatizingAnalysis
{
    private const A = 'A';
    private const ADV = 'ADV';
    private const ADVPRO = 'ADVPRO';
    private const ANUM = 'ANUM';
    private const APRO = 'APRO';
    private const COM = 'COM';
    private const CONJ = 'CONJ';
    private const INTJ = 'INTJ';
    private const NUM = 'NUM';
    private const PART = 'PART';
    private const PR = 'PR';
    private const S = 'S';
    private const SPRO = 'SPRO';
    private const V = 'V';

    private const OBSCENE = 'обсц';

    public function __construct(
        private string $lex,
        private float $wt,
        private string $gr,
    ) {
    }

    public function getLex(): string
    {
        return $this->lex;
    }

    public function getWeight(): float
    {
        return $this->wt;
    }

    /**
     * @return array<string>
     */
    public function getGrammarInfo(): array
    {
        return explode(',', $this->gr);
    }

    /**
     * A	    прилагательное
     * ADV	    наречие
     * ADVPRO   местоименное наречие
     * ANUM	    числительное-прилагательное
     * APRO	    местоимение-прилагательное
     * COM	    часть композита - сложного слова
     * CONJ	    союз
     * INTJ	    междометие
     * NUM	    числительное
     * PART	    частица
     * PR	    предлог
     * S	    существительное
     * SPRO     местоимение-существительное
     * V        глагол.
     *
     * A,обсц=им,ед,полн,муж
     *
     * @return string
     */
    public function getKind(): string
    {
        $grammarInfo = $this->getGrammarInfo();

        if ([] === $grammarInfo) {
            throw new LogicException('No grammar info found!');
        }

        return explode('=', $grammarInfo[0])[0];
    }

    public function isObscene(): bool
    {
        return str_contains($this->gr, self::OBSCENE);
    }

    public static function checkIfMeaningful(self $that): bool
    {
        return !in_array($that->getKind(), [self::CONJ, self::PART, self::PR], true);
    }
}
