/*
 * bootloader.c
 *
 * Created: 24.03.2021 11:26:46
 *  Author: User
 */ 

#define F_CPU 2000000UL

#include <avr/io.h>
#include <util/delay.h>
#include <avr/boot.h>

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))
#define GOTO_MAIN asm("jmp 0x0000")

#define LED_R_DDR  DDRA
#define LED_R_PORT PORTA
#define LED_R_BIT  1

#define LED_G_DDR  DDRA
#define LED_G_PORT PORTA
#define LED_G_BIT  3

#define LED_Y_DDR  DDRA
#define LED_Y_PORT PORTA
#define LED_Y_BIT  5

#define LED_B_DDR  DDRA
#define LED_B_PORT PORTA
#define LED_B_BIT  7

#define BTN_4_DDR  DDRA
#define BTN_4_PORT PORTA
#define BTN_4_PIN  PINA
#define BTN_4_BIT  6

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8
#define RS485_TIMEOUT_VALUE F_CPU/10  // приблизительно 3 секунды

uint8_t rs485_in_buff[RS485_BUFF_MAX_SIZE];
uint8_t rs485_in_buff_size = 0;
uint16_t rs485_errors = 0;
uint16_t rs485_packs = 0;
uint16_t rs485_hex_packs = 0;
uint8_t controller_id;
uint8_t is_boot = 0;
int in_hex_packs = 0;
uint32_t page_start = 0;
uint8_t page_buff[SPM_PAGESIZE];
uint8_t page_buff_size = 0;
uint32_t rs485_timeout = 0;

void board_reset(void) {
    WDTCR |= 1<<WDE;
    while (1) ;
}

void control_init(void) {
    SPIN(LED_R_DDR, LED_R_BIT);
    SPIN(LED_G_DDR, LED_G_BIT);
    SPIN(LED_Y_DDR, LED_Y_BIT);
    SPIN(LED_B_DDR, LED_B_BIT);
    
    CPIN(BTN_4_DDR, BTN_4_BIT);
    SPIN(BTN_4_PORT, BTN_4_BIT);
}

void leds_on(void) {
    SPIN(LED_R_PORT, LED_R_BIT);
    SPIN(LED_G_PORT, LED_G_BIT);
    SPIN(LED_Y_PORT, LED_Y_BIT);
    SPIN(LED_B_PORT, LED_B_BIT);
}

void leds_off(void) {
    CPIN(LED_R_PORT, LED_R_BIT);
    CPIN(LED_G_PORT, LED_G_BIT);
    CPIN(LED_Y_PORT, LED_Y_BIT);
    CPIN(LED_B_PORT, LED_B_BIT);
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
	uint8_t buff[7] = {
	    'C', 'M', 'D', 
		controller_id, 
		cmd, 
		(uint8_t)tag, (uint8_t)(tag >> 8)
	};
    uint8_t crc = 0;
    for (uint8_t i = 0; i < 7; i++) {
        crc = rs485_crc_table(crc ^ buff[i]);
		rs485_write_byte(buff[i]);
    }
	rs485_write_byte(crc);
}

uint8_t rs485_check_crc(uint8_t size) {
    uint8_t crc = 0;
    for (uint8_t i = 0; i < size; i++) {
        crc = rs485_crc_table(crc ^ rs485_in_buff[i]);
    }
    if (crc == 0) return 1;
    return 0;
}

