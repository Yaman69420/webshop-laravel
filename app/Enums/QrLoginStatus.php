<?php

namespace App\Enums;

enum QrLoginStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Consumed = 'consumed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'In afwachting',
            self::Approved => 'Goedgekeurd',
            self::Denied => 'Geweigerd',
            self::Consumed => 'Gebruikt',
            self::Expired => 'Verlopen',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Denied => 'red',
            self::Consumed => 'gray',
            self::Expired => 'amber',
        };
    }
}
