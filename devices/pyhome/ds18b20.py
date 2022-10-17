"""
DS18B20 temperature sensor driver for MicroPython.
Copyright (c) 2015, Moklyak Alexandr.
--------------------------------------------------------------------------
This class uses the OneWire driver to control DS18B20 temperature sensors.
It supports multiple devices on the same 1-wire bus. The following example
assumes the ground of your DS18B20 is connected to VCC and GND and the data
pin is connected to X4.
WARNING: There should not be any activity on the tire when using the parasite
power during the temperature conversion.
EXAMPLES:
1. Only one device is connected:
>>> import pyb
>>> from ds18b20 import DS18B20
>>> from onewire import OneWire
>>> ow = OneWire('X4')
>>> ds = DS18B20(ow)
>>> ds.start_measure()
>>> pyb.delay(750)
>>> print(ds.get_temp())
2. Output of data from all devices on the wire:
>>> import pyb
>>> from ds18b20 import DS18B20
>>> from onewire import OneWire
>>> ow = OneWire('X4')
>>> ds = DS18B20(ow)
>>> ds.start_measure()
>>> roms = ow.search()
>>> pyb.delay(750)
>>> temps = []
>>> for rom in roms:
>>>     temps += [ds.get_temp(rom)]
>>> print(temps)
"""

class DS18B20(object):
    THERM_CMD_CONVERTTEMP = 0x44
    THERM_CMD_RSCRATCHPAD = 0xbe
    THERM_CMD_WSCRATCHPAD = 0x4e
    THERM_CMD_CSCRATCHPAD = 0x48
    THERM_CMD_ESCRATCHPAD = 0xb8
    
    def __init__(self, onewire):
        self.ow = onewire
        self.buff = bytearray(9)
        
    def _match_rom(self, rom = False):
        # The method checks if the tire is ready to work, defines which 
        # ROM to work with, and sends a command to select the active device
        # with this key. As a result, the method returns the ROM of the
        # selected device or False if the operation can not be continued.
        
        if not self.ow.reset():
            return False
        if not rom:
            roms = self.ow.dev_list(0x28)
            if len(roms) > 0:
                rom = roms[0]
        if rom:
            self.ow.match_rom(rom)
            return rom
        else:
            return False
        #self.ow.write_byte(0xcc)
        
    def start_measure(self, rom = False):
        """
        Method sends a command to device to measure temperature. After this
        method is called, need to wait 750ms before reading the data.
        If the device is not specified, the command is sent to all devices
        on the wire.
        """
        
        if not self.ow.reset():
            return False
        if rom:
            self.ow.match_rom(rom)
        else:
            self.ow.write_byte(self.ow.CMD_SKIPROM)
        self.ow.write_byte(self.THERM_CMD_CONVERTTEMP)
        return True
    
    def _get_data(self, rom):
        # It returns the content of the memory device.
        
        if self._match_rom(rom):
            self.ow.write_byte(self.THERM_CMD_RSCRATCHPAD)
        else:
            return None
        for i in range(9):
            self.buff[i] = self.ow.read_byte()
        if self.ow.crc8(self.buff):
            return None
        return True
        
    def get_temp(self, rom = False):
        """
        Methods reads data from specified device. If the device is not
        specified then reading is done from first thermometer on the list
        (method search() is automaticly colled). 
        """
        
        if self._get_data(rom) == None:
            return None

        #return (self.buff[1] << 8 | self.buff[0]) / 16

        b = self.buff[1] << 8 | self.buff[0]
        if (b & (1<<15)):        
            m = b
            b = 0
            for i in range(16):
                if m & (1<<i) == 0:
                    b |= (1<<i)
            b = -b
            b += 1        
        return b / 16

    def get_config(self, rom = False):
        """
        The method determines the configuration that is stored in the RAM
        device. If the device is not specified then reading is done from first
        thermometer on the list (method search() is automaticly colled).
        The result of the method is the tuple:
            - maximum temperature alarm
            - minimum temperature alarm
            - bit temperature measurement (9-12 bits)
        """
        
        if self._get_data(rom) == None:
            return None

        max_temp = self.buff[2]
        if max_temp & (1<<7):
            max_temp = -(max_temp & ~(1<<7))
            
        min_temp = self.buff[3]
        if min_temp & (1<<7):
            min_temp = -(min_temp & ~(1<<7))

        bit = (self.buff[4] >> 5) + 9
        return(max_temp, min_temp, bit)

    def set_config(self, rom, max_temp, min_temp, bit):
        """
        Installing the configuration of the thermometer. Set upper and lower
        alarm threshold. Data is written to the RAM device.
        The maximum and minimum temperature alarm is a signed integer. Bit is
        set in the range of 9-12 bits.
        """
        
        if (max_temp < 0):
            max_temp = abs(max_temp) | (1<<7)
        if (min_temp < 0):
            min_temp = abs(min_temp) | (1<<7)

        config = 0b00011111 #9bit
        if bit == 10: config |= (1<<5) #10bit
        elif bit == 11: config |= (1<<6) #11bit
        elif bit == 12: config |= (1<<5) | (1<<6) #12bit
        
        if self._match_rom(rom):
            self.ow.write_byte(self.THERM_CMD_WSCRATCHPAD)
            self.ow.write_byte(max_temp)
            self.ow.write_byte(min_temp)
            self.ow.write_byte(config)
        else:
            return False

    def save_config(self, rom = False):
        """
        Method sends a command to device to store data from the RAM in the
        EEPROM. Stored device data is automatically read when you reconnect
        power.
        """
        
        if self._match_rom(rom):        
            self.ow.write_byte(self.THERM_CMD_CSCRATCHPAD)
        else:
            return False

    def load_config(self, rom = False):
        """
        The method sends a command to transfer data from EEPROM to the RAM of
        the device.
        """
        
        if self._match_rom(rom):
            self.ow.write_byte(self.THERM_CMD_ESCRATCHPAD)
        else:
            return False
