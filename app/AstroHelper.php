<?php

namespace App;

use Carbon\Carbon;

class AstroHelper
{
    const EPOCH = 2444238.5;
    const SYNMONTH = 29.53058868;

    const ELONGE = 278.833540;
    const ELONGP = 282.596403;
    const ECCENT = 0.016718;
    const SUNSMAX = 1.495985e8;
    const SUNANGSIZ = 0.533128;

    const MMLONG = 64.975464;
    const MMLONGP = 349.383063;
    const MLNODE = 151.950429;
    const MINC = 5.145396;
    const MECC = 0.054900;
    const MANGSIZ = 0.5181;
    const MSMAX = 384401.0;
    const MPARALLAX = 0.9507;

    // Convert degrees to radians.
    public function toRad($deg)
    {
        return $deg * (M_PI / 180.0);
    }

    // Convert radians to degrees.
    public function toDeg($rad)
    {
        return $rad * (180.0 / M_PI);
    }

    // Fix angle to range 0-360.
    public function fixAngle($angle)
    {
        return $angle - 360.0 * floor($angle / 360.0);
    }

    // Sin function with degree input.
    public function dsin($deg)
    {
        return sin($this->toRad($deg));
    }

    // Cos function with degree input.
    public function dcos($deg)
    {
        return cos($this->toRad($deg));
    }

    // Convert UNIX timestamp to Julian date.
    public function jTime($timestamp)
    {
        return ($timestamp / 86400) + 2440587.5;
    }

    // Convert Julian date to UNIX epoch timestamp.
    public function jDayToSecs($jday)
    {
        return ($jday - 2440587.5) * 86400;
    }

    // Convert Julian date to year, month, and day.
    public function jYear($td, &$yy, &$mm, &$dd)
    {
        $td += 0.5; // Convert to civil.
        $z = floor($td);
        $f = $td - $z;

        if ($z < 2299161.0) {
            $a = $z;
        } else {
            $alpha = floor(($z - 1867216.25) / 36524.25);
            $a = $z + 1 + $alpha - floor($alpha / 4);
        }

        $b = $a + 1524;
        $c = floor(($b - 122.1) / 365.25);
        $d = floor(365.25 * $c);
        $e = floor(($b - $d) / 30.6001);

        $dd = $b - $d - floor(30.6001 * $e) + $f;
        $mm = $e < 14 ? $e - 1 : $e - 13;
        $yy = $mm > 2 ? $c - 4716 : $c - 4715;
    }

    public function meanPhase($sdate, $k)
    {
        $t = ($sdate - 2415020.0) / 36525;
        $t2 = $t * $t;
        $t3 = $t2 * $t;

        $nt1 = 2415020.75933 + self::SYNMONTH * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * $this->dsin(166.56 + 132.87 * $t - 0.009173 * $t2);

        return $nt1;
    }

