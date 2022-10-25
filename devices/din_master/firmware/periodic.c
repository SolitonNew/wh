/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#include "board.h"
#include "periodic.h"
#include "core.h"
#include "config/devs.h"
#include "drivers/ds18b20.h"

int periodic_variable_index = -1;
int periodic_step = 0;
uint8_t periodic_measure_start = 0;  // 0-���� ow ����������; 1-��������� ��������; 2-��������� ������;

/**
 * Sequential requests for periodic work with peripherals
 * In particular, polling sensors ds18b20
 */
void periodic_processing(void) {
    if (periodic_step++ > PERIODIC_STEP_MAX) {
        periodic_step = 0;
       
        uint8_t rom[8] = {0, 0, 0, 0, 0, 0, 0, 0};
		
        if (periodic_measure_start == 0) {
            uint8_t find = 0;
            // We are looking for the next variable after schedule_ow_index OW with rom_1 = 0x28 and our controller
            for (int i = periodic_variable_index + 1; i < VARIABLE_COUNT; i++) {
                if (devs_get_variable_controller(i) == controller_id) {
                    devs_get_variable_rom(i, rom);
                    if (rom[0] == 0x28) {
                        periodic_variable_index = i;
                        find = 1;
                    }
                }
            }
            // If the first pass is not found, start from the beginning of the list
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
