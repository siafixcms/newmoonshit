<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\MoonHelper;
use Carbon\Carbon;

class MoonHelpersTest extends TestCase
{
    public function testGetMoonDayFromDate()
    {
        $date = Carbon::create(2024, 4, 30, 0, 0, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);

        $this->assertIsInt($moonDay, "The result should be an integer.");
        $this->assertGreaterThan(0, $moonDay, "The moon day should be greater than 0.");
    }

    public function testGetMoonSignFromDate()
    {
        $date = Carbon::create(2024, 5, 7, 0, 0, 0);
        $moonSign = MoonHelper::getMoonSignFromDate($date);
        dump($moonSign);

        $this->assertIsString($moonSign, "The result should be a string.");
        $this->assertContains($moonSign, MoonHelper::$phasesArray, "The moon sign should be valid.");
    }

    public function testFirstOutcome()
    {
        $date = Carbon::create(2024, 4, 30, 0, 0, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date);

        $this->assertEquals(22, $moonDay, 'Moon day differs');
        $this->assertEquals('Aquarius', $moonSign, 'Moon day differs');
        
    }

    public function testSecondOutcome()
    {
        $date = Carbon::create(2024, 4, 26, 0, 0, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date);

        $this->assertEquals(18, $moonDay, 'Moon day differs');
        $this->assertEquals('Sagittarius', $moonSign, 'Moon day differs');
        
    }

    public function testThirdOutcome()
    {
        $date = Carbon::create(2024, 4, 15, 0, 0, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date);

        $this->assertEquals(8, $moonDay, 'Moon day differs');
        $this->assertEquals('Cancer', $moonSign, 'Moon day differs');
        
    }
}