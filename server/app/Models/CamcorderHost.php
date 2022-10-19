<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CamcorderHost extends AffectsFirmwareModel
{
    protected $table = 'core_camcorder_hosts';
    public $timestamps = false;

    /**
     *
     * @return type
     */
    public function hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id');
    }

    /**
     *
     * @return type
     */
    public function devices()
    {
        return $this->hasMany(Device::class, 'host_id')
                    ->whereTyp('camcorder')
                    ->orderBy('name', 'asc');
    }

    /**
     * @var object|bool
     */
    private object|bool $driver = false;

    /**
     * @return object|bool
     */
    public function driver(): object|bool
    {
        if ($this->driver === false) {
            foreach (config('camcorder.drivers') as $class) {
                $instance = new $class();
                if ($instance->name == $this->typ) {
                    $instance->assignKey($this->id);
                    $instance->assignCaption($this->name);
                    $instance->assignData($this->data);
                    $this->driver = $instance;
                    break;
                }
            }
        }

        return $this->driver;
    }

    /**
     * @var object|null
     */
    public object|null $type = null;

    /**
     * @return object|null
     */
    public function type(): object|null
    {
        if ($this->type === null) {
            if ($this->driver()) {
                $type = [
                    'title' => $this->driver()->title,
                    'description' => $this->driver()->description,
                    'channels' => $this->driver()->channels,
                    'consuming' => 0,
                    'properties' => $this->driver()->propertiesWithTitles(),
                ];
            } else {
                $type = [
                    'title' => '',
                    'description' => '',
                    'channels' => [],
                    'consuming' => 0,
                    'properties' => [],
                ];
            }

            $this->type = (object)$type;
        }

        return $this->type;
    }

    /**
     * @return array
     */
    public function channelsOfType(): array
    {
        if ($this->type()) {
            return $this->type()->channels;
        }

        return [];
    }

    /**
     * @return array
     */
    public function typeList(): array
    {
        $result = [];
        foreach (config('camcorder.drivers') as $class) {
            $result[] = new $class();
        }
        return $result;
    }

    /**
     * @param int $hubID
     * @return Collection
     */
    public static function listForIndex(int $hubID): Collection
    {
        return self::whereHubId($hubID)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * @param int $hubID
     * @param int $id
     * @return CamcorderHost
     */
    public static function findOrCreate(int $hubID, int $id): CamcorderHost
    {
        $item = self::whereHubId($hubID)
            ->whereId($id)
            ->first();

        if (!$item) {
            $item = new CamcorderHost();
            $item->id = $id;
            $item->hub_id = $hubID;
        }

        return $item;
    }

    /**
     * @param Request $request
     * @param int $hubID
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function storeFromRequest(Request $request, int $hubID, int $id)
    {
        // Validation  ----------------------
        $rules = [
            'typ' => ($id == -1) ? 'required' : 'nullable',
            'name' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $item = self::find($id);
            if (!$item) {
                $item = new CamcorderHost();
                $item->hub_id = $hubID;
                $item->typ = $request->typ;
            }

            $item->name = $request->name;

            // Store properties data
            $propertiesData = [];
            $properties = $item->type()->properties;
            $i = 0;
            foreach ($properties as $key => $val) {
                $propertiesData[$key] = $request->get('camcorder_'.$key);
            }
            $item->data = json_encode($propertiesData);
            // ---------------------

            $item->save();

            // Store event
            EventMem::addEvent(EventMem::HOST_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
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
            // Clear relations
            foreach (Device::whereTyp('camcorder')->whereHostId($id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // ------------------------

            $item = self::find($id);
            $item->delete();

            // Store event
            EventMem::addEvent(EventMem::HOST_LIST_CHANGE, [
                'id' => $item->id,
                'hubID' => $item->hub_id,
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
     * @param int $hubID
     * @return string
     */
    public static function deleteByHubId(int $hubID): string
    {
        $result = 'OK';
        foreach (self::whereHubId($hubID)->get() as $host) {
            if (self::deleteById($host->id) != 'OK') {
                $result = 'With Errors';
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getThumbnailFileName(): string
    {
        return base_path('storage/app/camcorder/thumbnails/'.$this->id.'.jpg');
    }

    /**
     * @param string $apiToken
     * @return string
     */
    public function getThumbnailUrl(string $apiToken): string
    {
        if (file_exists($this->getThumbnailFileName())) {
            return route('cam-thumbnail', ['id' => $this->id, 'api_token' => $apiToken]);
        } else {
            return '';
        }
    }

    /**
     * @param string $host
     * @return string
     */
    public function getVideoUrl(string $host): string
    {
        return 'http://'.$host.':'.(10000 + $this->id);
    }
}
