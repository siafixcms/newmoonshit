<?php

namespace App;

use Carbon\Carbon;

class MoonHelper
{

    const EPOCH = 2444238.5; // 1980 January 0.0
    const ELONGE = 278.833540; // ecliptic longitude of the Sun at epoch 1980.0
    const ELONGP = 282.596403; // ecliptic longitude of the Sun at perigee
    const ECCENT = 0.016718; // eccentricity of Earth's orbit
    const SUNSMAX = 1.495985e8; // semi-major axis of Earth's orbit, km
    const SUNANGSIZ = 0.533128; // sun's angular size, degrees, at semi-major axis distance

    // Lunar orbit elements, epoch 1980.0
    const MMLONG = 64.975464; // moon's mean longitude at the epoch
    const MMLONGP = 349.383063; // mean longitude of the perigee at the epoch
    const MLNODE = 151.950429; // mean longitude of the node at the epoch
    const MINC = 5.145396; // inclination of the Moon's orbit
    const MECC = 0.054900; // eccentricity of the Moon's orbit
    const MANGSIZ = 0.5181; // moon's angular size at distance a from Earth
    const MSMAX = 384401.0; // semi-major axis of Moon's orbit in km
    const MPARALLAX = 0.9507; // parallax at distance a from Earth
    const SYNMONTH = 29.53058868;

    public static function getMoonDayFromDate(Carbon $date)
    {
        
    }

    private static $cityCoordinates = [
        "Москва" => ['north' => 55.45, 'east' => 37.37, 'gmt' => 3],
        // Add other cities here following the same pattern
    ];

    public static $zodiacSigns = [
        1 => "Aries", 2 => "Taurus", 3 => "Gemini", 4 => "Cancer",
        5 => "Leo", 6 => "Virgo", 7 => "Libra", 8 => "Scorpio",
        9 => "Sagittarius", 10 => "Capricorn", 11 => "Aquarius", 12 => "Pisces"
    ];

    public static function getMoonSignFromDate(Carbon $date, $city) {
        // Validate the city
        if (!isset(self::$cityCoordinates[$city])) {
            throw new \Exception("City not found in coordinates list.");
        }

        // Get city coordinates and timezone
        $cityInfo = self::$cityCoordinates[$city];
        $north = $cityInfo['north'];
        $east = $cityInfo['east'];
        $gmt = $cityInfo['gmt'];

        // Prepare date and time parameters
        $timestamp = $date->timestamp;
        $tzone = '+' . $gmt;

        // Assume $leto is summer time adjustment, set to 0 if not used
        $leto = 0;
        $zonedop = $gmt + $leto;

        // Calculate moon data
        $moondata = self::phase($timestamp, $north, $east, $zonedop);

        // Determine moon sign from moon data
        $moonSignIndex = $moondata[10];
        $moonSign = self::$zodiacSigns[$moonSignIndex] ?? "Sign not found";

        return $moonSign;
    }

    // Converts degrees to radians.
    public static function torad($arg) {
        return ($arg * (pi() / 180.0));
    }

    // Converts radians to degrees.
    public static function todeg($arg) {
        return ($arg * (180.0 / pi()));
    }

    // Returns the sine of a degree.
    public static function dsin($arg) {
        return sin(self::torad($arg));
    }

    // Returns the cosine of a degree.
    public static function dcos($arg) {
        return cos(self::torad($arg));
    }

    // Fixes an angle.
    public static function fixangle($arg) {
        return ($arg - 360.0 * (floor($arg / 360.0)));
    }

    // Converts Julian day to UNIX timestamp.
    public static function jdaytosecs($jday=0) {
        $stamp = ($jday - 2440587.5) * 86400;  // (juliandate - jdate of unix epoch) * (seconds per julian day)
        return $stamp;
    }

