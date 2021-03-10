/*
 * led.h
 *
 * Created: 10.03.2021 17:44:21
 *  Author: User
 */ 

#define CONTROL_LED_R_DDR  DDRC
#define CONTROL_LED_R_PORT PORTC
#define CONTROL_LED_R_BIT  4

#define CONTROL_LED_G_DDR  DDRC
#define CONTROL_LED_G_PORT PORTC
#define CONTROL_LED_G_BIT  3

#define CONTROL_LED_Y_DDR  DDRB
#define CONTROL_LED_Y_PORT PORTB
#define CONTROL_LED_Y_BIT  5

#define CONTROL_LED_B_DDR  DDRB
#define CONTROL_LED_B_PORT PORTB
#define CONTROL_LED_B_BIT  3

#define CONTROL_BTN_1_DDR  DDRC
#define CONTROL_BTN_1_PORT PORTC
#define CONTROL_BTN_1_PIN  PINC
#define CONTROL_BTN_1_BIT  2

#define CONTROL_BTN_2_DDR  DDRC
#define CONTROL_BTN_2_PORT PORTC
#define CONTROL_BTN_2_PIN  PINC
#define CONTROL_BTN_2_BIT  1

#define CONTROL_BTN_3_DDR  DDRC
#define CONTROL_BTN_3_PORT PORTC
#define CONTROL_BTN_3_PIN  PINC
#define CONTROL_BTN_3_BIT  0

#define CONTROL_BTN_4_DDR  DDRB
#define CONTROL_BTN_4_PORT PORTB
#define CONTROL_BTN_4_PIN  PINB
#define CONTROL_BTN_4_BIT  4

typedef struct _control_btn_states {
	uint8_t btn_1;
	uint8_t btn_2;
	uint8_t btn_3;
	uint8_t btn_4;
} control_btn_states_t;

void control_init(void);
void control_led_r(uint8_t new_state);
void control_led_g(uint8_t new_state);
void control_led_y(uint8_t new_state);
void control_led_b(uint8_t new_state);

void control_check_btn(control_btn_states_t *states);