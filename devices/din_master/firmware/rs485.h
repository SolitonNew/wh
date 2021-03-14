/*
 * rs485.h
 *
 * Created: 07.03.2021 13:34:14
 *  Author: User
 */

void rs485_init(void);
void rs485_sync(void);

void rs485_send(uint8_t c);
uint8_t rs485_check(void);