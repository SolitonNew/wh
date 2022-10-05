<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isNull;

class BackupMetaService
{
    private const BACKUP_META_TABLES = [
        'core_devices',
        'core_device_events',
        'core_extapi_hosts',
        'core_hubs',
        'core_i2c_hosts',
        'core_ow_hosts',
        'core_properties',
        'plan_rooms',
        'core_schedule',
        'core_scripts',
        //'web_users'
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function importFromRequest(Request $request)
    {
        // Validation  ----------------------
        $rules = [
            'file' => 'file|required',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Saving -----------------------
        try {
            $data = file_get_contents($request->file('file'));
            return $this->importFromString($data);
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function importFromString(string $data): string
    {
        $json = json_decode($data, true);

        return DB::transaction(function () use ($json) {
            $allTables = DB::select("show tables");
            foreach ($allTables as $table) {
                foreach ($table as $key => $val) {
                    if ($val === 'web_users') continue;

                    foreach (['core_', 'web_', 'media_', 'plan_'] as $prefix) {
                        if (str_starts_with($val, $prefix)) {
                            DB::delete('delete from '.$val);
                        }
                    }
                }
            }

            foreach (self::BACKUP_META_TABLES as $table) {
                foreach ($json as $tableData) {
                    if ($tableData['table'] == $table) {
                        foreach ($tableData['data'] as $row) {
                            $fields = [];
                            $values = [];
                            foreach ($row as $name => $value) {
                                $fields[] = $name;
                                if (is_null($value)) {
                                    $values[] = 'NULL';
                                } elseif (is_float($value)) {
                                    $values[] = $value;
                                } else {
                                    $values[] = "'".$value."'";
                                }
                            }

                            $sql = 'insert into '.$table.' ('.implode(', ', $fields).')'.
                                ' values ('.implode(', ', $values).')';

                            DB::insert($sql);
                        }
                    }
                }
            }

            return 'OK';
        });
    }

    /**
     * @return false|string
     */
    public function exportToString(): false|string
    {
        $result = [];
        foreach (self::BACKUP_META_TABLES as $table) {
            $data = DB::select('select * from '.$table);

            foreach ($data as $row) {
                foreach ($row as $val) {
                    if (isNull($val)) {
                        $val = 'NULL';
                    }
                }
            }

            $result[] = (object)[
                'table' => $table,
                'data' => $data,
            ];
        }
        return json_encode($result);
    }
}
