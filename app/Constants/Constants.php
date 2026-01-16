<?php

namespace App\Constants;

class Constants
{
    const ADMIN_ROLE = 'admin';
    const USER_ROLE = 'user';
    const SECTIONS_TYPES = ['super'];

    const TRANSACTION_TYPES = [
        'deposit' => [
            'ar' => 'ايداع',
            'en' => 'deposit',
        ],
        'Withdrawal' => [
            'ar' => 'سحب',
            'en' => 'Withdrawal',
        ],
    ];
    const TRANSACTION_STATUSES = [
        'cancelled' => [
            'ar' => 'ملغية',
            'en' => 'cancelled',
        ],
        'pending' => [
            'ar' => 'معلقة',
            'en' => 'pending',
        ],
        'processing' => [
            'ar' => 'جار المعالجة',
            'en' => 'processing',
        ],
        'completed' => [
            'ar' => 'منتهية',
            'en' => 'completed',
        ],
    ];
}
