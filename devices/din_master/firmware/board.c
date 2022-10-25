/*

    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
*/

#include "config/mmcu.h"
#include "board.h"
#include <avr/io.h>
#include "control.h"

uint8_t controller_id;
uint8_t controller_initialized = 0;

void board_reset(void) {
    #if MMCU == MMCU_ATMEGA16A
    WDTCR |= 1<<WDE;
    #elif MMCU == MMCU_ATMEGA328
    #endif
    while (1) ;
}

void board_rs485_error(void) {
    control_led_r(1);
}

void board_onewire_error(void) {
    control_led_b(1);
}

void board_script_error(void) {
    control_led_r(1);
}

void board_rs485_incoming_package(uint8_t show) {
    control_led_g(show);
}

void board_onewire_search(uint8_t start) {
    control_led_y(start);
}
