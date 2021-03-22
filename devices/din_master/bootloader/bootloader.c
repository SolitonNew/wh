/*
 * bootloader.c
 *
 * Created: 22.03.2021 20:08:22
 *  Author: User
 */ 

#include <avr/io.h>
#include <avr/interrupt.h>
#include <avr/boot.h>

typedef _rs485_hex_pack {
	uint8_t sign[3];
	uint8_t controller_id;
	uint16_t addr;
} rs485_hex_pack_t;

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
    while(1)
    {
        //TODO:: Please write your application code 
    }
}