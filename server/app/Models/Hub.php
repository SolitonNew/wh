<?php

namespace App\Models;

use \App\Library\AffectsFirmwareModel;
use \Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Hub extends AffectsFirmwareModel
{
    protected $table = 'core_hubs';
    public $timestamps = false;

    /**
     * @var array|string[]
     */
    protected array $affectFirmwareFields = [
        'rom',
    ];

    public function extapiHosts()
    {
        return $this->hasMany(ExtApiHost::class);
    }

    public function camcorderHosts()
    {
        return $this->hasMany(CamcorderHost::class);
    }

    public function owHosts()
    {
        return $this->hasMany(OwHost::class);
    }

    public function i2cHosts()
    {
        return $this->hasMany(I2cHost::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * @return int
     */
    public function hostsCount(): int
    {
        switch ($this->typ) {
            case 'extapi':
                return $this->extapiHosts->count();
            case 'orangepi':
                return $this->i2cHosts->count();
            case 'camcorder':
                return $this->camcorderHosts->count();
            case 'din':
                return $this->owHosts->count();
        }
        return 0;
    }

    /**
     * @param int $id
     * @return Hub
     */
    public static function findOrCreate(int $id): Hub
    {
        $item = Hub::find($id);

        if (!$item) {
            $item = new Hub();
            $item->id = -1;
        }

        return $item;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function storeFromRequest(Request $request, int $id)
    {
        // Validation  ----------------------
        $rules = [];
        if ($request->typ == 'din' || $request->typ == 'pyhome') {
            $rules = [
                'name' => 'string|required',
                'typ' => 'string|required',
                'comm' => 'string|nullable',
                'rom' => [
                    'numeric',
                    'required',
                    'min:1',
                    'max:15',
                    Rule::unique('core_hubs')->where(function ($query) use ($id, $request) {
                        return $query
                            ->where('typ', $request->typ)
                            ->where('rom', $request->rom)
                            ->whereNot('id', $id);
                    }),
                ]
            ];
        } else {
            $rules = [
                'name' => 'string|required',
                'typ' => 'string|required',
                'comm' => 'string|nullable',
            ];
        }

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $item = Hub::find($id);

            if (!$item) {
                $item = new Hub();
            }
            $item->name = $request->name;
            $item->typ = $request->typ;
            if ($item->typ == 'din' || $item->typ == 'pyhome') {
                $item->rom = $request->rom;
            } else {
                $item->rom = null;
            }
            $item->comm = $request->comm;
            $item->save();

            // Store event
            EventMem::addEvent(EventMem::HUB_LIST_CHANGE, [
                'id' => $item->id,
                'typ' => $item->typ,
            ]);
            // ------------

            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function deleteById(int $id)
    {
        try {
            $item = Hub::find($id);
            if (!$item) abort(404);

            // Clear relations
            ExtApiHost::deleteByHubId($item->id);
            OwHost::deleteByHubId($item->id);
            I2cHost::deleteByHubId($item->id);
            CamcorderHost::deleteByHubId($item->id);

            foreach (Device::whereHubId($item->id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // -------------------------------------

            $item->delete();
            // Store event
            EventMem::addEvent(EventMem::HUB_LIST_CHANGE, [
                'id' => $item->id,
                'typ' => $item->typ,
            ]);
            // ------------

            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @var \string[][]
     */
    public static $typs = [
        'extapi' => [
            'variable',
            'extapi',
        ],
        'orangepi' => [
            'variable',
            'orangepi',
            'i2c',
        ],
        'camcorder' => [
            'variable',
            'camcorder',
        ],
        'din' => [
            'variable',
            'din',
            'ow',
        ],
        'pyhome' => [
            'variable',
            'din',
            'ow',
        ],
        'zigbeeone' => [
            'variable',
        ],
    ];

    /**
     * @var bool|null
     */
    private static bool|null $withNetworks = null;

    /**
     * @param int $hubID
     * @return bool|null
     */
    public static function withNetworks(int $hubID): bool|null
    {
        if (self::$withNetworks === null) {
            self::$withNetworks = false;

            $hub = Hub::find($hubID);
            if ($hub) {
                $hubsWithNetworks = [
                    'din',
                    'orangepi',
                ];

                self::$withNetworks = in_array($hub->typ, $hubsWithNetworks);
            }
        }
        return self::$withNetworks;
    }

    /**
     * @var bool|null
     */
    private static bool|null $existsFirmwareHubs = null;

    /**
     * Returns true if there are hubs with firmware.
     *
     * @return bool|null
     */
    public static function existsFirmwareHubs()
    {
        if (self::$existsFirmwareHubs === null) {
            self::$existsFirmwareHubs = (Hub::whereTyp('din')->count() > 0);
        }

        return self::$existsFirmwareHubs;
    }

    /**
     * @param string $typ
     * @return bool
     */
    public static function isFirstSingleHub(string $typ): bool
    {
        $single = ['orangepi', 'zigbeeone'];
        if (in_array($typ, $single)) {
            return (self::whereTyp($typ)->count() === 0);
        }
        return true;
    }
}