    // Solves the Kepler equation.
    public static function kepler($m, $ecc) {
        $EPSILON = 1e-6;
        $m = self::torad($m);
        $e = $m;
        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $EPSILON);
        return ($e);
    }

    public static function time24 ($arg) {
        if ($arg > 24) {
        do { $arg=$arg-24; }
        while ($arg > 24 || $arg==24); }
        if ($arg < 0) $arg=$arg+24; 
        return ($arg); }

    // Main function to calculate moon phases and other properties.
    public static function phase ( $time=0, $Fi, $Dol, $gm )  {
        if ( empty($time) || $time == 0 )  {
            $time = time();
        }
        $pdate = self::jtime( $time );
    
        $pphase;
        $mage;
        $dist;
        $angdia;
        $sudist;
        $suangdia;
        $Day = $pdate - self::EPOCH;
        $N = self::fixangle( (360 / 365.2422) * $Day );
        $M = self::fixangle( $N + self::ELONGE - self::ELONGP );
        $Ec = self::kepler( $M, self::ECCENT );
        $Ec = sqrt( (1 + self::ECCENT) / (1 - self::ECCENT) ) * tan( $Ec / 2 );
        $Ec = 2 * self::todeg( atan($Ec) );
        $Lambdasun = self::fixangle( $Ec + self::ELONGP );
        $F = ( (1 + self::ECCENT * cos(self::torad($Ec))) / (1 - self::ECCENT * self::ECCENT) );
        $SunDist = self::SUNSMAX / $F;
        $SunAng = $F * self::SUNANGSIZ;
        $ml = self::fixangle( 13.1763966 * $Day + self::MMLONG );
        $MM = self::fixangle( $ml - 0.1114041 * $Day - self::MMLONGP );
        $MN = self::fixangle( self::MLNODE - 0.0529539 * $Day );
        $Ev = 1.2739 * sin( self::torad(2 * ($ml - $Lambdasun) - $MM) );
        $Ae = 0.1858 * sin( self::torad($M) );
        $A3 = 0.37 * sin( self::torad($M) );
        $MmP = $MM + $Ev - $Ae - $A3;
        $mEc = 6.2886 * sin( self::torad($MmP) );
        $A4 = 0.214 * sin( self::torad(2 * $MmP) );
        $lP = $ml + $Ev + $mEc - $Ae + $A4;
        $V = 0.6583 * sin( self::torad(2 * ($lP - $Lambdasun)) );
        $lPP = $lP + $V;
        $NP = $MN - 0.16 * sin( self::torad($M) );
        $y = sin( self::torad($lPP - $NP) ) * cos( self::torad(self::MINC) );
        $x = cos(self::torad($lPP - $NP));
        $Lambdamoon = self::todeg( atan2($y, $x) );
        $Lambdamoon += $NP;
        $BetaM = self::todeg( asin(sin(self::torad($lPP - $NP)) * sin(self::torad(self::MINC))) );
        $alfa1=self::eliamba($BetaM,$Lambdamoon);
        $beta1=self::ebeta($BetaM,$Lambdamoon);
        $betaSm=0.05*cos(self::torad($lPP-$NP));
        $liambaSm=0.55+0.06*cos(self::torad($MmP));
        $BetaM2=$BetaM+$betaSm*12;
        $Lambdamoon2=$Lambdamoon+$liambaSm*12;
        $beta2 = self::ebeta ($BetaM2,$Lambdamoon2); 
        $alfa2 = self::eliamba($BetaM2,$Lambdamoon2);
        $Fi=self::degtime4($Fi);
        $Dol=self::degtime4($Dol);
        $h=0;
        $u=self::todeg(atan(0.996647*tan(self::torad($Fi))));
        $PsinFi=(0.996647*sin(self::torad($u)))+(($h/6378140)*sin(self::torad($Fi)));
        $PcosFi=(cos(self::torad($u)))+(($h/6378140)*cos(self::torad($Fi)));
        $betaI=($beta1+$beta2) / 2;
        $Hgeo=self::todeg(acos(-tan(self::torad($Fi))*tan(self::torad($betaI))));
        $MoonDist = ( self::MSMAX * (1 - self::MECC * self::MECC)) / (1 + self::MECC * cos(self::torad($MmP + $mEc)) );
        $MoonDFrac = $MoonDist / self::MSMAX;
        $MoonAng = self::MANGSIZ / $MoonDFrac;
        $MoonPar = self::MPARALLAX / $MoonDFrac;
        $MoonAge = $lPP - $Lambdasun;
        $mage = self::SYNMONTH * ( self::fixangle($MoonAge) / 360.0 );
        $r=60.268322*$MoonDFrac;
        $Par1=self::todeg(atan(($PcosFi*sin(self::torad($Hgeo))) / ($r*cos(self::torad($beta1))-$PcosFi*cos(self::torad($Hgeo))))) / 15;
        $Par2=self::todeg(atan(($PcosFi*sin(self::torad($Hgeo))) / ($r*cos(self::torad($beta2))-$PcosFi*cos(self::torad($Hgeo))))) / 15;
        $alfaIs1=$alfa1-$Par1;
        $alfaIs2=$alfa2-$Par2;
        $His1=$Hgeo+$Par1;
        $His2=$Hgeo+$Par2;
        $betaIs1=self::todeg(atan(cos(self::torad($His1)) * ($r*sin(self::torad($beta1))-$PsinFi) / ($r*cos(self::torad($beta1))*cos(self::torad($Hgeo))-$PcosFi)));
        $betaIs2=self::todeg(atan(cos(self::torad($His2)) * ($r*sin(self::torad($beta2))-$PsinFi) / ($r*cos(self::torad($beta2))*cos(self::torad($Hgeo))-$PcosFi)));
        $Hv1=(1/15)*(self::todeg(acos(-tan(self::torad($Fi))*tan(self::torad($betaIs1)))));
        $Hv2=(1/15)*(self::todeg(acos(-tan(self::torad($Fi))*tan(self::torad($betaIs2)))));
        $LSTr1=self::time24(24-$Hv1+$alfaIs1); 
        $LSTr2=self::time24(24-$Hv2+$alfaIs2); 
        $LSTs1=self::time24($alfaIs1+$Hv1); 
        $LSTs2=self::time24($alfaIs2+$Hv2); 
        $TR=(12.03*$LSTr1) / (12.03+$LSTr1-$LSTr2);
        $TS=(12.03*$LSTs1) / (12.03+$LSTs1-$LSTs2);
        $betaSr=($betaIs1+$betaIs2) / 2;
        $Diam=0.5181 / $MoonDist;
        $Fir=self::todeg(acos(sin(self::torad($Fi))/cos(self::torad($betaSr))));
        $R=0.567;
        $Xr=$R+($Diam/2);
        $Yr=self::todeg(asin(sin(self::torad($Xr))/sin(self::torad($Fir))));
        $Tp=240*$Yr/cos(self::torad($betaSr));
        $Tp=$Tp / 3600;
        $Tri=self::time24($TR-$Tp);
        $Tsi=self::time24($TS+$Tp);
        $Doli=$Dol / 15;
        $GSTr=self::time24($Tri-$Doli);
        $GSTs=self::time24($Tsi-$Doli);
        $TR=self::time24($TR-$Doli);
        $TS=self::time24($TS-$Doli);
        $To=self::time24((0.0657098 * $Day) - 17.411472); 
        $GMTr=$GMTreal=self::time24((($GSTr-$To)*0.997270)+$gm); 
        $GMTs=self::time24((($GSTs-$To)*0.997270)+$gm);
        $TR=self::time24((($TR-$To)*0.997270)+$gm); 
        $TS=self::time24((($TS-$To)*0.997270)+$gm);
        $TR=self::degtime2($TR);
        $TS=self::degtime2($TS);
        $GMTr=self::degtime2($GMTr);
        $GMTs=self::degtime2($GMTs);
        $moonlam=$Lambdamoon;if($Lambdamoon<0){$moonlam=360+$Lambdamoon;}
        if($moonlam > 360) $moonlam-=360;
        $moonlambada=$moonlam;
        $cikl=30;
        if (($cikl > $moonlam) || ($cikl==$moonlam))  { $zstart=$moonlam; $znak=1; }
        else { $znak=1;
        do { $moonlam-=$cikl;
             $znak+=1; }
             while($cikl < $moonlam);
             $zstart=($znak*$cikl)-$moonlambada; }
             $zstart=self::degtimeg($zstart);
        $moonlam28=$Lambdamoon;
        if($moonlam28 > 360) $moonlam28-=360;
        $cikl28=360/28;
        if (($cikl28 > $moonlam28) || ($cikl28==$moonlam28))  { $zs28=$moonlam28; $znak28=1; }
        else { $znak28=1;
        do { $moonlam28-=$cikl28;
             $znak28+=1; }
             while($cikl28 < $moonlam28);
             $zs28=($znak28*$cikl28)-$moonlambada; }	 
        $MoonPhase = (1 - cos(self::torad($MoonAge))) / 2;
        $pphase = $MoonPhase;
        $dist = $MoonDist;
        $angdia = $MoonAng;
        $sudist = $SunDist;
        $suangdia = $SunAng;
        $mpfrac = self::fixangle($MoonAge) / 360.0;
        return array ( $mpfrac, $pphase, $mage, $dist, $angdia, $sudist, $suangdia, $GMTr, $GMTs, $GMTreal, $znak, $zstart, $znak28, $Fi, $Dol);
     }

     public static function degtime2 ($arg) {  
        //if($arg < 0) $arg=$arg * (-);
        $arg1=$arg; //часы, градусы
        $arg2=($arg1 - floor($arg1))*60; //минуты
        $arg3=($arg2 - floor($arg2))*60; //секунды
        $arg=floor($arg1).':'.floor($arg2);
        return ($arg);  }

     public static function degtimeg ($arg) {  
        $arg1=$arg; //часы, градусы
        $arg2=($arg1 - floor($arg1))*60; //минуты
        $arg3=($arg2 - floor($arg2))*60; //секунды
        $arg=floor($arg1).'°'.floor($arg2)."'".floor($arg3).'"';
        return ($arg);  }

     public static function degtime4 ($arg) {  //часовую переводим в десятичную
        //if($arg < 0) $arg=$arg * (-);
        $arg1=$arg; //градусы
        $arg2=($arg1 - floor($arg1))/60*100; //минуты
        $arg3=$arg2 + floor($arg1); //десятичный результат
        $arg=$arg3;
        return ($arg);  }

     public static function ebeta ($argbeta, $argliamba) {
        $Ei=23.441884;
        $sinBeta=(sin(self::torad($argbeta))*cos(self::torad($Ei)))+(cos(self::torad($argbeta))*sin(self::torad($Ei))*sin(self::torad($argliamba)));
        $beta=self::todeg(asin($sinBeta));
         return ($beta);  }

    public static function eliamba ($ebeta, $eliamba) {
        $Ei=23.441884;
        $y=sin(self::torad($eliamba))*cos(self::torad($Ei)) - tan(self::torad($ebeta))*sin(self::torad($Ei));
        $x=cos(self::torad($eliamba));
        $alfa_is=self::todeg(atan($y/$x));
        if ($y < 0 && $x > 0) $eliamba=($alfa_is+360) / 15;
        if ($y > 0 && $x < 0) $eliamba=($alfa_is+180) / 15;
        if ($y > 0 && $x > 0) $eliamba=($alfa_is+360) / 15;
        if ($y < 0 && $x < 0) $eliamba=($alfa_is+180) / 15;
          return ($eliamba);
    }

    // Converts internal date and time to astronomical Julian time
    public static function jtime($timestamp) {
        $julian = ($timestamp / 86400) + 2440587.5; // (seconds / (seconds per day)) + julian date of epoch
        return $julian;
    }

}