/*
 *  Author: Moklyak Alexandr
 */ 

#define RS485_BAUD 9600
#define RS485_UBRR F_CPU/16/RS485_BAUD-1
#define RS485_BUFF_MAX_SIZE 128
#define RS485_BUFF_MIN_SIZE 8
//#define USART_RXC_vect _VECTOR(12) // 11

/*
    ����� �������. ����� ���� ��������� � ��� �������.
    
    sign: "CMD"
    controller_id: �� ����������� � ������� ����������� �����.
                   ������ ���������� ���� ������ � ����� �� ���������� ��.
    cmd: 1 - reset                 ������������ �����������
         2 - match receive         ����������� ������������� �������� ������ VAR (���-�� � ���� tag) 
                                   (���� �� ��������������� �� ���������� ������)
         3 - match transmit        ����������� ������������� �������� ������ VAR
         4 - pack transmit count   ����� ���������� ����� ����� transmit ��� ������������ (���-�� ������� 
                                   �������� � ���� tag)
         5 - pack transmit init    ����� ������ ���������� ����� transmit ���� �� ��������������� (������ 
                                   �������������)
         6 - match receive init    ���������� ������ ������������� ������ ������������� (���-�� � ���� tag)
         7 - match ow scan         ����� ������� � �����������, ��� �� �� ������������� ���� ���� (�� 
                                   ���������� ������ ����� pack transmit count � ������ ������ ROM).
        24 - firmware              ������ ������ �������� �� ����������. ����� ���� �������� ������ ����� 
		                           ������ �������� HEX. � ���� tag ���-�� ������� ��� ���������.
		25 - firmware query size   ������������� ���-�� ������� HEX ������� ���� �������� ������������. 
                                   � tag ���������� ���-�� �������, ��� ���� ����������. 
                                   ���� ����� ���������, �� ���������� ����� �������� cmd: 4 ��������� 
                                   � �������� ���������
        26 - query firmware        ������ �������� � ������ ������.
        27 - from boot             �������� ����� ����������� �� cmd: 3 �� ����������. ����� ����� ���� 
                                   ���� ���������� ����������� �� ����� ������� ������.
    tag: ����� �����, ������� ����� ���� �������� � ������ (� ����������� �� ��������)
    crc: ����������� ���� � ���������� ����������� onewire.
    
    ����������: ����� �������� ����� ����������� ������������ �� �� ���������� ��� ���������� ���� rs485_is_online
*/
typedef struct _rs485_cmd_pack {  // 8 bytes
    uint8_t sign[3];  // CMD
    uint8_t controller_id;
    uint8_t cmd;
    int tag;
    uint8_t crc;
} rs485_cmd_pack_t;

/*
    ����� �������� �������� ����� ����������.
    
    sign: "VAR"
    controller_id: �� ����������� � ������� ����������� �����.
                   ������ ���������� ���� ������ � ����� �� ���������� ���.
    id: �� ����������
    value: �������� ����������
    crc: ����������� ���� � ���������� ����������� onewire.
*/
typedef struct _rs485_var_pack {  // 9 bytes
    uint8_t sign[3];  // VAR
    uint8_t controller_id;
    int id;
    int value;
    uint8_t crc;
} rs485_var_pack_t;

/*
    ����� �������� ����� ������ ROM
    
    sign: ROM
    controller_id: �� ����������� � ������� ����������� �����.
                   ������ ���������� ���� ������ � ����� �� ���������� ���.
    rom: ROM
    crc: ����������� ���� � ���������� ����������� onewire.
*/
typedef struct _rs485_ow_rom_pack {  // 13 bytes
    uint8_t sign[3]; // ROM
    uint8_t controller_id;
    uint8_t rom[8];
    uint8_t crc;
} rs485_ow_rom_pack_t;

/*
    ����� �������� 8 ���� ��������.
	���������� � ������� ������ ������ ����� ��� ���� ����� ������ 
	��� �� ������������. ���������� ������������� �����������.
	
	sign: HEX
	controller_id: �� ����������� � ������� ����������� �����.
                   ������ ���������� ���� ������ � ����� �� ���������� ���.
    data: hex ������
	crc: ����������� ���� � ���������� ����������� onewire.
*/
typedef struct _rs485_hex_pack { // 13 bytes
	uint8_t sign[3]; // HEX
	uint8_t controller_id;
	uint8_t data[8];
	uint8_t crc;
} rs485_hex_pack_t;

extern uint16_t rs485_errors;
extern uint16_t rs485_packs;
extern uint16_t rs485_recieve_count;

void rs485_init(void);
void rs485_transmit_CMD(uint8_t cmd, int tag);
void rs485_transmit_VAR(int id, int value);
void rs485_transmit_ROM(uint8_t *rom);
void rs485_in_buff_unpack(void);
