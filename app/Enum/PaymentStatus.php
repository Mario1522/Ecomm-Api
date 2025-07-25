<?php

namespace App\Enum;

enum PaymentStatus : string
{
    case PENDING = 'pending'; //Awaiting payment
    case COMPLETED = 'completed'; //Payment has been successfully completed
    case FAILD = 'failed'; //Payment has failed
    case REFUNDED = 'refunded'; //Payment has been refunded to the customer

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
