<?php

namespace App\Traits;

trait HasArabicTrans{
    protected function asJson($value): bool|string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}