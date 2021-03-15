/*
 * rs485.h
 *
 * Created: 07.03.2021 13:34:14
 *  Author: User
 */

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128

/*
	INIT
	uint8_t sign[4]
	uint8_t count
	
	SYNC
	uint8_t sign[4]
	uint8_t count
 		int     id      2
		float   value   8
	uint8_t crc         1

	6 + (10) * count
*/

extern uint8_t rs485_in_buff[];
extern uint8_t rs485_in_buff_size;

void rs485_init(void);