<?php

namespace App;

use Carbon\Carbon;

class MoonHelper
{
    // Constants and astronomical data from the old script
    const SYNMONTH = 29.53058868;
    const EPOCH = 2444238.5; // 1980 January 0.0
    const MMLONG = 64.975464; // Moon's mean longitude at the epoch
    const MMLONGP = 349.383063; // Mean longitude of the perigee at the epoch
    const MLNODE = 151.950429; // Mean longitude of the node at the epoch
    const MINC = 5.145396; // Inclination of the Moon's orbit
    const MECC = 0.054900; // Eccentricity of the Moon's orbit
    const MSMAX = 384401.0; // Semi-major axis of Moon's orbit in km

    // Function to convert angle in degrees to radians
    private static function degToRad($deg)
    {
        return $deg * (M_PI / 180);
    }

    // Function to calculate the Julian date from Carbon instance
    private static function getJulianDate(Carbon $date)
    {
        return $date->timestamp / 86400 + 2440587.5;
    }

    // Function to calculate moon day from a date
    public static function getMoonDayFromDate(Carbon $date)
    {
        $julian = self::getJulianDate($date);
        $k = floor(($julian - self::EPOCH) / self::SYNMONTH);
        return $k % 30 + 1; // Moon day is a cycle in 1 to 30
    }

    // Function to calculate moon sign from a date
    public static function getMoonSignFromDate(Carbon $date)
    {
        $julian = self::getJulianDate($date);
        $degrees = ($julian - self::EPOCH) * 360 / self::SYNMONTH;
        $position = $degrees % 360;

        // Assuming phasesArray has predefined moon signs as per degrees
        $signs = [
            'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
            'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'
        ];

        $index = floor($position / 30);
        return $signs[$index];
    }

    // Public static property to simulate predefined phases array for validation in tests
    public static $phasesArray = [
        'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
        'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'
    ];
}
