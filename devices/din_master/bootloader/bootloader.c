/*
 * bootloader.c
 *
 * Created: 22.03.2021 20:08:22
 *  Author: User
 */ 

#define F_CPU 2000000UL

#include <avr/io.h>
#include <avr/interrupt.h>
#include <util/delay.h>
//#include <avr/boot.h>

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))

#define BOOT_TIMEOUT 4000  // 4s

#define CONTROL_LED_R_DDR  DDRA
#define CONTROL_LED_R_PORT PORTA
#define CONTROL_LED_R_BIT  1

#define CONTROL_LED_G_DDR  DDRA
#define CONTROL_LED_G_PORT PORTA
#define CONTROL_LED_G_BIT  3

#define CONTROL_LED_Y_DDR  DDRA
#define CONTROL_LED_Y_PORT PORTA
#define CONTROL_LED_Y_BIT  5

#define CONTROL_LED_B_DDR  DDRA
#define CONTROL_LED_B_PORT PORTA
#define CONTROL_LED_B_BIT  7

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8

/*uint16_t rs485_errors = 0;
uint16_t rs485_packs = 0;
uint8_t controller_id = 0;

uint8_t is_boot = 0;
uint8_t page_buff[SPM_PAGESIZE];
uint8_t page_buff_size = 0;

uint8_t rs485_in_buff[RS485_BUFF_MAX_SIZE];
uint8_t rs485_in_buff_size = 0;

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

uint8_t rs485_check_crc(uint8_t size) {
    uint8_t crc = 0;
    for (uint8_t i = 0; i < size; i++) {
        crc = rs485_crc_table(crc ^ rs485_in_buff[i]);
    }
	return (crc == 0);
}

void rs485_in_buff_unpack(void) {
	start_unpack:;
    uint8_t pack_sign = 0;
	if (rs485_in_buff[0] == 'C' && rs485_in_buff[1] == 'M' && rs485_in_buff[2] == 'D') {
		pack_sign = 1;
	} else 
	if (rs485_in_buff[0] == 'H' && rs485_in_buff[1] == 'E' && rs485_in_buff[2] == 'X') {
		pack_sign = 4;
	}
	
	uint8_t size = 0;
	if (pack_sign == 1) { // CMD
		size = 8;
		if (rs485_in_buff_size < size) return ;
		if (rs485_check_crc(size)) {
			if (rs485_in_buff[3] == controller_id) {
				switch (rs485_in_buff[4]) {
                    case 1: // reset
                        //board_reset();
                        break;
		            case 24: // for boot loader
					    is_boot = 1;
		                break;
		            case 25: // for boot loader
		                break;
				}
			}
		} else {
			rs485_errors++;
            size = 0; // На дообработку
		}
	} else
	if (pack_sign == 4) { // HEX
		size = 13;
		if (rs485_in_buff_size < size) return ;
		if (rs485_check_crc(size)) {
			if (rs485_in_buff[3] == controller_id) {
				for (uint8_t i = 0; i < 8; i++) {
					page_buff[page_buff_size++] = rs485_in_buff[i];
				}
			}
		} else {
			rs485_errors++;
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

void boot_program_page(uint32_t page, uint8_t *buff) {
	return ;
    //uint8_t sreg = SREG;
    //cli();
    eeprom_busy_wait();
    boot_page_erase(page);
    boot_spm_busy_wait();     
    for (uint16_t i = 0; i < SPM_PAGESIZE; i += 2) {
        uint16_t w = *buff++;
        w += (*buff++) << 8;    
        boot_page_fill(page + i, w);
    }
    boot_page_write(page);
    boot_spm_busy_wait();
    boot_rww_enable();
    //SREG = sreg;
}

void leds_on(void) {
	SPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	SPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	SPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
}

void leds_off(void) {
	CPIN(CONTROL_LED_R_PORT, CONTROL_LED_R_BIT);
	CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
	CPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
	CPIN(CONTROL_LED_B_PORT, CONTROL_LED_B_BIT);
}

uint32_t page = 0;
int boot_timeout = 0; */

void rs485_init(void) {
	// Инициализация rs485
    unsigned int ubrr = RS485_UBRR;
    // Частота
    UBRRH = (uint8_t)(ubrr>>8);
    UBRRL = (uint8_t)ubrr;
    // Включаем
    UCSRB = (1<<RXCIE) | (1<<RXEN) | (1<<TXEN);
    // 8bit  2 stop bits 
    UCSRC = (1<<URSEL) | (1<<UCSZ0) | (1<<UCSZ1) | (1<<USBS);
}

ISR(USART__RXC_vect) {
	uint8_t c = UDR;
	SPIN(CONTROL_LED_Y_PORT, CONTROL_LED_Y_BIT);
    // Накапливаем входящий буфер
    //rs485_in_buff[rs485_in_buff_size++] = c;
}

int main(void)
{
	//controller_id = 1;
	

	
	// Инициализация LED индикаторов
	SPIN(CONTROL_LED_R_DDR, CONTROL_LED_R_BIT);
	SPIN(CONTROL_LED_G_DDR, CONTROL_LED_G_BIT);
	SPIN(CONTROL_LED_Y_DDR, CONTROL_LED_Y_BIT);
	SPIN(CONTROL_LED_B_DDR, CONTROL_LED_B_BIT);
	
	//leds_on();
	//_delay_ms(1000);
	//leds_off();
	
	rs485_init();
	sei();
	
    while (1) {
		if (GPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT)) {
			CPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
		} else {
			SPIN(CONTROL_LED_G_PORT, CONTROL_LED_G_BIT);
		}
		_delay_ms(500);
		
		
        /*rs485_in_buff_unpack();
		cli();
		if (page_buff_size >= SPM_PAGESIZE) {
			leds_on();
			boot_program_page(page, page_buff);
			page += SPM_PAGESIZE;
			page_buff_size = 0;
			leds_off();
		}
		sei(); */
		_delay_ms(1);
    }
}