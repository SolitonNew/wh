/*
 *  Author: Moklyak Alexandr
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/pgmspace.h>
#include <avr/interrupt.h>
#include <math.h>
#include "core.h"
#include "rs485.h"
#include "onewire.h"
#include "din.h"
#include "config/devs.h"
#include "config/scripts.h"
#include "drivers/ds18b20.h"
#include "drivers/hs.h"
#include "drivers/dht11.h"
#include "drivers/mq7.h"
#include "drivers/pc.h"
#include "drivers/fc.h"

int variable_values[VARIABLE_COUNT];

int core_variable_changed[CORE_VARIABLE_CHANGED_COUNT_MAX];
uint8_t core_variable_changed_count = 0;

/**
 * Инициализация ядра
 */
void core_init(void) {
    din_init();
    rs485_init();
    onewire_init();
}

/**
 * Обработка alarm событий на шине OW
 */
void core_onewire_alarm_processing(void) {
    if (onewire_alarms()) {
        uint8_t* ind = (uint8_t*)&onewire_roms_buff[0];
        for (uint8_t i = 0; i < onewire_roms_buff_count; i++) {
            core_request_ow_values(ind);
            ind += 8;
        }
    }
}

/**
 * Возвращает значение переменной конверьированое в float
 * Все действия с переменными проходят через этот механизм.
 *
 * Не используйте доступ к значениям переменных напрямую.
 *
 * Возвращает реальное значение переменной
 */
float core_get_variable_value(int index) {
    if ((index < 0) || (index >= VARIABLE_COUNT)) return 0;
    return (float)variable_values[index] / 10;
}

/**
 * Главная функция обработки назначения значения переменной.
 * Все действия с переменными проходят через этот механизм.
 *
 * Не используйте доступ к значениям переменных напрямую.
 *
 * target: 0-server init, 1-server, 2-devs, 3-script
 */
void core_set_variable_value_int(int index, uint8_t target, int value) {
    if ((index < 0) || (index >= VARIABLE_COUNT)) return ;
    if (variable_values[index] == value) return ;
    
    variable_values[index] = value;
    
    variable_t variable;
    devs_get_varible(index, &variable);
	
    if (variable.controller_id == controller_id) { // Это переменная этого контроллера
        // Выполняем пересылку новых состояний для devs
        switch(variable.typ) {
            case 0: // din
                din_set_value(variable.channel, value / 10);
                break;
            case 1: // ow
                core_transmit_ow_values(variable.ow_index);
                break;
            case 2: // variable;
                // not records
                break;
        }
        
        // Пишем в лог изменений для отправки на сервер
        uint8_t exists = 0;
        switch (target) {
            case 0: // server init
            case 1: // server
                break;
            case 2: // devs
            case 3: // script
                for (uint8_t i = 0; i < core_variable_changed_count; i++) {
                    if (core_variable_changed[i] == variable.id) {
                        exists = 1;
                        break;
                    }
                }
                if (!exists && core_variable_changed_count < CORE_VARIABLE_CHANGED_COUNT_MAX) {
                    core_variable_changed[core_variable_changed_count++] = variable.id;
                }                    
                break;
        }
        
        // Запрашиваем выполнение скрипта по событию изменения
        switch (target) {
            case 0: // server init
                break;
            case 1: // server
            case 2: // devs
            case 3: // script
                script_run_event_for_variable(index);
                break;
        }		
    }	
}

/**
 * Главная функция обработки назначения значения переменной (реальное значение).
 * Все действия с переменными проходят через этот механизм.
 *
 * Не используйте доступ к значениям переменных напрямую.
 *
 * target: 0-server init, 1-server, 2-devs, 3-script
 */
void core_set_variable_value(int index, uint8_t target, float value) {
    core_set_variable_value_int(index, target, ceil(value * 10));
}

/**
 * Собирает данные всех переменных с ow_index и отправляет в устройство
 */
