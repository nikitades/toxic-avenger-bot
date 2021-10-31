<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Infrastructure\Quotable;

use GuzzleHttp\ClientInterface;
use Nikitades\ToxicAvenger\Domain\CoolQuote;
use Nikitades\ToxicAvenger\Domain\CoolQuotesProviderInterface;
use Throwable;

class HttpCoolQuoteProvider implements CoolQuotesProviderInterface
{
    public function __construct(
        private ClientInterface $client,
    ) {
    }

    public function provide(): CoolQuote
    {
        try {
            $response = $this->client->request(
                method: 'GET',
                uri: 'http://api.quotable.io/random',
                options: [
                    'connect_timeout' => 0.5,
                ],
            );

            $dataEncoded = $response->getBody()->getContents();

            $data = json_decode($dataEncoded, true);

            return new CoolQuote(
                author: $data['author'],
                quote: $data['content'],
                tags: array_map(fn (string $tag): string => str_replace('-', '', $tag), $data['tags']),
            );
        } catch (Throwable $e) {
            return new CoolQuote(
                author: 'Frank Herbert',
                quote: 'Страх убивает разум. Страх - это малая смерть, несущая забвение. Я смотрю в лицо моему страху, я дам ему овладеть мною и пройти сквозь меня. И когда он пройдет сквозь меня, я обернусь и посмотрю на тропу страха. Там, где прошел страх, не останется ничего. Там, где прошел страх, останусь только я.',
                tags: [
                    'serviceisdown',
                    'spicemustflow',
                    'dune',
                ],
            );
        }
    }
}
