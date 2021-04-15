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
        
        // Заполняем справочник core_ow_types
        $data = [
            '40|DS18B20|TEMP|1',
            '240|Выключатель 2 кнопки|LEFT,RIGHT|100',
            '241|ШИМ контроллер|F1,F2,F3,F4|100',
            '242|Пин-конвертор|P1,P2,P3,P4|100',
            '243|Гигрометр|H,T|100',
            '244|Датчик CO|CO|0',
            '245|Датчик тока|AMP|0',
            '246|Реле|R1,R2,R3,R4|100',
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
        
        // Заполняем core_propertys
        $data = [
            '1|SYNC_STATE|Состояние синхронизации сревера и контроллеров: Запущен/Остановлен|STOP',
            '2|RS485_COMMAND|Команда, адресуемая демону RS485|',
            '3|RS485_COMMAND_INFO|Текст, поочередно меняющийся инициализатором или исполнителем команды.|',
            '4|FIRMWARE_CHANGES|Количество изменений внесенных в БД (которые влияют на прошивку) с момента последнего успешного обновления|',
            '5|WEB_CHECKED|ИДшники для веб версии клиента|',
            '6|WEB_COLOR|Раскраска по ключевым словам|',
            '7|RUNNING_DEMONS|Список отмеченых для автоматического запуска демонов|schedule-demon;command-demon',
            '8|PLAN_MAX_LEVEL|Глубина структуры системы|',
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
    }
}
