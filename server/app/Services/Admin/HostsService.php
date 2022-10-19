<?php

namespace App\Services\Admin;

use App\Models\CamcorderHost;
use App\Models\ExtApiHost;
use App\Models\Hub;
use App\Models\I2cHost;
use App\Models\OwHost;
use Illuminate\Support\Facades\Lang;

class HostsService
{
    /**
     * @param int $hubID
     * @return string
     */
    public function getHostType(int $hubID): string
    {
        return Hub::findOrCreate($hubID)->typ;
    }

    /**
     * @param int $hubID
     * @param string $group
     * @return array
     */
    public function getIndexList(int $hubID, string $group = ''): array
    {
        $data = [];

        $ow = $this->getOwHostsForIndex($hubID, $data);
        $i2c = $this->getI2cHostsForIndex($hubID, $data);
        $extApi = $this->getExtApiHostsForIndex($hubID, $data);
        $camcorder = $this->getCamcorderHostsForIndex($hubID, $data);

        return $data;
    }

    /**
     * @param int $hubID
     * @param array $data
     * @return void
     */
    private function getOwHostsForIndex(int $hubID, array &$data): void
    {
        $list = OwHost::whereHubId($hubID)
            ->get();

        foreach ($list as $row) {
            $data[] = (object)[
                'id' => $row->id,
                'group' => 'ow',
                'typName' => 'ow - '.$row->type()->description,
                'address' => $row->romAsString(),
                'comm' => $row->comm,
                'channels' => implode(', ', $row->type()->channels),
                'devices' => $row->devices,
                'lost' => $row->lost,
            ];
        }
    }

    /**
     * @param int $hubID
     * @param array $data
     * @return void
     */
    private function getI2cHostsForIndex(int $hubID, array &$data): void
    {
        $list = I2cHost::whereHubId($hubID)
            ->get();

        foreach ($list as $row) {
            $data[] = (object)[
                'id' => $row->id,
                'group' => 'i2c',
                'typName' => 'i2c - '.$row->type()->description,
                'address' => 'x'.dechex($row->address),
                'comm' => $row->comm,
                'channels' => implode(', ', $row->type()->channels),
                'devices' => $row->devices,
                'lost' => $row->lost,
            ];
        }
    }

    /**
     * @param int $hubID
     * @param array $data
     * @return void
     */
    private function getExtApiHostsForIndex(int $hubID, array &$data): void
    {
        $list = ExtApiHost::whereHubId($hubID)
            ->get();

        foreach ($list as $row) {
            $data[] = (object)[
                'id' => $row->id,
                'group' => 'extapi',
                'typName' => $row->type()->title,
                'address' => '-//-',
                'comm' => $row->comm,
                'channels' => implode(', ', $row->type()->channels),
                'devices' => $row->devices,
                'lost' => false,
            ];
        }
    }

    /**
     * @param int $hubID
     * @param array $data
     * @return void
     */
    private function getCamcorderHostsForIndex(int $hubID, array &$data): void
    {
        $list = CamcorderHost::whereHubId($hubID)
            ->get();

        foreach ($list as $row) {
            $data[] = (object)[
                'id' => $row->id,
                'group' => 'camcorder',
                'typName' => $row->type()->title.' ['.$row->name.']',
                'address' => '-//-',
                'comm' => $row->comm,
                'channels' => implode(', ', $row->type()->channels),
                'devices' => $row->devices,
                'lost' => false,
            ];
        }
    }

    /**
     * @param int $hubID
     * @return array
     */
    public function getHostTypList(int $hubID): array
    {
        $hub = Hub::find($hubID);
        $result = [];
        foreach ($hub->getHostTypeList() as $typ) {
            switch ($typ) {
                case 'ow':
                    foreach (config('onewire.types') as $rom1 => $ow) {
                        $result[] = (object)[
                            'title' => 'ow - '.$ow['description'],
                            'description' => $ow['description'],
                            'group' => 'ow',
                            'hostTypID' => $rom1,
                            'channels' => implode('; ', $ow['channels']),
                            'data' => '',
                        ];
                    }
                    break;
                case 'i2c':
                    foreach (config('i2c.types') as $name => $detail) {
                        $result[] = (object)[
                            'title' => 'i2c - '.$name,
                            'description' => $detail['description'],
                            'group' => 'i2c',
                            'hostTypID' => $name,
                            'channels' => implode('; ', $detail['channels']),
                            'data' => json_encode($detail['address']),
                        ];
                    }
                    break;
                case 'extapi':
                    foreach (config('extapi.drivers') as $class) {
                        $driver = new $class();
                        $properties = [];
                        foreach ($driver->properties as $key => $size) {
                            $properties[] = (object)[
                                'title' => Lang::get('admin/extapihosts/'.$driver->name.'.'.$key),
                                'key' => $key,
                                'size' => $size,
                            ];
                        }
                        $result[] = (object)[
                            'title' => Lang::get('admin/extapihosts/'.$driver->name.'.title'),
                            'description' => Lang::get('admin/extapihosts/'.$driver->name.'.description'),
                            'group' => 'extapi',
                            'hostTypID' => $driver->name,
                            'channels' => implode('; ', $driver->channels),
                            'data' => json_encode($properties),
                        ];
                    }
                    break;
                case 'camcorder':
                    foreach (config('camcorder.drivers') as $class) {
                        $driver = new $class();
                        $properties = [];
                        foreach ($driver->properties as $key => $size) {
                            $properties[] = (object)[
                                'title' => Lang::get('admin/camcorderhosts/'.$driver->name.'.'.$key),
                                'key' => $key,
                                'size' => $size,
                            ];
                        }
                        $result[] = (object)[
                            'title' => Lang::get('admin/camcorderhosts/'.$driver->name.'.title'),
                            'description' => Lang::get('admin/camcorderhosts/'.$driver->name.'.description'),
                            'group' => 'camcorder',
                            'hostTypID' => $driver->name,
                            'channels' => implode('; ', $driver->channels),
                            'data' => json_encode($properties),
                        ];
                    }
                    break;
            }
        }

        return $result;
    }
}
