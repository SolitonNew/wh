/*
 * ow_4_rele.c
 *
 * Created: 19.04.2017 18:51:21
 *  Author: Александр
 */ 

#define F_CPU 9600000UL
#include "avr/io.h"
#include <avr/interrupt.h>
#include "util/delay.h"

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))

#define OW_DDR DDRB
#define OW_READ PINB
#define OW_PIN 1

#define SEARCH_ROM 0xF0
#define ALARM_SEARCH 0xEC
#define MATCH_ROM 0x55
#define READ_DATA 0xA0
#define WRITE_DATA 0xB0

#define OW_UP OW_DDR &= ~(1 << OW_PIN)
#define OW_DOWN OW_DDR |= (1 << OW_PIN)
#define IS_LOW ((OW_READ & (1 << OW_PIN)) == 0)
#define IS_HIGH (OW_READ & (1 << OW_PIN))

#define WAIT_COUNT 1000
#define WAIT_FOR_LOW for (int i = 0; i < WAIT_COUNT && IS_HIGH; i++)
#define WAIT_FOR_HIGH for (int i = 0; i < WAIT_COUNT && IS_LOW; i++)

unsigned char ROM[8] = {0xF1,0x00,0x00,0x00,0x00,0x01,0x01,0x0};

unsigned char crc_table(unsigned char data)
{
	unsigned char crc = 0x0;
	unsigned char fb_bit = 0;
	for (unsigned char b = 0; b < 8; b++)
	{ 
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

unsigned char OW_readBit()
{
	unsigned char res = 0;
	WAIT_FOR_LOW;
	_delay_us(20);	
	if (IS_HIGH) res = 1;
	WAIT_FOR_HIGH;
	return res;
}

unsigned char OW_readByte()
{
	unsigned char res = 0;
	WAIT_FOR_HIGH;
	for (unsigned char i = 0; i < 8; i++)
	{
		res = res >> 1;
		if (OW_readBit())
			res |= 0x80;
	}
	return res;
}

void OW_writeBit(unsigned char b)
{	
	WAIT_FOR_LOW;
	OW_UP;
	//_delay_us(1);
	if (b == 0) OW_DOWN;	
	_delay_us(60);
	OW_UP;
	WAIT_FOR_HIGH;
}

void OW_writeByte(unsigned char data)
{
	WAIT_FOR_HIGH;
	for (unsigned char i = 0; i < 8; i++)
	{
		OW_writeBit(data & 1);
		data >>= 1;
	}
}

void one_wire_action()
{	
	//Presence
	WAIT_FOR_HIGH;
	_delay_us(30);
	OW_DOWN;
	_delay_us(100);
	OW_UP;		
	WAIT_FOR_HIGH;
	
	unsigned char i;
	unsigned char k;
	unsigned char count;
	unsigned char tmp[4];
	unsigned char crc = 0;
	
	unsigned char rom_cmd = OW_readByte();	

	switch (rom_cmd)
	{
		case SEARCH_ROM: // Поиск устройств на шине
		//case ALARM_SEARCH: // Поиск устройств с флагом ALARM
			//if ((rom_cmd != SEARCH_ROM) && (isChange == 0))
			//	return ;
			for (i = 0; i < 8; i++)
			{	
				unsigned char b = ROM[i];
				for (k = 0; k < 8; k++)
				{			
					unsigned char wb = (b & 1);
					OW_writeBit(wb);
					OW_writeBit(!wb);									
					unsigned char rb = OW_readBit();
					if (rb != wb)
						return ;			
					b >>= 1;
				}
			}				
			break; 
		
		case MATCH_ROM: // Выбор устройства
			// Проверем ключ устройства
			for (i = 0; i < 8; i++) 
				if (OW_readByte() != ROM[i])
					return ;
												
			switch (OW_readByte()) // Читаем комманду для этого устройства
			{
				case READ_DATA: // Чтение данных для мастера
					crc = 0;
					tmp[0] = GPIN(PORTB, 3);
					tmp[1] = GPIN(PORTB, 4);
					tmp[2] = GPIN(PORTB, 0);
					tmp[3] = GPIN(PORTB, 2);
					for (i = 0; i < 4; i++) {
						OW_writeByte(tmp[i]);
						crc = crc_table(crc ^ tmp[i]);
					}
					OW_writeByte(crc);
					break;
				
				case WRITE_DATA: // Запись занных от мастера
					crc = 0;
					for (i = 0; i < 4; i++) {
						tmp[i] = OW_readByte();
						crc = crc_table(crc ^ tmp[i]);
					}
					if (OW_readByte() == crc) {
						if (tmp[0]) SPIN(PORTB, 3); else CPIN(PORTB, 3);
						if (tmp[1]) SPIN(PORTB, 4); else CPIN(PORTB, 4);
						if (tmp[2]) SPIN(PORTB, 0); else CPIN(PORTB, 0);
						if (tmp[3]) SPIN(PORTB, 2); else CPIN(PORTB, 2);
					}
					break;
			}			
			break;			
		default: ;
	}	
}
	
ISR (INT0_vect)
{
	if ((MCUCR & (1<<ISC00))!=0) 
	{
		TCCR0B = 0; //Выключаем таймер
		TIFR0 |= (1<<TOV0);
		MCUCR = (1<<ISC01); //Сброс ISC00 - прерывание по \__
	}
	else
	{		
		TCNT0 = 255-60;
		TIFR0 |= (1<<TOV0); //Сброс флага TOV0
		TIMSK0 |= (1<<TOIE0);
		MCUCR = (1<<ISC01)|(1<<ISC00); // прерывание по __/
		TCCR0B = (1<<CS01)|(1<<CS00); //Делитель на 64		
	}
}

ISR (TIM0_OVF_vect)
{			
	TCCR0B = 0;
	TIFR0 |= 1<<TOV0;
	one_wire_action();
}

#define check_chan(i, mask) if (iter < chan_v[i]) PORTB |= (1<<mask); else PORTB &= ~(1<<mask);

int main(void)
{
	unsigned char crc = 0;
	for (unsigned char i = 0; i < 7; i++)
		crc = (crc_table(crc ^ ROM[i]));
	ROM[7] = crc;
	// Шина
	CPIN(OW_DDR, OW_PIN);
		
	TIFR0 |= 1<<TOV0;
	GIFR |= 1<<INTF0;
	MCUCR = (1<<ISC01); //Сброс ISC00 - прерывание по \__
	GIMSK |= (1<<INT0);
	
	sei();

	DDRB = (1<<PORTB0) | (1<<PORTB2) | (1<<PORTB3) | (1<<PORTB4);
    while(1)
    {
		
    }
}