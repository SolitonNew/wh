<?php

namespace App\Services\Admin;

use App\Models\Room;
use App\Models\Property;

class SettingsService
{
    /**
     * @return string[]
     */
    public function levels(): array
    {
        $levels = [
            1 => '',
            2 => '',
            3 => '',
        ];

        $parts = Room::generateTree();

        for ($i = 0; $i < 3; $i++) {
            for ($k = count($parts) - 1; $k >= 0; $k--) {
                if ($parts[$k]->level === $i) {
                    $levels[$i + 1] = (isset($levels[$i]) ? $levels[$i].' - ' : '').$parts[$k]->name;
                    break;
                }
            }
        }

        return $levels;
    }

    /**
     * @return int
     */
    public function getCurrentLevel(): int
    {
        return Property::getPlanMaxLevel();
    }

    /**
     * @param int $level
     * @return void
     */
    public function setCurrentLevel(int $level): void
    {
        try {
            Property::setPlanMaxLevel($level);
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }
    }

    /**
     * @return false|string|null
     */
    public function checkUpdates()
    {
        return shell_exec('git pull');
    }
}
