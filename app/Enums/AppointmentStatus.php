<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus: string implements HasLabel, HasColor
{
    case Created = 'created';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Canceled = 'canceled';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Created => 'warning',
            self::Confirmed => 'success',
            self::Rejected => 'danger',
            self::Canceled => 'danger',
        };
    }
}
