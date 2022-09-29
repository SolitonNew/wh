<?php

namespace App\Library;

use Carbon\Carbon;

class SunTime
{
    /**
     * Calculates the time of sunrise or sunset on a specified date with
     * location parameters.
     *
     * @param Carbon $date
     * @param float $latitude
     * @param float $longitude
     * @param float $zenith
     * @param string $sunTime
     * @return Carbon|null
     */
    public static function get(Carbon $date, float $latitude, float $longitude, float $zenith, string $sunTime): Carbon|null
    {
        // 1. first calculate the day of the year
        $N = $date->copy()->dayOfYear();

        // 2. convert the longitude to hour value and calculate an approximate time

        $lngHour = $longitude / 15;

        if ($sunTime == 'SUNRISE') {
            $t = $N + ((6 - $lngHour) / 24);
        } else {
            $t = $N + ((18 - $lngHour) / 24);
        }

        // 3. calculate the Sun's mean anomaly

        $M = (0.9856 * $t) - 3.289;

        //4. calculate the Sun's true longitude

        $L = $M + (1.916 * sin(deg2rad($M))) + (0.020 * sin(deg2rad(2 * $M))) + 282.634;

        // NOTE: L potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
        $L = self::adjust($L, 360);

        // 5a. calculate the Sun's right ascension

        $RA = rad2deg(atan(0.91764 * tan(deg2rad($L))));

        // NOTE: RA potentially needs to be adjusted into the range [0,360) by adding/subtracting 360
        $RA = self::adjust($RA, 360);

        // 5b. right ascension value needs to be in the same quadrant as L

        $Lquadrant = floor($L / 90) * 90;
        $RAquadrant = floor($RA / 90) * 90;
        $RA = $RA + ($Lquadrant - $RAquadrant);

        // 5c. right ascension value needs to be converted into hours

        $RA = $RA / 15;

        // 6. calculate the Sun's declination

        $sinDec = 0.39782 * sin(deg2rad($L));
        $cosDec = cos(asin($sinDec));

        // 7a. calculate the Sun's local hour angle

        $HCos = (cos(deg2rad($zenith)) - ($sinDec * sin(deg2rad($latitude)))) / ($cosDec * cos(deg2rad($latitude)));
        if (($HCos > 1) || ($HCos < -1)) {
            return null;
        }

        // 7b. finish calculating H and convert into hours

        if ($sunTime == 'SUNRISE') {
            $H = 360 - rad2deg(acos($HCos));
        } else {
            $H = rad2deg(acos($HCos));
        }

        $H = $H / 15;

        // 8. calculate local mean time of rising/setting
        $LocalT = $H + $RA - (0.06571 * $t) - 6.622;

        // 9. adjust back to UTC
        $UT = $LocalT - $lngHour;

        # NOTE: UT potentially needs to be adjusted into the range [0,24) by adding/subtracting 24
        $st = self::adjust($UT, 24);

        return Carbon::create($date->year, $date->month, $date->day, 0, 0, 0, 'UTC')->addSecond($st * 3600);
    }

    /**
     * @param float $value
     * @param int $bounds
     * @return float
     */
    private static function adjust(float $value, int $bounds): float
    {
        while ($value >= $bounds) {
            $value = $value - $bounds;
        }
        while ($value < 0) {
            $value = $value + $bounds;
        }

        return $value;
    }
}