void core_transmit_ow_values(int ow_index) {
    variable_t variable;
    fc_data_t fc_data;
	
    uint8_t rom[8];
    devs_get_ow_rom(ow_index, rom);
    int vars[8];
    uint8_t vars_num = devs_find_variables_by_ow_index(ow_index, vars);
	
    switch (rom[0]) {
        case DS18B20_CODE: // ds18b20
            // readonly
            break;
        case HS_CODE: // hs
            // readonly
            break;
        case FC_CODE: // fc			
            for (uint8_t i = 0; i < vars_num; i++) {
                if (devs_get_varible(vars[i], &variable)) {
                    switch (variable.channel) {
                        case 0: // f1
                            fc_data.f1 = core_get_variable_value(vars[i]);
                            break;
                        case 1: // f2
                            fc_data.f2 = core_get_variable_value(vars[i]);
                            break;
                        case 2: // f3
                            fc_data.f3 = core_get_variable_value(vars[i]);
                            break;
                        case 3: // f4
                            fc_data.f4 = core_get_variable_value(vars[i]);
                            break;
                        default: ;
                    }
                }
            }
            fc_set_data(rom, &fc_data);
            break;
        case PC_CODE: // pc
            // readonly
            break;
        case DHT11_CODE: // dht11
            // readonly
            break;
        case MQ7_CODE: // mq7
            // readonly
            break;
        case 0xf5: // ampermetr
            // readonly
            break;
    }	
}

/**
 * Запрашивает данные каналов устройства по ow_index и применяет их в контроллере
 */
void core_request_ow_values(uint8_t *rom) {
    variable_t variable;
    ds18b20_data_t ds18b20_data;
    hs_data_t hs_data;
    fc_data_t fc_data;
    dht11_data_t dht11_data;
    mq7_data_t mq7_data;
    pc_data_t pc_data;
    float value;
    int ow_index = devs_onewire_rom_index(rom);
    int vars[8];
    uint8_t vars_num = devs_find_variables_by_ow_index(ow_index, vars);
	
    switch (rom[0]) {
        case DS18B20_CODE: // ds18b20
            if (ds18b20_get_data(rom, &ds18b20_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // temp
                                value = ds18b20_data.temp;
                                if (value != 85) {
                                    core_set_variable_value(vars[i], 2, value);
                                }                            
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }			
            break;
        case HS_CODE: // hs
            if (hs_get_data(rom, &hs_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // left
                                core_set_variable_value(vars[i], 2, hs_data.left);
                                break;
                            case 1: // right
                                core_set_variable_value(vars[i], 2, hs_data.right);
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }				
            break;
        case FC_CODE: // fc
            if (fc_get_data(rom, &fc_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // f1
                                core_set_variable_value(vars[i], 2, fc_data.f1);
                                break;
                            case 1: // f2
                                core_set_variable_value(vars[i], 2, fc_data.f2);
                                break;
                            case 2: // f3
                                core_set_variable_value(vars[i], 2, fc_data.f3);
                                break;
                            case 3: // f4
                                core_set_variable_value(vars[i], 2, fc_data.f4);
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }				
            break;
        case PC_CODE: // pc
            if (pc_get_data(rom, &pc_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // p1
                                core_set_variable_value(vars[i], 2, pc_data.p1);
                                break;
                            case 1: // p2
                                core_set_variable_value(vars[i], 2, pc_data.p2);
                                break;
                            case 2: // p3
                                core_set_variable_value(vars[i], 2, pc_data.p3);
                                break;
                            case 3: // p4
                                core_set_variable_value(vars[i], 2, pc_data.p4);
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }				
            break;
        case DHT11_CODE: // dht11
            if (dht11_get_data(rom, &dht11_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // p1
                                core_set_variable_value(vars[i], 2, dht11_data.h);
                                break;
                            case 1: // p2
                                core_set_variable_value(vars[i], 2, dht11_data.t);
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }				
            break;
        case MQ7_CODE: // mq7
            if (mq7_get_data(rom, &mq7_data)) {
                for (uint8_t i = 0; i < vars_num; i++) {
                    if (devs_get_varible(vars[i], &variable)) {
                        switch (variable.channel) {
                            case 0: // p1
                                core_set_variable_value(vars[i], 2, mq7_data.co);
                                break;
                            default: ;
                        }
                    }
                }
            } else {
                board_onewire_error();
            }				
            break;
        case 0xf5: // ampermetr
            break;
    }
}