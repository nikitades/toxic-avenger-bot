<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\Infrastructure\Yandex\Lemmatizer;

use Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer\LemmatizerProcess;
use Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer\YandexLemmatizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class YandexLemmatizerTest extends TestCase
{
    public function testLemmatizePhraseWithOnlyMeaningful(): void
    {
        $lemmatizer = new YandexLemmatizer(
            lemmatizerFactory: new LemmatizerProcess(
                serializer: new Serializer(
                    normalizers: [
                        new ObjectNormalizer(null, null, null, new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor()])),
                        new ArrayDenormalizer(),
                    ],
                    encoders: [new JsonDecode(), new JsonEncode()]
                )
            )
        );

        $result = $lemmatizer->lemmatizePhraseWithOnlyMeaningful('Во поле берёзка стояла, во поле кудрявая стояла, люли-люли стояла, люли-люли стояла');

        static::assertEquals(
            [
                'поле',
                'березка',
                'стоять',
                'поле',
                'кудрявый',
                'стоять',
                'люли',
                'люли',
                'стоять',
                'люли',
                'люли',
                'стоять',
            ],
            $result
        );
    }
}
