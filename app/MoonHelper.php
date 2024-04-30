<?php

namespace App;

use Carbon\Carbon;
use App\AstroHelper;

class MoonHelper
{
    public static $phasesArray = [
        'aries',
        'taurus',
        'gemini',
        'cancer',
        'leo',
        'virgo',
        'libra',
        'scorpio',
        'sagittarius',
        'capricorn',
        'aquarius',
        'pisces',
    ];

    public static function getMoonDayFromDate(Carbon $targetDate = null, $timezone = 'Europe/Riga') : int
    {
        $targetDate = $targetDate ?: Carbon::now($timezone);
        $astroHelper = new AstroHelper();

        $phaseDates = $astroHelper->phaseHunt($targetDate->timestamp);
        $newMoonDate = Carbon::createFromTimestamp($phaseDates[0]);

        $moonDay = $targetDate->diffInDays($newMoonDate) + 1;
        return $moonDay;
    }

    public static function getMoonSignFromDate(Carbon $targetDate = null, $timezone = 'Europe/Riga') : string
    {
        $targetDate = $targetDate ?: Carbon::now($timezone);

        // Assuming you have a way to calculate the moon's ecliptic longitude.
        $astroHelper = new AstroHelper();
        $moonPosition = $astroHelper->phase($targetDate->timestamp, 0, 0, 0)[0];

        $signIndex = intval($moonPosition / 30);
        return self::$phasesArray[$signIndex];
    }
}