    public function truePhase($k, $phase)
    {
        $apcor = 0;

        $k += $phase;
        $t = $k / 1236.85;
        $t2 = $t * $t;
        $t3 = $t2 * $t;

        $pt = 2415020.75933 + self::SYNMONTH * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * $this->dsin(166.56 + 132.87 * $t - 0.009173 * $t2);

        $m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;
        $mprime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;

        if ($phase < 0.01 || abs($phase - 0.5) < 0.01) {
            $pt += (0.1734 - 0.000393 * $t) * $this->dsin($m)
                + 0.0021 * $this->dsin(2 * $m)
                - 0.4068 * $this->dsin($mprime)
                + 0.0161 * $this->dsin(2 * $mprime)
                - 0.0004 * $this->dsin(3 * $mprime)
                + 0.0104 * $this->dsin(2 * $f)
                - 0.0051 * $this->dsin($m + $mprime)
                - 0.0074 * $this->dsin($m - $mprime)
                + 0.0004 * $this->dsin(2 * $f + $m)
                - 0.0004 * $this->dsin(2 * $f - $m)
                - 0.0006 * $this->dsin(2 * $f + $mprime)
                + 0.0010 * $this->dsin(2 * $f - $mprime)
                + 0.0005 * $this->dsin($m + 2 * $mprime);

            $apcor = 1;
        } elseif (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01) {
            $pt += (0.1721 - 0.0004 * $t) * $this->dsin($m)
                + 0.0021 * $this->dsin(2 * $m)
                - 0.6280 * $this->dsin($mprime)
                + 0.0089 * $this->dsin(2 * $mprime)
                - 0.0004 * $this->dsin(3 * $mprime)
                + 0.0079 * $this->dsin(2 * $f)
                - 0.0119 * $this->dsin($m + $mprime)
                - 0.0047 * $this->dsin($m - $mprime)
                + 0.0003 * $this->dsin(2 * $f + $m)
                - 0.0004 * $this->dsin(2 * $f - $m)
                - 0.0006 * $this->dsin(2 * $f + $mprime)
                + 0.0021 * $this->dsin(2 * $f - $mprime)
                + 0.0003 * $this->dsin($m + 2 * $mprime)
                + 0.0004 * $this->dsin($m - 2 * $mprime)
                - 0.0003 * $this->dsin(2 * $m + $mprime);

            if ($phase < 0.5) {
                $pt += 0.0028 - 0.0004 * $this->dcos($m) + 0.0003 * $this->dcos($mprime);
            } else {
                $pt += -0.0028 + 0.0004 * $this->dcos($m) - 0.0003 * $this->dcos($mprime);
            }
            $apcor = 1;
        }

        if (!$apcor) {
            throw new \Exception("Invalid phase selector: $phase");
        }

        return $pt;
    }

    public function phaseHunt($time = 0)
    {
        if (empty($time)) {
            $time = time();
        }

        $sdate = $this->jTime($time);
        $adate = $sdate - 45;

        $this->jYear($adate, $yy, $mm, $dd);

        $k1 = floor(($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685);
        $adate = $nt1 = $this->meanPhase($adate, $k1);

        while (true) {
            $adate += self::SYNMONTH;
            $k2 = $k1 + 1;
            $nt2 = $this->meanPhase($adate, $k2);

            if ($nt1 <= $sdate && $nt2 > $sdate) {
                break;
            }

            $nt1 = $nt2;
            $k1 = $k2;
        }

        return [
            $this->jDayToSecs($this->truePhase($k1, 0.0)),
            $this->jDayToSecs($this->truePhase($k1, 0.25)),
            $this->jDayToSecs($this->truePhase($k1, 0.5)),
            $this->jDayToSecs($this->truePhase($k1, 0.75)),
            $this->jDayToSecs($this->truePhase($k2, 0.0))
        ];
    }

    public function phaseList($sdate, $edate)
    {
        if (empty($sdate) || empty($edate)) {
            return [];
        }

        $sdate = $this->jTime($sdate);
        $edate = $this->jTime($edate);

        $phases = [];
        $d = $k = $yy = $mm = 0;

        $this->jYear($sdate, $yy, $mm, $d);
        $k = floor(($yy + (($mm - 1) * (1.0 / 12.0)) - 1900) * 12.3685) - 2;

        while (true) {
            ++$k;
            foreach ([0.0, 0.25, 0.5, 0.75] as $phase) {
                $d = $this->truePhase($k, $phase);
                if ($d >= $edate) {
                    return $phases;
                }
                if ($d >= $sdate) {
                    if (empty($phases)) {
                        array_push($phases, floor(4 * $phase));
                    }
                    array_push($phases, $this->jDayToSecs($d));
                }
            }
        }
    }

    // Kepler's equation solver
    public function kepler($m, $ecc)
    {
        $EPSILON = 1e-6;
        $m = $this->toRad($m);
        $e = $m;

        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $EPSILON);

        return $e;
    }

