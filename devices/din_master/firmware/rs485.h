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
    ����� �������. ����� ���� ��������� � ��� �������.
    
    sign: "CMD"
    controller_id: �� ����������� � ������� ����������� �����.
                   ������ ���������� ���� ������ � ����� �� ���������� ��.
    cmd: 1 - reset                 ������������ �����������
         2 - match receive         ����������� ������������� �������� ������ VAR (���-�� � ���� tag) 
                                   (���� �� ��������������� �� ���������� ������)
         3 - match transmit        ����������� ������������� �������� ������ VAR
         2 - pack transmit count   ����� ���������� ����� ����� transmit ��� ������������ (���-�� ������� 
                                   �������� � ���� tag)
         4 - pack transmit init    ����� ������ ���������� ����� transmit ���� �� ��������������� (������ 
                                   �������������)
         5 - mach receive init     ���������� ������ ������������� ������ ������������� (���-�� � ���� tag)
         6 - match ow scan         ����� ������� � �����������, ��� �� �� ������������� ���� ���� (�� 
                                   ���������� ������ ����� pack transmit count � ������ ������ ROM).
    tag: ����� �����, ������� ����� ���� �������� � ������ (� ����������� �� ��������)
    crc: ����������� ���� � ���������� ����������� onewire.
*/
typedef struct _rs485_cmd_pack {
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
typedef struct _rs485_var_pack {
    uint8_t sign[3];  // VAR
    uint8_t controller_id;
    int id;
    float value;
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
typedef struct _rs485_ow_rom_pack {
    uint8_t sign[3]; // ROM
    uint8_t controller_id;
    uint8_t rom[8];
    uint8_t crc;
} rs485_ow_rom_pack_t;

extern uint8_t rs485_in_buff[];
extern uint8_t rs485_in_buff_size;

void rs485_init(void);
void rs485_transmit_CMD(uint8_t cmd, int tag);
void rs485_transmit_VAR(int id, float value);
void rs485_transmit_ROM(uint8_t *rom);