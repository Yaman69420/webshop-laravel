<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'In afwachting',
            self::Paid      => 'Betaald',
            self::Shipped   => 'Verzonden',
            self::Cancelled => 'Geannuleerd',
            self::Refunded  => 'Terugbetaald',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'yellow',
            self::Paid      => 'green',
            self::Shipped   => 'indigo',
            self::Cancelled => 'red',
            self::Refunded  => 'gray',
        };
    }
}
