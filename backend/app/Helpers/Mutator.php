<?php

namespace App\Helpers;

class Mutator
{
    public static function numberToDigits(string $number): string
    {
        return trim(preg_replace('/^1|\D/', "", $number));
    }

    public static function digitsToRuPhoneNumber(string $number): string
    {
        $countryCode = substr($number, 0, 1);
        $regionCode = substr($number, 1, 3);
        $partOne = substr($number, 4, 3);
        $partTwo = substr($number, 7, 2);
        $partThree = substr($number, 9, 2);
        return "+$countryCode ($regionCode) $partOne-$partTwo-$partThree";
    }
}
