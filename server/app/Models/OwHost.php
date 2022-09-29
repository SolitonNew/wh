<?php

namespace App\Models;

use App\Library\AffectsFirmwareModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class OwHost extends AffectsFirmwareModel
{
    protected $table = 'core_ow_hosts';
    public $timestamps = false;

    protected array $affectFirmwareFields = [
        'id',
    ];

    public function hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id');
    }

    /**
     * @return Relation
     */
    public function devices(): Relation
    {
        return $this->hasMany(Device::class, 'host_id')
                    ->whereTyp('ow')
                    ->orderBy('name', 'asc');
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
            $types = config('onewire.types');
            $type = isset($types[$this->rom_1]) ? $types[$this->rom_1] : [];

            if (!isset($type['description'])) {
                $type['description'] = '';
            }

            if (!isset($type['channels'])) {
                $type['channels'] = [];
            }

            if (!isset($type['consuming'])) {
                $type['consuming'] = 0;
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
     * @return string
     */
    public function romAsString(): string
    {
        return sprintf("x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X x%'02X",
            $this->rom_1,
            $this->rom_2,
            $this->rom_3,
            $this->rom_4,
            $this->rom_5,
            $this->rom_6,
            $this->rom_7
        );
    }

    /**
     * @param int $hubID
     * @return Collection
     */
    public static function listForIndex(int $hubID): Collection
    {
        return OwHost::whereHubId($hubID)
            ->orderBy('rom_1', 'asc')
            ->orderBy('rom_2', 'asc')
            ->orderBy('rom_3', 'asc')
            ->orderBy('rom_4', 'asc')
            ->orderBy('rom_5', 'asc')
            ->orderBy('rom_6', 'asc')
            ->orderBy('rom_7', 'asc')
            ->get();
    }

    /**
     * @param int $hubID
     * @param int $id
     * @return OwHost
     */
    public static function findOrCreate(int $hubID, int $id): OwHost
    {
        $item = self::whereHubId($hubID)
            ->whereId($id)
            ->first();

        if (!$item) {
            $item = new OwHost();
            $item->id = $id;
            $item->hub_id = $hubID;
        }

        return $item;
    }

    /**
     * @param Request $request
     * @param int $hubID
     * @param int $id
     * @return void
     */
    public static function storeFromRequest(Request $request, int $hubID, int $id)
    {

    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function deleteById(int $id)
    {
        try {
            // Clear relations
            foreach (Device::whereTyp('ow')->whereHostId($id)->get() as $device) {
                Device::deleteById($device->id);
            }
            // -------------------------

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
    public static function deleteByHubId(int $hubID)
    {
        $result = 'OK';
        foreach (self::whereHubId($hubID)->get() as $host) {
            if (self::deleteById($host->id) != 'OK') {
                $result = 'With Errors';
            }
        }
        return $result;
    }
}
