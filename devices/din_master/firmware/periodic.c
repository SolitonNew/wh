/*
 *  Author: Moklyak Alexandr
 */ 

#include "board.h"
#include "periodic.h"
#include "config/devs.h"

int periodic_variable_index = -1;
int periodic_step = 0;
uint8_t periodic_measure_start = 0;  // 0-ищем ow переменную; 1-запускаем измерени; 2-сохраняем данные;

/**
 * Поочередные запросы для периодической работы с периферией
 * В частности опрос датчиков ds18b20
 */
void periodic_processing(void) {
    if (periodic_step++ > PERIODIC_STEP_MAX) {
        periodic_step = 0;
       
        uint8_t rom[8] = {0, 0, 0, 0, 0, 0, 0, 0};
		
        if (periodic_measure_start == 0) {
            uint8_t find = 0;
            // Ищем следующую после schedule_ow_index OW переменную с rom_1 = 0x28 и нашего контрллера
            for (int i = periodic_variable_index + 1; i < VARIABLE_COUNT; i++) {
                if (devs_get_variable_controller(i) == controller_id) {
                    devs_get_variable_rom(i, rom);
                    if (rom[0] == 0x28) {
                        periodic_variable_index = i;
                        find = 1;
                    }
                }
            }
            // Если за первый проход не нашли, начниаем с начала списка
            if (!find) {
                for (int i = 0; i <= periodic_variable_index; i++) {
                    if (devs_get_variable_controller(i) == controller_id) {
                        devs_get_variable_rom(i, rom);
                        if (rom[0] == 0x28) {
                            periodic_variable_index = i;
                            find = 1;
                        }
                    }
                }    
            }
            
            if (find) {
                periodic_measure_start = 1;
            }
        } else {
            devs_get_variable_rom(periodic_variable_index, rom);
        }
        
        switch (rom[0]) {
            case 0x28:
                if (periodic_measure_start == 1) {
                    ds18b20_start_measure(rom);
                    periodic_measure_start = 2;
                } else
                if (periodic_measure_start == 2) {
                    core_request_ow_values(rom);
                    periodic_measure_start = 0;
                }                                        
                break;
        }            
    }
}