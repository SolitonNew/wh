#include <avr/io.h>

#define F_CPU 8000000UL

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))

extern uint8_t controller_id;