    public function phase($time = 0, $Fi, $Dol, $gm)
    {
        if (empty($time)) {
            $time = time();
        }

        $pdate = $this->jTime($time);

        $Day = $pdate - self::EPOCH;
        $N = $this->fixAngle((360 / 365.2422) * $Day);
        $M = $this->fixAngle($N + self::ELONGE - self::ELONGP);
        $Ec = $this->kepler($M, self::ECCENT);

        $Ec = sqrt((1 + self::ECCENT) / (1 - self::ECCENT)) * tan($Ec / 2);
        $Ec = 2 * $this->toDeg(atan($Ec));
        $Lambdasun = $this->fixAngle($Ec + self::ELONGP);

        $F = (1 + self::ECCENT * cos($this->toRad($Ec))) / (1 - self::ECCENT * self::ECCENT);
        $SunDist = self::SUNSMAX / $F;
        $SunAng = $F * self::SUNANGSIZ;

        // Moon's position calculation
        $ml = $this->fixAngle(13.1763966 * $Day + self::MMLONG);
        $MM = $this->fixAngle($ml - 0.1114041 * $Day - self::MMLONGP);
        $MN = $this->fixAngle(self::MLNODE - 0.0529539 * $Day);

        $Ev = 1.2739 * sin($this->toRad(2 * ($ml - $Lambdasun) - $MM));
        $Ae = 0.1858 * sin($this->toRad($M));
        $A3 = 0.37 * sin($this->toRad($M));
        $MmP = $MM + $Ev - $Ae - $A3;

        $mEc = 6.2886 * sin($this->toRad($MmP));
        $A4 = 0.214 * sin($this->toRad(2 * $MmP));
        $lP = $ml + $Ev + $mEc - $Ae + $A4;
        $V = 0.6583 * sin($this->toRad(2 * ($lP - $Lambdasun)));
        $lPP = $lP + $V;

        $NP = $MN - 0.16 * sin($this->toRad($M));
        $y = sin($this->toRad($lPP - $NP)) * cos($this->toRad(self::MINC));
        $x = cos($this->toRad($lPP - $NP));

        $Lambdamoon = $this->toDeg(atan2($y, $x)) + $NP;
        $BetaM = $this->toDeg(asin(sin($this->toRad($lPP - $NP)) * sin($this->toRad(self::MINC))));

        $alfa1 = $this->eliamba($BetaM, $Lambdamoon);
        $beta1 = $this->ebeta($BetaM, $Lambdamoon);

        $betaSm = 0.05 * cos($this->toRad($lPP - $NP));
        $liambaSm = 0.55 + 0.06 * cos($this->toRad($MmP));
        $BetaM2 = $BetaM + $betaSm * 12;
        $Lambdamoon2 = $Lambdamoon + $liambaSm * 12;

        $beta2 = $this->ebeta($BetaM2, $Lambdamoon2);
        $alfa2 = $this->eliamba($BetaM2, $Lambdamoon2);

        $Fi = $this->degtime4($Fi);
        $Dol = $this->degtime4($Dol);

        $h = 0;
        $u = $this->toDeg(atan(0.996647 * tan($this->toRad($Fi))));
        $PsinFi = (0.996647 * sin($this->toRad($u))) + (($h / 6378140) * sin($this->toRad($Fi)));
        $PcosFi = cos($this->toRad($u)) + (($h / 6378140) * cos($this->toRad($Fi)));

        $betaI = ($beta1 + $beta2) / 2;
        $Hgeo = $this->toDeg(acos(-tan($this->toRad($Fi)) * tan($this->toRad($betaI))));

        $MoonDist = (self::MSMAX * (1 - self::MECC * self::MECC)) / (1 + self::MECC * cos($this->toRad($MmP + $mEc)));
        $MoonDFrac = $MoonDist / self::MSMAX;
        $MoonAng = self::MANGSIZ / $MoonDFrac;
        $MoonPar = self::MPARALLAX / $MoonDFrac;
        $MoonAge = $lPP - $Lambdasun;
        $mage = self::SYNMONTH * ($this->fixAngle($MoonAge) / 360.0);

        $r = 60.268322 * $MoonDFrac;

        $Par1 = $this->toDeg(atan(($PcosFi * sin($this->toRad($Hgeo))) / ($r * cos($this->toRad($beta1)) - $PcosFi * cos($this->toRad($Hgeo))))) / 15;
        $Par2 = $this->toDeg(atan(($PcosFi * sin($this->toRad($Hgeo))) / ($r * cos($this->toRad($beta2)) - $PcosFi * cos($this->toRad($Hgeo))))) / 15;

        $alfaIs1 = $alfa1 - $Par1;
        $alfaIs2 = $alfa2 - $Par2;

        $His1 = $Hgeo + $Par1;
        $His2 = $Hgeo + $Par2;

        $betaIs1 = $this->toDeg(atan(cos($this->toRad($His1)) * ($r * sin($this->toRad($beta1)) - $PsinFi) / ($r * cos($this->toRad($beta1)) * cos($this->toRad($Hgeo)) - $PcosFi)));
        $betaIs2 = $this->toDeg(atan(cos($this->toRad($His2)) * ($r * sin($this->toRad($beta2)) - $PsinFi) / ($r * cos($this->toRad($beta2)) * cos($this->toRad($Hgeo)) - $PcosFi)));

        $Hv1 = (1 / 15) * $this->toDeg(acos(-tan($this->toRad($Fi)) * tan($this->toRad($betaIs1))));
        $Hv2 = (1 / 15) * $this->toDeg(acos(-tan($this->toRad($Fi)) * tan($this->toRad($betaIs2))));

        $LSTr1 = $this->time24(24 - $Hv1 + $alfaIs1);
        $LSTr2 = $this->time24(24 - $Hv2 + $alfaIs2);

        $LSTs1 = $this->time24($alfaIs1 + $Hv1);
        $LSTs2 = $this->time24($alfaIs2 + $Hv2);

        $TR = (12.03 * $LSTr1) / (12.03 + $LSTr1 - $LSTr2);
        $TS = (12.03 * $LSTs1) / (12.03 + $LSTs1 - $LSTs2);

        $betaSr = ($betaIs1 + $betaIs2) / 2;
        $Diam = 0.5181 / $MoonDist;
        $Fir = $this->toDeg(acos(sin($this->toRad($Fi)) / cos($this->toRad($betaSr))));

        $R = 0.567;
        $Xr = $R + ($Diam / 2);
        $Yr = $this->toDeg(asin(sin($this->toRad($Xr)) / sin($this->toRad($Fir))));
        $Tp = 240 * $Yr / cos($this->toRad($betaSr));
        $Tp = $Tp / 3600;

        $Tri = $this->time24($TR - $Tp);
        $Tsi = $this->time24($TS + $Tp);

        $Doli = $Dol / 15;

        $GSTr = $this->time24($Tri - $Doli);
        $GSTs = $this->time24($Tsi - $Doli);

        $TR = $this->time24($TR - $Doli);
        $TS = $this->time24($TS - $Doli);

        $To = $this->time24((0.0657098 * $Day) - 17.411472);
        $GMTr = $GMTreal = $this->time24((($GSTr - $To) * 0.997270) + $gm);
        $GMTs = $this->time24((($GSTs - $To) * 0.997270) + $gm);

        $TR = $this->time24((($TR - $To) * 0.997270) + $gm);
        $TS = $this->time24((($TS - $To) * 0.997270) + $gm);

        $TR = $this->degtime2($TR);
        $TS = $this->degtime2($TS);
        $GMTr = $this->degtime2($GMTr);
        $GMTs = $this->degtime2($GMTs);

        $moonlam = $Lambdamoon;

        if ($moonlam < 0) {
            $moonlam = 360 + $Lambdamoon;
        }
        if ($moonlam > 360) {
            $moonlam -= 360;
        }

        $moonlambada = $moonlam;
        $cikl = 30;
        if (($cikl > $moonlam) || ($cikl == $moonlam)) {
            $zstart = $moonlam;
            $znak = 1;
        } else {
            $znak = 1;
            do {
                $moonlam -= $cikl;
                $znak += 1;
            } while ($cikl < $moonlam);
            $zstart = ($znak * $cikl) - $moonlambada;
        }
        $zstart = $this->degtimeg($zstart);

        $moonlam28 = $Lambdamoon;
        if ($moonlam28 > 360) {
            $moonlam28 -= 360;
        }
        $cikl28 = 360 / 28;
        if (($cikl28 > $moonlam28) || ($cikl28 == $moonlam28)) {
            $zs28 = $moonlam28;
            $znak28 = 1;
        } else {
            $znak28 = 1;
            do {
                $moonlam28 -= $cikl28;
                $znak28 += 1;
            } while ($cikl28 < $moonlam28);
            $zs28 = ($znak28 * $cikl28) - $moonlambada;
        }

        $MoonPhase = (1 - cos($this->toRad($MoonAge))) / 2;
        $mpfrac = $this->fixAngle($MoonAge) / 360.0;

        return [
            $mpfrac, $MoonPhase, $mage, $MoonDist, $MoonAng, $SunDist, $SunAng, $GMTr, $GMTs, $GMTreal, $znak, $zstart, $znak28, $Fi, $Dol
        ];
    }

