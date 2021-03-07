<<<<<<< HEAD
﻿/*
 * onewire.h
 *
 * Created: 06.02.2017 21:17:02
 *  Author: Александр
 */ 

#define OW_DDR DDRC
#define OW_PORT PORTC
#define OW_PIN PINC
#define OW_BIT 2

#define	OW_SEARCH_FIRST	0xFF		// start new search
#define	OW_PRESENCE_ERR	0xFF
#define	OW_DATA_ERR	    0xFE
#define OW_LAST_DEVICE	0x00

#define OW_SEARCH_ROM 0xF0
#define OW_ALARM_SEARCH 0xEC
#define OW_MATCH_ROM 0x55
#define OW_SKIPROM 0xCC
#define OW_READ_DATA 0xA0
#define OW_WRITE_DATA 0xB0


#define THERM_CMD_CONVERTTEMP 0x44
#define THERM_CMD_RSCRATCHPAD 0xBE

#define OW_checkIn OW_PIN & (1<<OW_BIT)

void ow_init() {
	OW_DDR &= ~(1<<OW_PIN);
}

unsigned char crc_table(unsigned char data) {
	unsigned char crc = 0x0;
	unsigned char fb_bit = 0;
	for (unsigned char b = 0; b < 8; b++) { 
		fb_bit = (crc ^ data) & 0x01;
		if (fb_bit==0x01)
			crc = crc ^ 0x18;
		crc = (crc >> 1) & 0x7F;
		if (fb_bit==0x01) 
			crc = crc | 0x80;
		data >>= 1;
	}
	return crc;
}

void OW_set(unsigned char mode) {
	if (mode) {
		OW_PORT &= ~(1<<OW_BIT);
		OW_DDR |= (1<<OW_BIT);
	} else {
		OW_PORT &= ~(1<<OW_BIT);
		OW_DDR &= ~(1<<OW_BIT);
	}
}

unsigned char OW_reset(void)
{
	unsigned char status;
	OW_set(1);
	_delay_us(480);
	OW_set(0);
	_delay_us(60);	
	status = OW_checkIn;
	_delay_us(420);
	return !status;
}


void OW_writeBit(unsigned char bit)
{
	OW_set(1);
	_delay_us(1);
	if(bit) OW_set(0); 
	_delay_us(60);	
	OW_set(0);
}

unsigned char OW_readBit(void)
{
	unsigned char bit=0;
	OW_set(1);
	_delay_us(1);	
	OW_set(0);
	_delay_us(10);
	if (OW_checkIn) bit = 1;
	_delay_us(40);
	return bit;
}

void OW_writeByte(unsigned char byte)
{
	for (unsigned char i=0; i<8; i++) 
		OW_writeBit(byte & (1<<i));
}

unsigned char OW_readByte(void)
{
	unsigned char n=0;
	for (unsigned char i=0; i<8; i++) 
		if (OW_readBit()) 
			n |= (1<<i);	
	return n;
}

void startTemp() {
	if (!OW_reset()) return 0;
	OW_writeByte(OW_SKIPROM);
	OW_writeByte(THERM_CMD_CONVERTTEMP);
}

int getTemp() {
	if (!OW_reset()) return 0;
	
	unsigned char d[9];	
		
	OW_writeByte(OW_SKIPROM);
	OW_writeByte(THERM_CMD_RSCRATCHPAD);	
	for (unsigned char i = 0; i < 9; i++)
		d[i] = OW_readByte();
	
	unsigned char crc = 0;
	for (unsigned char i = 0; i < 9; i++)
		crc = crc_table(crc ^ d[i]);
	
	if (crc == 0)
		return ceil(((d[1] << 8 | d[0]) / 16.0) * 10);
	
	return 0;
}

=======
/*
 * onewire.h
 *
 * Created: 07.03.2021 13:33:02
 *  Author: User
 */ 

#define ONEWIRE_DDR DDRC
#define ONEWIRE_PORT PORTC
#define ONEWIRE_PIN PINC
#define ONEWIRE_BIT 2

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

void onewire_init(void);
>>>>>>> ab5a208959b97b7e6e50dcb4c9a7438b438c9c2d
