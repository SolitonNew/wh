<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Lang;

class ScheduleController extends Controller
{
    /**
     * The index route for working with schedule entries.
     *
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function index()
    {
        $data = Schedule::listAll();

        return view('admin.schedule.schedule', [
            'data' => $data,
        ]);
    }

    /**
     * The route to create or update schedule entries.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Laravel\Lumen\Application
     */
    public function editShow(int $id)
    {
        $item = Schedule::findOrCreate($id);

        return view('admin.schedule.schedule-edit', [
            'item' => $item,
            'enableList' => Lang::get('admin/schedule.enable_list'),
            'interval' => Lang::get('admin/schedule.interval'),
        ]);
    }

    /**
     * The route to create or update schedule entries.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function editPost(Request $request, int $id)
    {
        return Schedule::storeFromRequest($request, $id);
    }

    /**
     * The route to delete schedule entries by id.
     *
     * @param int $id
     * @return string
     */
    public function delete(int $id): string
    {
        Schedule::deleteById($id);
        return 'OK';
    }
}
