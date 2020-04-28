<?php

namespace App\Service;

class AllowedSpecialBadWordsService
{

    /**
     * @return array<string>
     */
    public function getInternational(): array
    {
        return [
            ')',
            '))'
        ];
    }

    /**
     * @return array<string>
     */
    public function getRu(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getEn(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getAll(): array
    {
        return [
            ...$this->getInternational(),
            ...$this->getRu(),
            ...$this->getEn()
        ];
    }
}
