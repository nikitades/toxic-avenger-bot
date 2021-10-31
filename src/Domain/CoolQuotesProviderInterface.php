<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

interface CoolQuotesProviderInterface
{
    public function provide(): CoolQuote;
}