<?php

namespace App\Services\Api;

use App\Models\Room;
use App\Models\Device;
use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Lang;

class RoomsService
{
    /**
     * @var mixed|array
     */
    private mixed $groups = [];

    /**
     * @var mixed|array
     */
    private mixed $variables = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        $this->groups = Room::orderBy('order_num', 'asc')
                            ->orderBy('name', 'asc')
                            ->get();

        $this->variables = Device::get();

        foreach ($this->variables as $var) {
            if (!$var->comm) {
                foreach ($this->groups as $g) {
                    if ($g->id == $var->room_id) {
                        $var->comm = $g->name;
                        break;
                    }
                }
            }
        }

        $data = [];
        switch (Property::getPlanMaxLevel()) {
            case 1:
                $data[] = (object)[
                    'title' => '',
                ];
                $this->makeItems(null, 2, $data);
                break;
            case 2:
                $this->makeItems(null, 1, $data);
                break;
            default:
                $this->makeItems(null, 0, $data);
        }

        return $data;
    }

    /**
     * This method to creates data to display a list of rooms.
     *
     * @param int|null $parentID
     * @param int $level
     * @param $data
     * @return void
     */
    private function makeItems(int|null $parentID, int $level, &$data): void
    {
        $switches_2 = [];
        foreach (Lang::get('terminal.switches_second') as $s) {
            $switches_2[] = ' '.$s;
        }

        for ($i = 0; $i < count($this->groups); $i++) {
            $row = $this->groups[$i];
            if ($row->parent_id == $parentID) {
                switch ($level) {
                    case 0:
                        break;
                    case 1:
                        $parentTitle = '';
                        foreach ($this->groups as $p_g) {
                            if ($p_g->id == $row->parent_id) {
                                $parentTitle = mb_strtoupper($p_g->name);
                                break;
                            }
                        }

                        $itemTitle = mb_strtoupper($row->name);
                        if ($parentTitle != '' && $parentTitle != $itemTitle) {
                            $itemTitle = $parentTitle.' '.$itemTitle;
                        }

                        $data[] = (object)[
                            'title' => $itemTitle,
                            'rooms' => [],
                        ];
                        break;
                    case 2:
                        $room = $data[count($data) - 1];
                        $titleUpper = mb_strtoupper($row->name);
                        $vars = $this->findVariable($row->id, $titleUpper);

                        $temperature = null;
                        $switch_1 = null;
                        $switch_2 = null;

                        foreach ($vars as $v) {
                            switch ($v['app_control']) {
                                case 4:
                                    if (mb_strtoupper($v['comm']) == $titleUpper) {
                                        $temperature = (object)[
                                            'id' => $v['id'],
                                            'value' => $v['value']
                                        ];
                                    }
                                    break;
                                case 1:
                                    if (mb_strtoupper($v['comm']) == $titleUpper) {
                                        $switch_1 = (object)[
                                            'id' => $v['id'],
                                            'value' => $v['value']
                                        ];
                                    } else {
                                        for ($n = 0; $n < count($switches_2); $n++) {
                                            if (mb_strtoupper($v['comm']) == $titleUpper.$switches_2[$n]) {
                                                $switch_2 = (object)[
                                                    'id' => $v['id'],
                                                    'value' => $v['value']
                                                ];
                                                break;
                                            }
                                        }
                                    }
                                    break;
                            }
                        }

                        $room->rooms[] = (object)[
                            'id' => $row->id,
                            'title' => mb_strtoupper($row->name),
                            'titleCrop' => str_replace($room->title, '', $titleUpper),
                            'temperature' => $temperature,
                            'switch_1' => $switch_1,
                            'switch_2' => $switch_2,
                        ];

                        break;
                }
                $this->makeItems($row->id, $level + 1, $data);
            }
        }
    }

    /**
     * This method of finding a device in the device storage by id and room name.
     *
     * @param int|null $roomID
     * @param string $roomNameUpper
     * @return array
     */
    private function findVariable(int|null $roomID, string $roomNameUpper): array
    {
        $res = [];
        for ($i = 0; $i < count($this->variables); $i++) {
            $var = $this->variables[$i];
            if ($var->room_id == $roomID) {
                if (mb_strtoupper(mb_substr($var->comm, 0, mb_strlen($roomNameUpper))) == $roomNameUpper) {
                    $res[] = $var;
                }
            }
        }
        return $res;
    }
}
