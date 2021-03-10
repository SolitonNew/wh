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
#include "config/devs.h"
#include "config/scripts.h"
#include "drivers/ds18b20.h"
#include "drivers/hs.h"
#include "drivers/dht11.h"
#include "drivers/mq7.h"
#include "drivers/ow4rele.h"


uint8_t core_onewire_alarm_buff[ONEWIRE_ALARM_LIMIT * 8];

int core_get_variable_index(int id) {
	for (int i = 0; i < variable_count; i++) {
		int vid = pgm_read_dword(&variables[i]);
		if (vid == id) {		
			return i;
		}
	}
	return -1;
}

uint8_t core_get_variable_controller(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 2);
}

uint8_t core_get_variable_typ(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 3);
}

uint8_t core_get_variable_direction(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 4);
}

int core_get_variable_ow_index(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_dword((int)(&variables[index]) + 5);
}

uint8_t core_get_variable_channel(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return pgm_read_byte((int)(&variables[index]) + 7);
}

void core_get_variable_rom(int index, uint8_t *rom) {
	int ow_index = core_get_variable_ow_index(index);
	if (ow_index > -1) {
		int ind = (int)&onewire_roms[ow_index * 8];
		for (uint8_t i = 0; i < 8; i++) {
			rom[i] = pgm_read_byte(ind++);
		}
	} else {
		rom[0] = 0;
	}
}

int core_onewire_rom_index(uint8_t *rom) {
	int ind = (int)&onewire_roms[0];
	for (int i = 0; i < onewire_roms_count; i++) {
		for (int r = 0; r < 8; r++) {
			uint8_t b = pgm_read_byte(ind + r);
			if (rom[r] == b) {
				if (r == 7) {
					return i;
				}
			} else {
				break;
			}
		}
		ind += 8;
	}
	return -1;
}

uint8_t core_find_variables_by_ow_index(int ow_index, int *vars) {
	uint8_t num = 0;
	for (int i = 0; i < variable_count; i++) {
		if (core_get_variable_ow_index(i) == ow_index) {
			vars[num++] = i;
			if (num == 8) break;
		}
	}
	return num;
}

float core_get_variable_value(int index) {
	if ((index < 0) || (index >= variable_count)) return 0;
	return variable_values[index];
}

void core_set_variable_value(int index, float value) {
	if ((index < 0) || (index >= variable_count)) return ;
	variable_values[index] = value;
}

void core_init(void) {
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
	// с дальнейшей реакцией по модификации переменных
	
	int ow_index;
	int vars[8];
	uint8_t vars_count;
	ds18b20_data_t ds18b20_data;
	hs_data_t hs_data;
	dht11_data_t dht11_data;
	mq7_data_t mq7_data;
	
	uint8_t num = onewire_alarms(core_onewire_alarm_buff);
	if (num) {
		int rom_ind = 0;
		for (uint8_t i = 0; i < num; i++) {
			ow_index = core_onewire_rom_index(&core_onewire_alarm_buff[rom_ind]);
			if (ow_index > -1) {
				vars_count = core_find_variables_by_ow_index(ow_index, vars);
			} else {
				vars_count = 0;
			}
			switch (core_onewire_alarm_buff[rom_ind]) {
				case 0x28: // ds18b20
					if (ds18b20_get_data(&core_onewire_alarm_buff[rom_ind], &ds18b20_data)) {
						for (uint8_t n = 0; n < vars_count; n++) {
							if (core_get_variable_controller(vars[n]) == controller_id) {
								core_set_variable_value(vars[n], ds18b20_data.temp);
							}
						}
					}
					break;
				case 0xf0: // hs
					if (hs_get_data(&core_onewire_alarm_buff[rom_ind], &hs_data)) {
						for (uint8_t n = 0; n < vars_count; n++) {
							if (core_get_variable_controller(vars[n]) == controller_id) {
								switch (core_get_variable_channel(vars[n])) {
									case 0:
										core_set_variable_value(vars[n], hs_data.left);
										break;
									case 1:
										core_set_variable_value(vars[n], hs_data.right);
										break;
								}
							}
						}
					}
					break;
				case 0xf1: // ow_4_rele
					// not record
					break;
				case 0xf3: // dht11
					if (dht11_get_data(&core_onewire_alarm_buff[rom_ind], &dht11_data)) {
						//
					}
					break;
				case 0xf4: // mq7
					if (mq7_get_data(&core_onewire_alarm_buff[rom_ind], &mq7_data)) {
						//
					}
			}
			rom_ind += 8;
		}
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
