<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\MoonHelper;
use Carbon\Carbon;

class MoonHelpersTest extends TestCase
{
    public function testGetMoonDayFromDate()
    {
        $date = Carbon::create(1988, 5, 7, 0, 0, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);

        $this->assertIsInt($moonDay, "The result should be an integer.");
        $this->assertGreaterThan(0, $moonDay, "The moon day should be greater than 0.");
    }

    public function testGetMoonSignFromDate()
    {
        $date = Carbon::create(1989, 9, 20, 0, 0, 0);
        $moonSign = MoonHelper::getMoonSignFromDate($date);
        dump($moonSign);

        $this->assertIsString($moonSign, "The result should be a string.");
        $this->assertContains($moonSign, MoonHelper::$phasesArray, "The moon sign should be valid.");
    }
}