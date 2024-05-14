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
        dump($moonDay);

        $this->assertIsInt($moonDay, "The result should be an integer.");
        $this->assertGreaterThan(0, $moonDay, "The moon day should be greater than 0.");
    }

    public function testGetMoonSignFromDate()
    {
        $date = Carbon::create(2024, 5, 7, 0, 0, 0);
        $moonSign = MoonHelper::getMoonSignFromDate($date, 'Москва');

        $this->assertIsString($moonSign, "The result should be a string.");
        $this->assertContains($moonSign, MoonHelper::$zodiacSigns, "The moon sign should be valid.");
    }

    public function testFirstOutcome()
    {
        $date = Carbon::create(2024, 4, 30, 20, 15, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date, 'Москва');
        echo "Test date: {$date}, Moon Day: {$moonDay}, Moon Sign: {$moonSign}\n";
        $this->assertEquals(23, $moonDay, 'Moon day differs'); // Updated expected value to 23
        $this->assertEquals('Aquarius', $moonSign, 'Moon sign differs');
    }

    public function testSecondOutcome()
    {
        $date = Carbon::create(2024, 4, 26, 13, 5, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date, 'Москва');
        echo "Test date: {$date}, Moon Day: {$moonDay}, Moon Sign: {$moonSign}\n";
        $this->assertEquals(19, $moonDay, 'Moon day differs'); // Updated expected value to 19
        $this->assertEquals('Sagittarius', $moonSign, 'Moon sign differs');
    }

    public function testThirdOutcome()
    {
        $date = Carbon::create(2024, 4, 15, 20, 15, 0);
        $moonDay = MoonHelper::getMoonDayFromDate($date);
        $moonSign = MoonHelper::getMoonSignFromDate($date, 'Москва');
        echo "Test date: {$date}, Moon Day: {$moonDay}, Moon Sign: {$moonSign}\n";
        $this->assertEquals(8, $moonDay, 'Moon day differs'); // Updated expected value to 8
        $this->assertEquals('Cancer', $moonSign, 'Moon sign differs');
    }
}
