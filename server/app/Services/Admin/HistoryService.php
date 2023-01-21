<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\DeviceChange;
use \Carbon\Carbon;

class HistoryService
{
    public const FILTER_DATE = 'STATISTICS-TABLE-DATE';
    public const FILTER_SQL = 'STATISTICS-TABLE-SQL';

    /**
     * @param Request $request
     * @return void
     */
    public function storeFilterDataFromRequest(Request $request): void
    {
        if ($request->method() == 'POST') {
            //
        }
    }

    /**
     * @param int|null $deviceID
     * @return array
     */
    public function getFilteringData(int $deviceID = null): array
    {
        $date = isset($_COOKIE[self::FILTER_DATE]) ? $_COOKIE[self::FILTER_DATE] : '';
        $sql = isset($_COOKIE[self::FILTER_SQL]) ? $_COOKIE[self::FILTER_SQL] : '';
        $errors = [];
        $data = [];
        $count = 0;
        $limit = config('settings.admin_history_lines_limit');

        if ($date) {
            $query = DeviceChange::whereDeviceId($deviceID);

            $d = Carbon::parse($date, \App\Models\Property::getTimezone())->startOfDay()->timezone('UTC');
            $query->whereBetween('created_at', [$d, $d->copy()->addDay()]);

            if ($sql) {
                $query->whereRaw('value '.$sql);
            }

            try {
                $data = $query->orderBy('id', 'asc')
                    ->limit($limit)
                    ->get();
                $count = $query->count();
            } catch (\Exception $ex) {
                $errors['sql'] = $ex->getMessage();
                $data = [];
            }
        }

        return [
            $data,
            $count,
            $limit,
            $errors
        ];
    }

    /**
     * @param int $deviceID
     * @return int
     */
    public function deleteAllVisibleValues(int $deviceID): int
    {
        try {
            $date = isset($_COOKIE[self::FILTER_DATE]) ? $_COOKIE[self::FILTER_DATE] : '';
            $sql = isset($_COOKIE[self::FILTER_SQL]) ? $_COOKIE[self::FILTER_SQL] : '';

            if (!$date) {
                throw new \Exception('Field date is required');
            }

            $d = Carbon::parse($date)->startOfDay();
            $query = DeviceChange::whereDeviceId($deviceID)
                        ->whereBetween('created_at', [$d, $d->copy()->addDay()]);
            if ($sql) {
                $query->whereRaw('value '.$sql);
            }

            return $query->delete();
        } catch (\Exception $ex) {
            abort(response()->json([
                'errors' => [$ex->getMessage()],
            ]), 422);
        }

        return 0;
    }
}
