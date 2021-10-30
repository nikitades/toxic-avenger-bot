<?php

declare(strict_types=1);

namespace Nikitades\ToxicAvenger\Domain;

class ToxicityMeasurer
{
    public function measureToxicityLevel(int $usages): ToxicityMeasure | null
    {
        foreach ([
            350 => '🔥🔥🔥 TOXIC GOD 🔥🔥🔥',
            300 => '⚔️⚔️ TOXIC AVENGER ⚔️⚔️',
            250 => '💂💂 TOXIC SOLDIER 💂💂',
            200 => '👹👹 TOXIC PREDATOR 👹👹',
            150 => '🦠 TOXIC VIRUS 🦠',
            100 => '🗑️ REAL TRASH 🗑️',
            75 => '🏄‍♂️ MENTAL SICKNESS 🏄‍♂️',
            50 => '👺 TOURETTE SYNDROME 👺',
            25 => '🤯 HARD NEUROSIS 🤯',
            10 => '🤬 DIFFICULT DAY 🤬',
            5 => '😬 DIRTY BOY 😬',
        ] as $degree => $title) {
            if ($usages >= $degree) {
                return new ToxicityMeasure(
                    usagesCount: $usages,
                    title: $title,
                );
            }
        }

        return null;
    }
}
