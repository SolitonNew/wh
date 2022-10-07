class TcNew(object):
    CMD_READ = 0xa0
    
    def __init__(self, onewire):
        self.ow = onewire
        self.buf = bytearray(12)
        
    def _match_rom(self, rom = False):
        #if not self.ow.reset():
        #    return False
        if not rom:
            roms = self.ow.dev_list(0xF0)
            if len(roms) > 0:
                rom = roms[0]
        if rom:
            self.ow.match_rom(rom)
            return rom
        else:
            return False
            
    def _get_data(self, rom):
        if self._match_rom(rom):
            self.ow.write_byte(self.CMD_READ)
        else:
            return None
        for i in range(12):
            self.buf[i] = self.ow.read_byte()
        if self.ow.crc8(self.buf):
            return None
        return True
        
    def get_data(self, rom = False):
        """
        Methods reads data from specified device. If the device is not
        specified then reading is done from first thermometer on the list
        (method search() is automaticly colled). 
        """
        if self._get_data(rom) == None:
            return None

        res = [[], []]
        
        for i in range(5):
            v = self.buf[i * 2] + (self.buf[i * 2 + 1] << 8)
            if v > 32767:
                v -= 0xffff + 1
            v = v / 10
            if v == -100:
                v = None
            res[0] += [v]

        for i in range(6):
            if self.buf[10] & (1<<i):
                res[1] += [1]
            else:
                res[1] += [0]
        
        return res
