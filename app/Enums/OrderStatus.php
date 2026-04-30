<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'In afwachting',
            self::Processing => 'In verwerking',
            self::Paid       => 'Betaald',
            self::Shipped    => 'Verzonden',
            self::Delivered  => 'Geleverd',
            self::Cancelled  => 'Geannuleerd',
            self::Refunded   => 'Terugbetaald',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'yellow',
            self::Processing => 'blue',
            self::Paid       => 'green',
            self::Shipped    => 'indigo',
            self::Delivered  => 'emerald',
            self::Cancelled  => 'red',
            self::Refunded   => 'gray',
        };
    }
}
