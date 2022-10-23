"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

class HS(object):
    CMD_READ_DATA = 0xA0
    SENSOR_LEFT = 8
    SENSOR_RIGHT = 16
    SENSOR_LEFT_LONG = 32
    SENSOR_RIGHT_LONG = 64
    
    def __init__(self, onewire, rom = False):
        self.ow = onewire
        self.rom = rom

    def _match_rom(self, rom = False):
        if not rom:
            roms = self.ow.dev_list(0xF0)
            if len(roms) > 0:
                rom = roms[0]
        if rom:
            self.ow.match_rom(rom)
            return rom
        else:
            return False
        
    def get_data(self, rom = False):
        if self._match_rom(rom):
            self.ow.write_byte(self.CMD_READ_DATA)
        else:
            return None
        
        buff = bytearray(2)
        for i in range(2):
            buff[i] = self.ow.read_byte()

        if self.ow.crc8(buff):
            return None

        return buff[0]
        
    def value(self, val = None, channel = ''):
        if val == None:
            resLeft = 0
            resRight = 0
            resLeftLong = 0
            resRightLong = 0
            
            d = self.get_data(self.rom)

            if d != None:
                if (d & self.SENSOR_LEFT):
                    resLeft = 1
                    
                if (d & self.SENSOR_RIGHT):
                    resRight = 1
                    
                if (d & self.SENSOR_LEFT_LONG):
                    resLeftLong = 1
                    
                if (d & self.SENSOR_RIGHT_LONG):
                    resRightLong = 1

            return {'LEFT': resLeft, 'RIGHT': resRight, 'LEFT_LONG': resLeftLong, 'RIGHT_LONG': resRightLong}
            
