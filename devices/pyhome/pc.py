class PC(object):
    CMD_READ_DATA = 0xA0
    CMD_WRITE_PROP = 0xB1
    
    def __init__(self, onewire, rom = False):
        self.ow = onewire
        self.rom = rom

    def _match_rom(self, rom = False):
        if not rom:
            roms = self.ow.dev_list(0xF2)
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
        
        buff = bytearray(2)
        for i in range(2):
            buff[i] = self.ow.read_byte()

        if self.ow.crc8(buff):
            return False

        res = bytearray(4)
        ii = [4, 3, 2, 0]
        for i in range(4):
            if buff[0] & (1<<ii[i]):
                res[i] = 1
            else:
                res[i] = 0
        return res
        
    def value(self, val = None, channel = ''):
        if val == None:
            d = self.get_data(self.rom)

            if d:
                self.data = d
                return {'P1':self.data[0], 'P2':self.data[1], 'P3':self.data[2], 'P4':self.data[3]}
            
            return None
        
