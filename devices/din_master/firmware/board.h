#include <avr/io.h>

#define F_CPU 2000000UL
#define MAIN_LOOP_DELAY 1

#define SPIN(data, pin) (data |= (1<<pin))
#define CPIN(data, pin) (data &= ~(1<<pin))
#define GPIN(data, pin) (data & (1<<pin))

extern uint8_t controller_id;
extern uint8_t controller_initialized;

void board_reset(void);
void board_rs485_error(void);
void board_onewire_error(void);
void board_script_error(void);
void board_rs485_incoming_package(uint8_t show);
void board_onewire_search(uint8_t start);