void rs485_in_buff_unpack(void) {
	start_unpack:;
	
	if (rs485_in_buff_size < 3) return ;
	
    uint8_t pack_sign = 0;
    if (rs485_in_buff[0] == 'C' && rs485_in_buff[1] == 'M' && rs485_in_buff[2] == 'D') {
        pack_sign = 1;
    } else 
    if (rs485_in_buff[0] == 'V' && rs485_in_buff[1] == 'A' && rs485_in_buff[2] == 'R') {
        pack_sign = 2;
    } else
    if (rs485_in_buff[0] == 'R' && rs485_in_buff[1] == 'O' && rs485_in_buff[2] == 'M') {
        pack_sign = 3;
    } else
    if (rs485_in_buff[0] == 'H' && rs485_in_buff[1] == 'E' && rs485_in_buff[2] == 'X') {
        pack_sign = 4;
    }
    
	uint8_t size = 0;
	if (pack_sign == 1) { // CMD 8
		size = 8;
		if (rs485_in_buff_size < size) return ;       
		if (rs485_check_crc(size)) {
            int tag = (int)rs485_in_buff[5] | ((int)rs485_in_buff[6] << 8);
			if (rs485_in_buff[3] == controller_id) {
				switch (rs485_in_buff[4]) {
                    case 1: // reset
                        board_reset();
                        break;
                    case 3: // match transmit        контроллеру приготовиться отдавать данные VAR
                        // Если нажата кнопка 4 то требуем прощивку
                        if (!GPIN(BTN_4_PIN, BTN_4_BIT)) {
                            rs485_transmit_CMD(26, 0);
                            leds_on();
                        } else {
                            rs485_transmit_CMD(27, 0); // отвечаем что мы перегружались
                            GOTO_MAIN;                 // Прыгаем на основную прошивку
                        }
                        break;
		            case 24: // Приготовиться шиться
					    in_hex_packs = tag;						
					    is_boot = 1;
		                break;
		            case 25: // Сказать сколько прошилось (вернуть CMD: 4)
                        // Возвращаем кол-во полученых с правильной crc пакетов HEX
                        rs485_transmit_CMD(4, rs485_hex_packs);
                        if (tag == rs485_hex_packs) { // Прошивка полная
                            GOTO_MAIN;
                        }                            
		                break;
                    case 26: // Ручной запрос прошивки контроллером
                        break;
                    case 27: // Контроллер ответит на cmd:3 если он в буте (в штатном режиме просто перегрузился)
                        break;
				}
			}
		} else {
			rs485_errors++;
            size = 0; // На дообработку
        }
    } else
    if (pack_sign == 2) { // VAR 9
		size = 9;
		if (rs485_in_buff_size < size) return ;
		if (rs485_check_crc(size)) {
            //
		} else {
			rs485_errors++;
            size = 0; // На дообработку
		}
	} else
    if (pack_sign == 3) { // ROM 13
		size = 13;
		if (rs485_in_buff_size < size) return ;
		if (rs485_check_crc(size)) {
            //
		} else {
			rs485_errors++;
            size = 0; // На дообработку
		}
	} else
	if (pack_sign == 4) { // HEX 13
		size = 13;
		if (rs485_in_buff_size < size) return ;
		if (rs485_check_crc(size)) {
			if (rs485_in_buff[3] == controller_id) {
				if (is_boot) {
                    rs485_hex_packs++;
				    for (uint8_t i = 4; i < 12; i++) {
					    page_buff[page_buff_size++] = rs485_in_buff[i];
				    }
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
	
    if (goto_start_unpack) {
        goto start_unpack;
    }
}

void boot_program_page(uint32_t page, uint8_t *buff) {
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
}

void rs485_init(void) {
    unsigned int ubrr = RS485_UBRR;
    UBRRH = (uint8_t)(ubrr>>8);
    UBRRL = (uint8_t)ubrr;
    UCSRB = (1<<RXEN) | (1<<TXEN);
    UCSRC = (1<<URSEL) | (1<<UCSZ0) | (1<<UCSZ1) | (1<<USBS);
}

void boot_step_1(void) {
    while (1) {
		rs485_timeout = RS485_TIMEOUT_VALUE;
		while (!(UCSRA & (1<<RXC))) {
            if (rs485_timeout-- == 0) goto finish;
			if (is_boot && (in_hex_packs == rs485_hex_packs)) goto finish;
		}
        rs485_in_buff[rs485_in_buff_size++] = UDR;
		rs485_in_buff_unpack();
		if (GPIN(LED_G_PORT, LED_G_BIT)) {
            CPIN(LED_G_PORT, LED_G_BIT);
		} else {
			SPIN(LED_G_PORT, LED_G_BIT);
		}			
		
		if (page_buff_size >= SPM_PAGESIZE) {
			leds_on();
			boot_program_page(page_start, page_buff);
			page_start += SPM_PAGESIZE;
			page_buff_size -= SPM_PAGESIZE;
			leds_off();
		}			
    }
    
    finish: ;
    
    if (page_buff_size > 0) { // нужно дошить
        // Добьем буфер прошивки символами 0xFF
        for (uint32_t i = page_buff_size; i < SPM_PAGESIZE; i++) {
            page_buff[i] = 0xff;
        }
        leds_on();
        boot_program_page(page_start, page_buff);
        leds_off();
    }
    
    exit: ;
}

void boot_step_2(void) {
    // Ловим команду 25, чтобы сказать сколько ж мы получили прошщивки
    // И дальше по итогам полученой информации скажет что делать дальше
    while (1) {
		rs485_timeout = RS485_TIMEOUT_VALUE;
		while (!(UCSRA & (1<<RXC))) {
            if (rs485_timeout-- == 0) return ;
		}
        rs485_in_buff[rs485_in_buff_size++] = UDR;
		rs485_in_buff_unpack();
		if (GPIN(LED_G_PORT, LED_G_BIT)) {
            CPIN(LED_G_PORT, LED_G_BIT);
		} else {
			SPIN(LED_G_PORT, LED_G_BIT);
		}
    }
}

int main(void) {
    rs485_in_buff_size = 0;
    rs485_errors = 0;
    rs485_packs = 0;
    rs485_hex_packs = 0;
    is_boot = 0;
	in_hex_packs = 0;
    page_start = 0;
    page_buff_size = 0;
    rs485_timeout = 0;
    
	controller_id = 1;
	
	control_init();
	leds_on();
	rs485_init();
    // Начинаем читать порт на предмет команды прошиваться
    boot_step_1();
    if (is_boot) {
        boot_step_2();
    }
    leds_off();
    
    // Прыгаем в основную программу
    GOTO_MAIN;
}    