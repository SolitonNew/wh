"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

class DHT11(object):
    CMD_READ_DATA = 0xA0
    
    def __init__(self, onewire, rom = False):
        self.ow = onewire
        self.rom = rom

    def _match_rom(self, rom = False):
        if not rom:
            roms = self.ow.dev_list(0xF3)
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
        
        buff = bytearray(3)
        for i in range(3):
            buff[i] = self.ow.read_byte()

        if self.ow.crc8(buff):
            return False

        return buff[:2:]
        
    def value(self, val = None, channel = ''):
        if val == None:
            resH = 0
            resT = 0
            
            d = self.get_data(self.rom)

            if d != None:
                resH = d[0]
                resT = d[1]
            return {'H':resH, 'T':resT}
        
