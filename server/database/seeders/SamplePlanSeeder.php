<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SamplePlanSeeder extends Seeder
{
    public function run()
    {
        // Filling out of the plan_rooms table
        $data = file_get_contents(base_path().'/database/seeders/sample.plan.json');
        App\Models\Room::importFromString($data);
    }
}
