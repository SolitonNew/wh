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
        // Создаем админа по умолчанию
        $item = new \App\Http\Models\UsersModel();
        $item->login = 'wh';
        $item->password = bcrypt('wh');
        $item->access = 2;
        $item->save();
        
        // Filling out of the core_ow_types table
        $data = [
            '40|DS18B20|Termometr|1',
            '240|Two buttons switch|LEFT,RIGHT|100',
            '241|Venting|F1,F2,F3,F4|100',
            '242|Pin converter|P1,P2,P3,P4|100',
            '243|Humidity sensor|H,T|100',
            '244|Gas sensor|CO|0',
            '245|Currency sensor|AMP|0',
            '246|Relay|R1,R2,R3,R4|100',
        ];
        
        foreach($data as $row) {
            $attrs = explode('|', $row);
            \App\Http\Models\OwTypesModel::create([
                'code' => $attrs[0],
                'comm' => $attrs[1],
                'channels' => $attrs[2],
                'consuming' => $attrs[3],
            ])->save();
        }
        
        // Filling out of the core_propertys table
        $data = [
            '1|SYNC_STATE|Synchronization state of the server and controllers: Running/Stoped|STOP',
            '2|RS485_COMMAND|Command addressed to the RS485-demon|',
            '3|RS485_COMMAND_INFO|Text that is alternately changed by the initializer or executor of the command|',
            '4|FIRMWARE_CHANGES|The number of changes made to the database (which affect the firmware) since the last successful update|',
            '5|WEB_CHECKED|IDs for the web version of the client|',
            '6|WEB_COLOR|Coloring by keywords|',
            '7|RUNNING_DEMONS|List of daemons marked for automatic start|schedule-demon;command-demon',
            '8|PLAN_MAX_LEVEL|System structure depth|',
        ];

        foreach($data as $row) {
            $attrs = explode('|', $row);
            \App\Http\Models\PropertysModel::create([
                'id' => $attrs[0],
                'name' => $attrs[1],
                'comm' => $attrs[2],
                'value' => $attrs[3],
            ])->save();
        }
        
        // Filling out of the plan_parts table
        $data = file_get_contents(base_path().'/database/seeds/sample.plan.json');
        App\Http\Models\PlanPartsModel::importFromString($data);
        
    }
}
