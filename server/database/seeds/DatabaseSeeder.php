<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create default admin record
        $item = new \App\Models\User();
        $item->login = 'wh';
        $item->password = bcrypt('wh');
        $item->access = 2;
        $item->save();
        
        // Filling out of the core_properties table
        $data = [
            '1|SYNC_STATE|Synchronization state of the server and controllers: Running/Stoped|STOP',
            '2|DIN_COMMAND|Command addressed to the din-daemon|',
            '3|DIN_COMMAND_INFO|Text that is alternately changed by the initializer or executor of the command|',
            '4|FIRMWARE_CHANGES|The number of changes made to the database (which affect the firmware) since the last successful update|',
            '5|WEB_CHECKED|IDs for the web version of the client|',
            '6|WEB_COLOR|Coloring by keywords|',
            '7|RUNNING_DAEMONS|List of daemons marked for automatic start|schedule-daemon;command-daemon;observer-daemon',
            '8|PLAN_MAX_LEVEL|System structure depth|',
        ];

        foreach($data as $row) {
            $attrs = explode('|', $row);
            \App\Models\Property::create([
                'id' => $attrs[0],
                'name' => $attrs[1],
                'comm' => $attrs[2],
                'value' => $attrs[3],
            ])->save();
        }
        
        // Filling out of the plan_rooms table
        $data = file_get_contents(base_path().'/database/seeds/sample.plan.json');
        App\Models\Room::importFromString($data);
        
    }
}
