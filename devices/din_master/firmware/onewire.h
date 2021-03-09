/*
 * onewire.h
 *
 * Created: 07.03.2021 13:33:02
 *  Author: User
 */ 

#define ONEWIRE_DDR DDRB
#define ONEWIRE_PORT PORTB
#define ONEWIRE_PIN PINB
#define ONEWIRE_BIT 7

#define	ONEWIRE_SEARCH_FIRST	0xFF
#define	ONEWIRE_PRESENCE_ERR	0xFF
#define	ONEWIRE_DATA_ERR	    0xFE
#define ONEWIRE_LAST_DEVICE		0x00

#define ONEWIRE_SEARCH_ROM 0xF0
#define ONEWIRE_ALARM_SEARCH 0xEC
#define ONEWIRE_MATCH_ROM 0x55
#define ONEWIRE_SKIPROM 0xCC
#define ONEWIRE_READ_DATA 0xA0
#define ONEWIRE_WRITE_DATA 0xB0

#define ONEWIRE_SEARCH_LIMIT = 40
#define ONEWIRE_ALARM_LIMIT 10

void onewire_init(void);
unsigned char onewire_search(unsigned char *roms);
unsigned char onewire_alarms(unsigned char *roms);

unsigned char onewire_match_rom(unsigned char *rom);

float onewire_get_value(unsigned char *rom);
void onewire_set_value(unsigned char *rom, float value);