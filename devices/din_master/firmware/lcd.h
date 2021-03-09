/*
 * log.h
 *
 * Created: 09.03.2021 9:39:44
 *  Author: User
 */ 

#define LCD_DDR DDRC
#define LCD_PORT PORTC
#define LCD_RST 3
#define LCD_SCK 2
#define LCD_DC 1
#define LCD_DATA 0

void lcd_init(void);
void lcd_char(unsigned char c);
void lcd_text(unsigned char *text, unsigned char num);
void lcd_hex(unsigned char byte);
void lcd_clear(void);