/*
 * rs485.c
 *
 * Created: 07.03.2021 13:34:26
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include <util/delay.h>
#include <string.h>
#include "core.h"
#include "rs485.h"
#include "onewire.h"
#include "config/devs.h"

uint16_t rs485_errors = 0;
uint16_t rs485_packs = 0;
uint8_t rs485_tag = 0;

uint8_t controller_id;
uint8_t controller_initialized;

uint8_t rs485_in_buff[RS485_BUFF_MAX_SIZE];
uint8_t rs485_in_buff_size = 0;
uint8_t rs485_is_online = 0;
uint16_t rs485_recieve_count = 0;

uint8_t onewire_roms_buff[ONEWIRE_SEARCH_ROMS];
uint8_t onewire_roms_buff_count;

int core_variable_changed[CORE_VARIABLE_CHANGED_COUNT_MAX];
uint8_t core_variable_changed_count;
int variable_values[VARIABLE_COUNT];

uint8_t rs485_in_buff_lock = 0;

ISR(USART_RXC_vect) {
	uint8_t c = UDR;
	
	if (rs485_in_buff_lock) {
		if (rs485_in_buff_size <= RS485_BUFF_MAX_SIZE / 2) {
			rs485_in_buff_lock = 0;
		} else { 
			return ;
		}			
    }
	
	// Накапливаем входящий буфер
	rs485_in_buff[rs485_in_buff_size++] = c;
	
	// Защита от переполнения
	// Если дошли до полного заполнения буфера ставим блокировку.
	// Блокировка будет снята только после уменьшения входного буфера 
	// меньше половины его максимально возможного размера.
	if (rs485_in_buff_size >= RS485_BUFF_MAX_SIZE) {
		rs485_in_buff_lock = 1;
		board_rs485_error();
	}
}

void rs485_init(void) {
	unsigned int ubrr = RS485_UBRR;
	
	// Частота
    UBRRH = (uint8_t)(ubrr>>8);
	UBRRL = (uint8_t)ubrr;
	
	// Включаем
	UCSRB = (1<<RXCIE) | (1<<RXEN) | (1<<TXEN);
	
	// 8bit  2 stop bits 
	UCSRC = (1<<URSEL) | (1<<UCSZ0) | (1<<UCSZ1) | (1<<USBS);
}

uint8_t rs485_crc_table(uint8_t data) {
	uint8_t crc = 0x0;
	uint8_t fb_bit = 0;
	for (uint8_t b = 0; b < 8; b++) { 
		fb_bit = (crc ^ data) & 0x01;
		if (fb_bit == 0x01)
			crc = crc ^ 0x18;
		crc = (crc >> 1) & 0x7F;
		if (fb_bit == 0x01) 
			crc = crc | 0x80;
		data >>= 1;
	}
	return crc;
}

void rs485_write_byte(uint8_t c) {
    while (!(UCSRA & (1<<UDRE))) ;
    UDR = c;
}

void rs485_transmit_CMD(uint8_t cmd, int tag) {
    rs485_cmd_pack_t pack;
    memcpy(pack.sign, "CMD", 3);
    pack.controller_id = controller_id;
    pack.cmd = cmd;
    pack.tag = tag;
    uint8_t *ind = (uint8_t*)&pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
	
	switch (cmd) {
		case 4:
		case 5:
		    board_rs485_incoming_package(0);
			break;
	}
}

void rs485_transmit_VAR(int id, int value) {
    rs485_var_pack_t pack;
    memcpy(pack.sign, "VAR", 3);
    pack.controller_id = controller_id;
    pack.id = id;
    pack.value = value;
    uint8_t *ind = (uint8_t*)&pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
}

void rs485_transmit_ROM(uint8_t *rom) {
    rs485_ow_rom_pack_t pack;
    memcpy(pack.sign, "ROM", 3);
    pack.controller_id = controller_id;
    memcpy(pack.rom, rom, 8);
    uint8_t *ind = (uint8_t*)&pack;
	uint8_t crc = 0;
	for (uint8_t i = 0; i < sizeof(pack) - 1; i++) {
        uint8_t b = *ind++;
		crc = rs485_crc_table(crc ^ b);
        rs485_write_byte(b);
	}
    rs485_write_byte(crc);
}

uint8_t memeq(uint8_t *a1, uint8_t *a2, uint8_t len) {
    for (uint8_t i = 0; i < len; i++) {
        if ((*a1++) != (*a2++)) return 0;
    }
    return 1;
}

void rs485_cmd_pack_handler(rs485_cmd_pack_t *pack) {
    uint8_t i;
    int index;
    switch (pack->cmd) {
        case 1: // reset
            board_reset();
            break;
        case 2: // match receive
            board_rs485_incoming_package(1);
            rs485_is_online = 2;
            rs485_recieve_count = pack->tag;
            break;
        case 3: // match transmit
            rs485_is_online = 3;
            if (!controller_initialized) {
                rs485_transmit_CMD(5, 0);
            } else {
                rs485_transmit_CMD(4, core_variable_changed_count);
                for (i = 0; i < core_variable_changed_count; i++) {
                    index = devs_get_variable_index(core_variable_changed[i]);
                    rs485_transmit_VAR(core_variable_changed[i], variable_values[index]);
                }
                core_variable_changed_count = 0;
            }
            break;
        case 4: // pack transmit count
            // not records            
            break;
        case 5: // pack transmit init
            rs485_is_online = 5;
            break;
        case 6: // match receive init
            rs485_is_online = 6;
            rs485_recieve_count = pack->tag;
            controller_initialized = 1; // Помечаем, что контроллер проинициализирован. Теперь можем принимать данные.
            break;
        case 7: // match ow scan
		    board_rs485_incoming_package(1);
            rs485_is_online = 7;
            board_onewire_search(1);
            onewire_search();
            rs485_transmit_CMD(4, onewire_roms_buff_count);
            for (int i = 0; i < onewire_roms_buff_count * 8; i += 8) {
                rs485_transmit_ROM(&onewire_roms_buff[i]);
            }
            board_onewire_search(0);
            onewire_roms_buff_count = 0;
            break;
    }
}

void rs485_var_pack_handler(rs485_var_pack_t *pack) {
    rs485_recieve_count--;
    if (rs485_is_online == 6) {
        core_set_variable_value_int(devs_get_variable_index(pack->id), 0, pack->value);
    } else {
        core_set_variable_value_int(devs_get_variable_index(pack->id), 1, pack->value);
    } 
}

void rs485_in_buff_unpack(void) {
    start_unpack:;    
    if (rs485_in_buff_size < RS485_BUFF_MIN_SIZE) return ;
    
    // достигли минимального объема для возможной обработки
	
	uint8_t pack_sign = 0;
	if (memeq(&rs485_in_buff[0], (uint8_t*)"CMD", 3)) {
        pack_sign = 1;
	} else
	if (memeq(&rs485_in_buff[0], (uint8_t*)"VAR", 3)) {
		pack_sign = 2;
	} else
	if (memeq(&rs485_in_buff[0], (uint8_t*)"ROM", 3)) {
		pack_sign = 3;
	}
	
    uint8_t size = 0;
    if (pack_sign == 1) { // CMD
        rs485_cmd_pack_t pack;
        size = sizeof(pack);
        uint8_t *ind = (uint8_t*)&pack;
	    uint8_t crc = 0;
	    for (uint8_t i = 0; i < size; i++) {
		    crc = rs485_crc_table(crc ^ rs485_in_buff[i]);
            (*ind++) = rs485_in_buff[i];
	    }
        if (crc == 0) { // Все нормально - обрабатываем
			rs485_packs++;
            if (pack.controller_id == controller_id) { // это наши данные
                rs485_cmd_pack_handler(&pack);
            } else {
                rs485_is_online = 0;
            }                            
        } else {
            size = 0; // На дообработку
        }
    } else
    if (pack_sign == 2) { // VAR
        rs485_var_pack_t pack;
        size = sizeof(pack);
        if (rs485_in_buff_size < size) return ;
        uint8_t *ind = (uint8_t*)&pack;
	    uint8_t crc = 0;
	    for (uint8_t i = 0; i < size; i++) {
		    crc = rs485_crc_table(crc ^ rs485_in_buff[i]);
            (*ind++) = rs485_in_buff[i];
	    }
        if (crc == 0) { // Все нормально - обрабатываем
			rs485_packs++;
            if (pack.controller_id == controller_id) { // это наши данные
                if (controller_initialized) {
                    rs485_var_pack_handler(&pack);
                }
            } else {
                rs485_is_online = 0;
            }
        } else {
            size = 0; // На дообработку
        }
    } else
    if (pack_sign == 3) {  // ROM   обрабатываем этот пакет только ради очереди. Таких данных на вход не бывает.
        rs485_ow_rom_pack_t pack;
        size = sizeof(pack);
        if (rs485_in_buff_size < size) return ;
        uint8_t *ind = (uint8_t*)&pack;
	    uint8_t crc = 0;
	    for (uint8_t i = 0; i < size; i++) {
		    crc = rs485_crc_table(crc ^ rs485_in_buff[i]);
            (*ind++) = rs485_in_buff[i];
	    }
        if (crc == 0) { // Все нормально - обрабатываем
			rs485_packs++;
            if (pack.controller_id == controller_id) { // это наши данные
                // not records   
            } else {
                rs485_is_online = 0;
            }
        } else {
            size = 0; // На дообработку
        }
    }
	
	if (pack_sign == 0 || size == 0) { // С данными что-то не то. Ищем подобие сигнатуры
		size = 0;
		for (uint8_t i = 1; i < rs485_in_buff_size - 2; i++) {
			if (rs485_in_buff[i] >= 'A' && 
			    rs485_in_buff[i + 1] >= 'A' && 
				rs485_in_buff[i + 2] >= 'A') {
					size = i;
					break;
				}
		}
	}		
    			
    uint8_t goto_start_unpack = 0;
    cli();
	if (size == 0) { // Это значит, что данных с подобием сигнаты не нашли. Все в мусорку.
		rs485_in_buff_size = 0;
	} else		
    if (size == rs485_in_buff_size) { // самый простой вариант - просто обнуляем буфер
        rs485_in_buff_size = 0;
    } else { // сложнее - сдвигаем на size к началу и повторяем операцию
        for (uint8_t i = 0; i < rs485_in_buff_size - size; i++) {
            rs485_in_buff[i] = rs485_in_buff[i + size];
        }
        rs485_in_buff_size -= size;
        goto_start_unpack = 1;
    }
    sei();
    
    if (goto_start_unpack) {
        goto start_unpack;
    }
}
