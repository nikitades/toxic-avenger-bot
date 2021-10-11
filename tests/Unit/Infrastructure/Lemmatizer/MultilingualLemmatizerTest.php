<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\Infrastructure;

use ArrayIterator;
use LanguageDetection\Language;
use LanguageDetection\LanguageResult;
use Nikitades\ToxicAvenger\Infrastructure\Lemmatizer\LanguageBoundLemmatizerInterface;
use Nikitades\ToxicAvenger\Infrastructure\Lemmatizer\MultilingualLemmatizer;
use PHPUnit\Framework\TestCase;

class MultilingualLemmatizerTest extends TestCase
{
    /**
     * @dataProvider getTestCases
     * @param array<string> $expectedData
     */
    public function testLemmatizePhraseWithOnlyMeaningful(string $phraseLang, string $lemmatizerLang, array $expectedData): void
    {
        $lemmatizers = new ArrayIterator([
            new class($lemmatizerLang) implements LanguageBoundLemmatizerInterface {
                public function __construct(private string $lemmatizerLang)
                {
                }

                public function getLanguage(): string
                {
                    return $this->lemmatizerLang;
                }

                public function lemmatizePhraseWithOnlyMeaningful(string $phrase): array
                {
                    return ['a', 'b', 'c'];
                }
            },
        ]);

        $languageDetectionResult = new LanguageResult([
            $phraseLang => 1,
        ]);

        $languageDetector = $this->createMock(Language::class);
        $languageDetector->expects(static::once())->method('detect')->willReturn($languageDetectionResult);

        $multilingualLemmatizer = new MultilingualLemmatizer(
            lemmatizers: $lemmatizers,
            languageDetector: $languageDetector
        );

        $result = $multilingualLemmatizer->lemmatizePhraseWithOnlyMeaningful('aaa bbb ccc');

        static::assertEquals(
            $expectedData,
            $result
        );
    }

    /**
     * @return iterable<string,array<mixed>>
     */
    public function getTestCases(): iterable
    {
        yield 'ru lang and ru lemmatizer' => [
            'lang' => 'ru',
            'lemmatizerLang' => 'ru',
            'expected' => ['a', 'b', 'c'],
        ];

        yield 'de lang and ru lemmatizer' => [
            'lang' => 'ru',
            'lemmatizerLang' => 'ru',
            'expected' => ['a', 'b', 'c'],
        ];
    }
}
