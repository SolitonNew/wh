<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Property extends Model
{
    protected $table = 'core_properties';
    public $timestamps = false;

    const VERSION = '2.17.8 alpha';

    /**
     * @return array
     */
    public static function getWebColors(): array
    {
        $item = self::whereName('WEB_COLOR')
            ->whereUserId(Auth::user()->id)
            ->first();
        if ($item && $item->value) {
            return json_decode($item->value, true);
        } else {
            return [];
        }
    }

    /**
     * @param array $colors
     * @return void
     */
    public static function setWebColors(array $colors): void
    {
        $userID = Auth::user()->id;

        $item = self::whereName('WEB_COLOR')
            ->whereUserId($userID)
            ->first();

        if (!$item) {
            $item = new Property();
            $item->user_id = $userID;
            $item->name = 'WEB_COLOR';
            $item->comm = '';
        }

        $item->value = json_encode($colors);
        $item->save();
    }

    /**
     * @return string
     */
    public static function getWebChecks(): string
    {
        $item = self::whereName('WEB_CHECKED')
            ->whereUserId(Auth::user()->id)
            ->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return '';
        }
    }

    /**
     * @param string $checks
     * @return void
     */
    public static function setWebChecks(string $checks): void
    {
        $userID = Auth::user()->id;

        $item = self::whereName('WEB_CHECKED')
            ->whereUserId($userID)
            ->first();
        if (!$item) {
            $item = new Property();
            $item->user_id = $userID;
            $item->name = 'WEB_CHECKED';
            $item->comm = '';
        }

        $item->value = $checks;
        $item->save();
    }

    /**
     * @return string
     */
    public static function getWebColumns(): string
    {
        $item = self::whereName('WEB_COLUMNS')
            ->whereUserId(Auth::user()->id)
            ->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return '3';
        }
    }

    /**
     * @param string $columns
     * @return void
     */
    public static function setWebColumns(string $columns): void
    {
        $userID = Auth::user()->id;

        $item = self::whereName('WEB_COLUMNS')
            ->whereUserId($userID)
            ->first();
        if (!$item) {
            $item = new Property();
            $item->user_id = $userID;
            $item->name = 'WEB_COLUMNS';
            $item->comm = '';
        }

        $item->value = $columns;
        $item->save();
    }

    /**
     * @return int
     */
    public static function getPlanMaxLevel(): int
    {
        $item = self::whereName('PLAN_MAX_LEVEL')->first();
        if ($item && $item->value) {
            return $item->value;
        } else {
            return 1;
        }
    }

    /**
     * @param int $maxLevel
     * @return void
     */
    public static function setPlanMaxLevel(int $maxLevel): void
    {
        $item = self::whereName('PLAN_MAX_LEVEL')->first();
        if ($item) {
            $item->value = $maxLevel;
        } else {
            $item = new Property();
            $item->name = 'PLAN_MAX_LEVEL';
            $item->comm = '';
            $item->value = $maxLevel;
        }
        $item->save();
    }

    /**
     * The cache for getFirmwareChanges.
     *
     * @var int|bool
     */
    protected static int|bool $firmwareChanges = false;

    /**
     * Returns the number of changes to the DB (what affects the firmware)
     * siens the last update.
     *
     * @return int
     */
    public static function getFirmwareChanges(): int
    {
        if (self::$firmwareChanges === false) {
            $item = self::whereName('FIRMWARE_CHANGES')->first();
            if ($item) {
                self::$firmwareChanges = $item->value ?: 0;
            } else {
                self::$firmwareChanges = 0;
            }
        }
        return self::$firmwareChanges;
    }

    /**
     * Sets the number of changes to the DB (what affects the firmware).
     *
     * @param int $count
     * @return void
     */
    public static function setFirmwareChanges(int $count): void
    {
        $item = self::whereName('FIRMWARE_CHANGES')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'FIRMWARE_CHANGES';
            $item->comm = '';
        }
        $item->value = $count;
        $item->save();

        self::$firmwareChanges = $count;
    }

    /**
     * @var string|bool
     */
    private static string|bool $timezone = false;

    /**
     * @return string|bool
     */
    public static function getTimezone(): string|bool
    {
        if (self::$timezone === false) {
            $item = self::whereName('TIMEZONE')->first();
            if ($item) {
                self::$timezone = $item->value ?: 'UTC';
            } else {
                self::$timezone = 'UTC';
            }
        }

        return self::$timezone;
    }

    /**
     * @param string $timezone
     * @return void
     */
    public static function setTimezone(string $timezone): void
    {
        $item = self::whereName('TIMEZONE')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'TIMEZONE';
            $item->comm = '';
        }

        $item->value = $timezone;
        $item->save();

        self::$timezone = $timezone;
    }

    /**
     * @var object|bool
     */
    private static object|bool $location = false;

    /**
     * @return object|bool
     */
    public static function getLocation(): object|bool
    {
        if (self::$location === false) {
            $item = self::whereName('LOCATION')->first();
            if ($item && $item->value) {
                self::$location = json_decode($item->value);
            } else {
                self::$location = (object)[
                    'latitude' => 0,
                    'longitude' => 0,
                ];
            }
        }

        return self::$location;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @return void
     */
    public static function setLocation(float $latitude, float $longitude): void
    {
        $item = self::whereName('LOCATION')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'LOCATION';
            $item->comm = '';
        }

        self::$location = (object)[
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $item->value = json_encode(self::$location);
        $item->save();
    }

    /**
     * @var object|bool
     */
    private static object|bool $forecast_settings = false;

    /**
     * @return object|bool
     */
    public static function getForecastSettings(): object|bool
    {
        if (self::$forecast_settings === false) {
            $item = self::whereName('FORECAST_SETTINGS')->first();
            if ($item && $item->value) {
                self::$forecast_settings = json_decode($item->value);
            } else {
                self::$forecast_settings = (object)[
                    'TEMP' => '',
                    'P' => '',
                    'CC' => '',
                    'G' => '',
                    'H' => '',
                    'V' => '',
                    'WD' => '',
                    'WS' => '',
                    'MP' => '',
                ];
            }
        }

        return self::$forecast_settings;
    }

    /**
     * @param $temp
     * @param $p
     * @param $cc
     * @param $g
     * @param $h
     * @param $v
     * @param $wd
     * @param $ws
     * @param $mp
     * @return void
     */
    public static function setForecastSettings($temp, $p, $cc, $g, $h, $v, $wd, $ws, $mp): void
    {
        $item = self::whereName('FORECAST_SETTINGS')->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'FORECAST_SETTINGS';
            $item->comm = '';
        }

        self::$forecast_settings = (object)[
            'TEMP' => $temp,
            'P' => $p,
            'CC' => $cc,
            'G' => $g,
            'H' => $h,
            'V' => $v,
            'WD' => $wd,
            'WS' => $ws,
            'MP' => $mp,
        ];

        $item->value = json_encode(self::$forecast_settings);
        $item->save();
    }

    /**
     * @var array|bool
     */
    private static array|bool $last_view_id = false;

    /**
     * @param string $page
     * @return string
     */
    public static function getLastViewID(string $page): string
    {
        if (self::$last_view_id === false) {
            $item = self::whereName('LAST_VIEW_ID')
                ->whereUserId(Auth::user()->id)
                ->first();
            if ($item && $item->value) {
                self::$last_view_id = json_decode($item->value, true);
            } else {
                self::$last_view_id = [];
            }
        }

        return isset(self::$last_view_id[$page]) ? self::$last_view_id[$page] : '';
    }

    /**
     * @param string $page
     * @param string|null $id
     * @return void
     */
    public static function setLastViewID(string $page, string|null $id): void
    {
        $userID = Auth::user()->id;

        $item = self::whereName('LAST_VIEW_ID')
            ->whereUserId($userID)
            ->first();
        if (!$item) {
            $item = new Property();
            $item->name = 'LAST_VIEW_ID';
            $item->comm = '';
            $item->user_id = $userID;
        }

        $data = $item->value ? json_decode($item->value, true) : [];
        $data[$page] = $id;
        self::$last_view_id = $data;
        $item->value = json_encode(self::$last_view_id);
        $item->save();
    }

    /**
     * @param string $name
     * @param bool $clear
     * @return string
     */
    public static function getProperty(string $name, bool $clear = false): string
    {
        $item = self::whereName($name)->first();
        if ($item) {
            $value = $item->value;
            if ($clear && $value != '') {
                $item->value = '';
                $item->save();
            }
            return $value;
        }
        return '';
    }

    /**
     * @param string $name
     * @param string $text
     * @param bool $append
     * @return void
     */
    public static function setProperty(string $name, string $text, bool $append = false): void
    {
        $item = self::whereName($name)->first();
        if (!$item) {
            $item = new Property();
            $item->name = $name;
            $item->comm = '';
        }
        if ($append) {
            $item->value .= $text;
        } else {
            $item->value = $text;
        }
        $item->save();
    }
}
