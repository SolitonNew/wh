/*
 * variables.c
 *
 * Created: 07.03.2021 13:34:41
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include <avr/pgmspace.h>
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


uint8_t core_onewire_alarm_buff[ONEWIRE_ALARM_LIMIT * 8];

float core_get_variable_value(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return variable_values[index];
}

// target: 0-server, 1-devs, 2-script
void core_set_variable_value(int index, uint8_t target, float value) {
	if ((index < 0) || (index >= variable_count)) return ;
	if (variable_values[index] == value) return ;
	
	variable_values[index] = value;
	
	variable_t variable;
	devs_get_varible(index, &variable);
	
	if (variable.controller_id == controller_id) { // Это переменная этого контроллера
		// Выполняем пересылку новых состояний для devs
		switch(variable.typ) {
			case 0: // din
				din_set_value(variable.channel, value);
				break;
			case 1: // ow
				core_transmit_ow_values(variable.ow_index);
				break;
			case 2: // variable;
				// not records
				break;
		}
		
		// Пишем в лог изменений для отправки на сервер
		switch (target) {
			case 1: // devs
			case 2: // script
				
				break;
		}
		
		// Запрашиваем выполнение скрпита по событию изменения
		script_run_event_for_variable(index);
	}	
}

void core_init(void) {
	din_init();
	rs485_init();
	onewire_init();
}

void core_rs485_processing(void) {
	// Обработка буфера входящих пакетов
	// Отсылка данных из буфера исходящих пакетов
	// Реакция на сервисные команды
	
}

void core_onewire_alarm_processing(void) {
	// Обработка alarm событий на шине OW
		
	uint8_t alarm_num = onewire_alarms(core_onewire_alarm_buff);
	if (alarm_num) {
		uint8_t* ind = (uint8_t*)&core_onewire_alarm_buff[0];
		for (uint8_t i = 0; i < alarm_num; i++) {
			core_request_ow_values(ind);
			ind += 8;
		}
	}
}

// Собирает данные всех переменных с ow_index и отправляет в устройство
void core_transmit_ow_values(int ow_index) {
	variable_t variable;
	fc_data_t fc_data;
	
	uint8_t rom[8];
	devs_get_ow_rom(ow_index, rom);
	int vars[8];
	uint8_t vars_num = devs_find_variables_by_ow_index(ow_index, vars);
	
	switch (rom[0]) {
		case 0x28: // ds18b20
			// readonly
			break;
		case 0xf0: // hs
			// readonly
			break;
		case 0xf1: // fc			
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
		case 0xf2: // pc
			// readonly
			break;
		case 0xf3: // dht11
			// readonly
			break;
		case 0xf4: // mq7
			// readonly
			break;
		case 0xf5: // ampermetr
			// readonly
			break;
	}	
}

// Запрашивает данные каналов устройства по ow_index и применяет их в контроллере
void core_request_ow_values(uint8_t *rom) {
	variable_t variable;
	ds18b20_data_t ds18b20_data;
	hs_data_t hs_data;
	fc_data_t fc_data;
	dht11_data_t dht11_data;
	mq7_data_t mq7_data;
	pc_data_t pc_data;
	
	int ow_index = devs_onewire_rom_index(rom);
	int vars[8];
	uint8_t vars_num = devs_find_variables_by_ow_index(ow_index, vars);
	
	switch (rom[0]) {
		case 0x28: // ds18b20
			ds18b20_get_data(rom, &ds18b20_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // temp
							core_set_variable_value(vars[i], 1, ds18b20_data.temp);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf0: // hs
			hs_get_data(rom, &hs_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // left
							core_set_variable_value(vars[i], 1, hs_data.left);
							break;
						case 1: // right
							core_set_variable_value(vars[i], 1, hs_data.right);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf1: // fc
			fc_get_data(rom, &fc_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // f1
							core_set_variable_value(vars[i], 1, fc_data.f1);
							break;
						case 1: // f2
							core_set_variable_value(vars[i], 1, fc_data.f2);
							break;
						case 2: // f3
							core_set_variable_value(vars[i], 1, fc_data.f3);
							break;
						case 3: // f4
							core_set_variable_value(vars[i], 1, fc_data.f4);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf2: // pc
			pc_get_data(rom, &pc_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // p1
							core_set_variable_value(vars[i], 1, pc_data.p1);
							break;
						case 1: // p2
							core_set_variable_value(vars[i], 1, pc_data.p2);
							break;
						case 2: // p3
							core_set_variable_value(vars[i], 1, pc_data.p3);
							break;
						case 3: // p4
							core_set_variable_value(vars[i], 1, pc_data.p4);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf3: // dht11
			dht11_get_data(rom, &dht11_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // p1
							core_set_variable_value(vars[i], 1, dht11_data.h);
							break;
						case 1: // p2
							core_set_variable_value(vars[i], 1, dht11_data.t);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf4: // mq7
			mq7_get_data(rom, &mq7_data);
			for (uint8_t i = 0; i < vars_num; i++) {
				if (devs_get_varible(vars[i], &variable)) {
					switch (variable.channel) {
						case 0: // p1
							core_set_variable_value(vars[i], 1, mq7_data.co);
							break;
						default: ;
					}
				}
			}
			break;
		case 0xf5: // ampermetr
			
			break;
	}
}

int schedule_variable_index = 0;
int schedule_step = 0;
uint8_t schedule_measure_start = 1;

void core_schedule_processing(void) {
	// Поочередные запросы для периодической работы с периферией
	// В частности опрос датчиков ds18b20
	
	if (schedule_step > 100) {
		schedule_step = 0;
		
	}
	schedule_step++;
}
