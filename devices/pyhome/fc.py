"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

class FC(object):
    CMD_READ_DATA = 0xA0
    CMD_WRITE_DATA = 0xB0
    
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
            return False
        
        buff = bytearray(4)
        for i in range(4):
            buff[i] = self.ow.read_byte()

        if self.ow.crc8(buff):
            return False

        return buff

    def set_data(self, buff, rom = False):
        if self._match_rom(rom):
            self.ow.write_byte(self.CMD_WRITE_DATA)
        else:
            return False
        
        for b in buff:
            self.ow.write_byte(b)

        self.ow.write_byte(self.ow.crc8(buff))
    
    def value(self, val = None, channel = ''):
        if val == None:
            d = self.get_data(self.rom)

            if d != None:
                self.data = bytearray(d)

            return {'F1':self.data[0], 'F2':self.data[1], 'F3':self.data[2], 'F4':self.data[3]}
        else:
            if channel == 'F1':
                self.data[0] = int(val)
            elif channel == 'F2':
                self.data[1] = int(val)
            elif channel == 'F3':
                self.data[2] = int(val)
            elif channel == 'F4':
                self.data[3] = int(val)
            
            self.set_data(self.data, self.rom)
            
