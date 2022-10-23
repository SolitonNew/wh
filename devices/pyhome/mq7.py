import math

class MQ7(object):
    CMD_READ_DATA = 0xA0
    
    def __init__(self, onewire):
        self.ow = onewire
        self.roms = []

    def _match_rom(self, rom = False):
        #if not self.ow.reset():
        #    return False
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

        adc = (buff[0]<<8) + (buff[1])
        adc_k = (1023 - adc) / adc
        try:
            return math.exp((math.log(adc_k / 1.18) - 3.38) / -0.77)
        except:
            return False
        
    def value(self, val = None, channel = ''):
        if val == None:
            res = self.get_data(self.rom)
            if res:
                res = ((res * 10)//1)/10
            return res
    
