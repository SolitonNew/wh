/*
 * dht11.c
 *
 * Created: 28.01.2017 14:02:05
 *  Author: Александр
 */ 

#define F_CPU 9600000UL
#include "avr/io.h"
#include <avr/interrupt.h>
#include "util/delay.h"

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))

#define DHT_DDR DDRB
#define DHT_BIT 3
#define DHT_PIN PINB
#define DHT_PORT PORTB

#define DHT_LED_BIT 0

#define DHT_IS_LOW ((DHT_PIN & (1<<DHT_BIT)) == 0)
#define DHT_IS_HIGH (DHT_PIN & (1<<DHT_BIT))
#define DHT_WAIT_COUNT 100
#define DHT_WAIT_FOR_LOW for (unsigned char i = 0; i < DHT_WAIT_COUNT && DHT_IS_HIGH; i++) _delay_us(1)
#define DHT_WAIT_FOR_HIGH for (unsigned char i = 0; i < DHT_WAIT_COUNT && DHT_IS_LOW; i++) _delay_us(1)

#define OW_DDR DDRB
#define OW_READ PINB
#define OW_PIN 1

#define SEARCH_ROM 0xF0
#define ALARM_SEARCH 0xEC
#define MATCH_ROM 0x55
#define READ_DATA 0xA0

#define OW_UP OW_DDR &= ~(1 << OW_PIN)
#define OW_DOWN OW_DDR |= (1 << OW_PIN)
#define IS_LOW ((OW_READ & (1 << OW_PIN)) == 0)
#define IS_HIGH (OW_READ & (1 << OW_PIN))

#define WAIT_COUNT 1000
#define WAIT_FOR_LOW for (int i = 0; i < WAIT_COUNT && IS_HIGH; i++)
#define WAIT_FOR_HIGH for (int i = 0; i < WAIT_COUNT && IS_LOW; i++)

unsigned char sensor_data[2]; // Температура, Влажность
unsigned char isChange = 0;

unsigned char ROM[8] = {0xF3,0x00,0x00,0x00,0x00,0x00,0x07,0x0};
	
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
	
	unsigned char crc = 0;
	unsigned char i;
	unsigned char k;
	
	unsigned char rom_cmd = OW_readByte();

	switch (rom_cmd)
	{
		case SEARCH_ROM: // Поиск устройств на шине
		case ALARM_SEARCH: // Поиск устройств с флагом ALARM
			if ((rom_cmd != SEARCH_ROM) && (isChange == 0))
				return ;
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
				case READ_DATA: // Чтение данных	
					isChange = 0;
					crc = 0;
					for (i = 0; i < 2; i++) {
						OW_writeByte(sensor_data[i]);
						crc = crc_table(crc ^ sensor_data[i]);
					}
					OW_writeByte(crc);
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

unsigned char tmp[5];

void readDHT11()
{	
	SPIN(DHT_DDR, DHT_BIT);
	CPIN(DHT_PORT, DHT_BIT);
	_delay_ms(18);
	SPIN(DHT_PORT, DHT_BIT);
	_delay_us(40);
	CPIN(DHT_DDR, DHT_BIT);
	_delay_us(40); // Подождем шо скажет термометр
	if DHT_IS_HIGH {
		return;
	}	
	_delay_us(80);
	if DHT_IS_LOW {
		return;
	}	
	
	DHT_WAIT_FOR_LOW;
	for (unsigned char i = 0; i < 5; i++) {
		tmp[i] = 0;
		for (unsigned char k = 0; k < 8; k++) {
			DHT_WAIT_FOR_HIGH;
			_delay_us(40); // 0 = 28; 1 = 70
			if (DHT_IS_HIGH) {
				tmp[i] |= 1<<(7 - k);
			}			
			DHT_WAIT_FOR_LOW;
		}
	}
	
	if ((tmp[0] + tmp[2] == tmp[4]) && (tmp[4] != 0)) {
		if (tmp[0] != sensor_data[0] || tmp[2] != sensor_data[1]) {
			sensor_data[0] = tmp[0];
			sensor_data[1] = tmp[2];
			isChange = 1;
		}
	}
}

int main(void)
{
	unsigned char crc = 0;
	for (unsigned char i = 0; i < 7; i++)
		crc = (crc_table(crc ^ ROM[i]));
	ROM[7] = crc;
	
	DDRB = 0xff;
			
	// Шина
	CPIN(OW_DDR, OW_PIN);
		
	TIFR0 |= 1<<TOV0;
	GIFR |= 1<<INTF0;
	MCUCR = (1<<ISC01); //Сброс ISC00 - прерывание по \__
	GIMSK |= (1<<INT0);
	
	_delay_ms(10);
		
    while(1)
    {
		cli();
		SPIN(PORTB, DHT_LED_BIT);
		readDHT11();
		CPIN(PORTB, DHT_LED_BIT);
		sei();
		_delay_ms(60000);
    }
}