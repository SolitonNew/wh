/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#include <avr/io.h>

#define ONEWIRE_DDR DDRD
#define ONEWIRE_PORT PORTD
#define ONEWIRE_PIN PIND
#define ONEWIRE_BIT 7

#define	ONEWIRE_SEARCH_FIRST 0xFF
#define	ONEWIRE_PRESENCE_ERR 0xFF
#define	ONEWIRE_DATA_ERR 0xFE
#define ONEWIRE_LAST_DEVICE 0x00

#define ONEWIRE_SEARCH_ROM 0xF0
#define ONEWIRE_ALARM_SEARCH 0xEC
#define ONEWIRE_MATCH_ROM 0x55
#define ONEWIRE_SKIPROM 0xCC
#define ONEWIRE_READ_DATA 0xA0
#define ONEWIRE_WRITE_DATA 0xB0

#define ONEWIRE_SEARCH_LIMIT 40
#define ONEWIRE_SEARCH_ROMS ONEWIRE_SEARCH_LIMIT * 8

extern int onewire_error;
extern uint8_t onewire_roms_buff[ONEWIRE_SEARCH_ROMS];
extern uint8_t onewire_roms_buff_count;

void onewire_init(void);
uint8_t onewire_crc_table(uint8_t data);
uint8_t onewire_reset(void);
void onewire_write_byte(uint8_t byte);
uint8_t onewire_read_byte(void);
uint8_t onewire_match_rom(uint8_t *rom);
uint8_t onewire_search(void);
uint8_t onewire_alarms(void);
