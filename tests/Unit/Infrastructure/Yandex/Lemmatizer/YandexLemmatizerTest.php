<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Tests\Unit\Infrastructure\Yandex\Lemmatizer;

use Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer\LemmatizerSystemAwareFactory;
use Nikitades\ToxicAvenger\Infrastructure\Yandex\Lemmatizer\YandexLemmatizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
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
        // a full list of extractors is shown further below
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        // list of PropertyListExtractorInterface (any iterable)
        $listExtractors = [$reflectionExtractor];

        // list of PropertyTypeExtractorInterface (any iterable)
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];

        // list of PropertyDescriptionExtractorInterface (any iterable)
        $descriptionExtractors = [$phpDocExtractor];

        // list of PropertyAccessExtractorInterface (any iterable)
        $accessExtractors = [$reflectionExtractor];

        // list of PropertyInitializableExtractorInterface (any iterable)
        $propertyInitializableExtractors = [$reflectionExtractor];

        $propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors,
            $propertyInitializableExtractors
        );

        // //TODO: понять как сделать десериализацию вложенных объектов
        // $lemmatizer = new YandexLemmatizer(
        //     lemmatizerFactory: new LemmatizerSystemAwareFactory(
        //         serializer: new Serializer(
        //             normalizers: [
        //                 new ObjectNormalizer(null, null, null, $propertyInfo),
        //                 new ArrayDenormalizer(),
        //             ],
        //             encoders: [new JsonDecode(), new JsonEncode()]
        //         )
        //     )
        // );

        // $result = $lemmatizer->lemmatizePhraseWithOnlyMeaningful('Во поле берёзка стояла, во поле кудрявая стояла, люли-люли стояла, люли-люли стояла');

        static::assertEquals([], $result);
    }
}
