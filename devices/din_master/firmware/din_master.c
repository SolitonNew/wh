/*
 * din_master.c
 *
 * Created: 06.03.2021 0:15:01
 *  Author: User
 */ 

#include "globals.h"
#include <avr/io.h>
#include "util/delay.h"
#include "core.h"
#include "control.h"
#include "onewire.h"

#include "config/devs.h"
#include "drivers/ds18b20.h"
#include "drivers/dht11.h"

#include "lcd.h"

uint8_t controller_id;
control_btn_states_t control_btn_states = {0, 0, 0, 0};
	
//uint8_t roms[80];

ds18b20_data_t ds18b20_data;
dht11_data_t dht11_data;
uint8_t text[16];

int main(void)
{
	controller_id = 1;
	
	//control_init();
	//core_init();

	lcd_init();
	
	onewire_init();
	
	/*uint8_t num = onewire_search(roms);
	uint8_t ind = 0;
	for (uint8_t i = 0; i < num; i++) {
		for (uint8_t k = 0; k < 8; k++) {
			lcd_hex(roms[ind]);
			ind++;
		}
		lcd_nl();
	}*/
	
    while(1)
    {	
		//core_rs485_processing();
		//core_onewire_alarm_processing();
		//core_schedule_processing();
		
		// Обработка кнопок управления
		control_check_btn(&control_btn_states);
		if (control_btn_states.btn_1) {
			control_led_r(1);
		}
		
		// -------------------------------
		lcd_clear();
		
		int ind = 0;
		for (uint8_t i = 0; i < onewire_roms_count; i++) {
			uint8_t rom[8];
			for (uint8_t r = 0; r < 8; r++) {
				rom[r] = pgm_read_byte(&onewire_roms[ind]);
				ind++;
			}
			
			uint8_t c = 0;
			switch (rom[0]) {
				case 0x28:
					ds18b20_get_data(rom, &ds18b20_data);
					c = sprintf(text, "   T:%d", (int)ds18b20_data.temp);
					ds18b20_start_measure(rom);
					break;
				case 0xf3:
					dht11_get_data(rom, &dht11_data);
					c = sprintf(text, "%d  H:%d  T:%d", (int)rom[6], (int)dht11_data.h, (int)dht11_data.t);
					break;
			}
			
			lcd_move(1, 1 + i);
			lcd_text(text, c);
		}
		// -------------------------------
		
		_delay_ms(750);
    }
}

