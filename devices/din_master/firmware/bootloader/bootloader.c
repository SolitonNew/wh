/*
 * bootloader.c
 *
 * Created: 22.03.2021 20:08:22
 *  Author: User
 */ 

#include "board.h"
#include <avr/io.h>
#include <avr/interrupt.h>
#include <avr/boot.h>

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8
#define USART_RXC_vect _VECTOR(11)

typedef struct _rs485_cmd_pack {  // 8 bytes
    uint8_t sign[3];  // CMD
    uint8_t controller_id;
    uint8_t cmd;
    int tag;
    uint8_t crc;
} rs485_cmd_pack_t;

typedef struct _rs485_hex_pack { // 13 bytes
	uint8_t sign[3]; // HEX
	uint8_t controller_id;
	uint8_t data[8];
	uint8_t crc;
} rs485_hex_pack_t;


uint16_t rs485_errors = 0;
uint16_t rs485_packs = 0;
uint8_t controller_id;

uint8_t rs485_in_buff[RS485_BUFF_MAX_SIZE];
uint8_t rs485_in_buff_size = 0;

ISR(USART_RXC_vect) {
    uint8_t c = UDR;
    
    // Накапливаем входящий буфер
    rs485_in_buff[rs485_in_buff_size++] = c;
    
    // Защиты от переполнения не будет
	// Просто засветим светодиодом и все
    if (rs485_in_buff_size >= RS485_BUFF_MAX_SIZE) {
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
            //board_reset();
            break;
		case 24: // for boot loader
		    break;
		case 25: // for boot loader
		    break;
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
    if (memeq(&rs485_in_buff[0], (uint8_t*)"HEX", 3)) {
        pack_sign = 4;
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
            }                            
        } else {
            board_rs485_error();
            size = 0; // На дообработку
        }
    } else
    if (pack_sign == 4) {  // HEX   
        rs485_hex_pack_t pack;
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
            }
        } else {
            board_rs485_error();
            size = 0; // На дообработку
        }
    }
	
    if (pack_sign == 0 || size == 0) { // С данными что-то не то. Ищем подобие сигнатуры
        size = 0;
        for (uint8_t i = 1; i < rs485_in_buff_size - 2; i++) {
            if (rs485_in_buff[i] >= 'A' && rs485_in_buff[i + 1] >= 'A' && rs485_in_buff[i + 2] >= 'A') {
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

void boot_program_page (uint32_t page, uint8_t *buff) {
    uint8_t sreg = SREG;
    cli();
    eeprom_busy_wait();
    boot_page_erase(page);
    boot_spm_busy_wait();     
    for (uint16_t i = 0; i < SPM_PAGESIZE; i += 2) {
        // Set up little-endian word.
        uint16_t w = *buff++;
        w += (*buff++) << 8;    
        boot_page_fill(page + i, w);
    }
    boot_page_write(page);
    boot_spm_busy_wait();
    boot_rww_enable();
    SREG = sreg;
}

int main(void)
{
	rs485_init();
	
    while(1)
    {
        rs485_in_buff_unpack();
    }
}