<?php

namespace App\Enums;

enum ContactType: string
{
    case CONTACT = 'contact';

    public static function all(): array
    {
        return [
            self::CONTACT->value,
        ];
    }
}