    public function ebeta($argbeta, $argliamba)
    {
        $Ei = 23.441884;
        $sinBeta = (sin($this->toRad($argbeta)) * cos($this->toRad($Ei))) +
            (cos($this->toRad($argbeta)) * sin($this->toRad($Ei)) * sin($this->toRad($argliamba)));
        $beta = $this->toDeg(asin($sinBeta));

        return $beta;
    }

    public function eliamba($ebeta, $eliamba)
    {
        $Ei = 23.441884;
        $y = sin($this->toRad($eliamba)) * cos($this->toRad($Ei)) - tan($this->toRad($ebeta)) * sin($this->toRad($Ei));
        $x = cos($this->toRad($eliamba));

        $alfa_is = $this->toDeg(atan($y / $x));
        if ($y < 0 && $x > 0) $eliamba = ($alfa_is + 360) / 15;
        elseif ($y > 0 && $x < 0) $eliamba = ($alfa_is + 180) / 15;
        elseif ($y > 0 && $x > 0) $eliamba = ($alfa_is + 360) / 15;
        elseif ($y < 0 && $x < 0) $eliamba = ($alfa_is + 180) / 15;

        return $eliamba;
    }

    public function time24($arg)
    {
        while ($arg > 24 || $arg == 24) {
            $arg -= 24;
        }
        if ($arg < 0) {
            $arg += 24;
        }

        return $arg;
    }

