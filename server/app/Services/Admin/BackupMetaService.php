<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;

class BackupMetaService
{
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
            $this->importFromString($data);
            return 'OK';
        } catch (\Exception $ex) {
            return response()->json([
                'errors' => [$ex->getMessage()],
            ]);
        }
    }

    /**
     * @param string $data
     * @return void
     */
    public function importFromString(string $data): void
    {

    }

    /**
     * @return string
     */
    public function exportToString()
    {
        return 'OK';
    }
}
