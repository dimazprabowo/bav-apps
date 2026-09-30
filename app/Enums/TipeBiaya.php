<?php

namespace App\Enums;

enum TipeBiaya: string
{
    case FixCost = 'Fix Cost';
    case RabProject = 'RAB Project';

    public function label(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