    public function degtime($arg)
    {
        $arg1 = $arg * 60;
        $arg2 = ($arg1 - floor($arg1)) * 60;
        $arg3 = ($arg2 - floor($arg2)) * 60;

        return floor($arg1) . '°' . floor($arg2) . "'" . floor($arg3) . '"';
    }

    public function degtimeg($arg)
    {
        $arg1 = $arg;
        $arg2 = ($arg1 - floor($arg1)) * 60;
        $arg3 = ($arg2 - floor($arg2)) * 60;

        return floor($arg1) . '°' . floor($arg2) . "'" . floor($arg3) . '"';
    }

    public function degtime2($arg)
    {
        $arg1 = $arg;
        $arg2 = ($arg1 - floor($arg1)) * 60;

        return floor($arg1) . ':' . floor($arg2);
    }

    public function degtime3($arg)
    {
        $arg1 = $arg;
        $arg2 = ($arg1 - floor($arg1)) * 24;
        $arg3 = ($arg2 - floor($arg2)) * 60;

        return floor($arg1) . 'y ' . floor($arg2) . 'h ' . floor($arg3) . 'm';
    }

    public function degtime4($arg)
    {
        $arg1 = $arg;
        $arg2 = ($arg1 - floor($arg1)) / 60 * 100;
        $arg3 = $arg2 + floor($arg1);

        return $arg3;
    }

    public function sgn($arg)
    {
        return $arg < 0 ? -1 : ($arg > 0 ? 1 : 0);
    }
}