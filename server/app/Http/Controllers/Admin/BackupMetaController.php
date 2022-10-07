<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\BackupMetaService;
use Illuminate\Http\Request;

class BackupMetaController extends \App\Http\Controllers\Controller
{
    /**
     * @var BackupMetaService
     */
    private BackupMetaService $service;

    public function __construct(BackupMetaService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function importShow()
    {
        return view('admin.settings.backup-meta-import');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function importPost(Request $request)
    {
        return $this->service->importFromRequest($request);
    }

    /**
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function export()
    {
        $data = $this->service->exportToString();

        return response($data, 200, [
            'Content-Length' => strlen($data),
            'Content-Disposition' => 'attachment; filename="'.\Carbon\Carbon::now()->format('Ymd_His').'_meta.json"',
            'Pragma' => 'public',
        ]);
    }
}
