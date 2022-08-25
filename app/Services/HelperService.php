<?php

namespace App\Services;

class HelperService
{
    public static function telephone($number) {
        return '+998' . str_replace(['(', ')', '-', ' '], '', $number);
    }
}
