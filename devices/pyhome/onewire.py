"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

import pyb
from pyb import disable_irq
from pyb import enable_irq

class OneWire(object):
    CMD_SEARCHROM = 0xf0
    CMD_ALARM_SEARCH = 0xec
    CMD_READROM = 0x33
    CMD_MATCHROM = 0x55
    CMD_SKIPROM = 0xcc

    def __init__(self, pinId):
        self.num_errors = 0
        self.roms = []
        self.pin = pyb.Pin(pinId)
        self.pin.init(self.pin.IN, self.pin.PULL_UP)
        # Optimisation of stabilisation of time intervals
        self.links = (self.pin, pyb.udelay, self.pin.init, self.pin.value, self.pin.OUT_PP, self.pin.IN)

    def reset(self):
        """
        Perform the onewire reset function.
        """
        
        # Optimisation of stabilisation of time intervals
        pin, udelay, pinInit, pinValue, pinOUT, pinIN = self.links

        pinValue(0)
        pinInit(pinOUT)
        udelay(480)
        i = disable_irq()
        pinInit(pinIN, pin.PULL_UP)
        udelay(60)
        status = not pinValue()
        enable_irq(i)
        udelay(420)
        return status

    def write_bit(self, value):
        """
        Write a single bit.
        """
        
        # Optimisation of stabilisation of time intervals
        pin, udelay, pinInit, pinValue, pinOUT, pinIN = self.links

        i = disable_irq()
        pinValue(0)
        pinInit(pinOUT)
        udelay(1)
        if value:
            pinValue(1)
        udelay(60)
        pinValue(1)
        udelay(1)
        enable_irq(i)

    def read_bit(self):
        """
        Read a single bit
        """

        # Optimisation of stabilisation of time intervals
        pin, udelay, pinInit, pinValue, pinOUT, pinIN = self.links

        pinInit(pinIN, pin.PULL_UP) # Half of the packages are not matching by CRC whitout this line
        i = disable_irq()
        pinValue(0)
        pinInit(pinOUT)
        udelay(1)
        pinInit(pinIN, pin.PULL_UP)
        udelay(1)
        value = pinValue()
        enable_irq(i)
        udelay(40)
        return value

    def write_byte(self, value):
        """
        Write a byte.
        """

        for i in range(8):
            self.write_bit(value & 1)
            value >>= 1

    def read_byte(self):
        """
        Read a single byte and return the value as an integer.
        """

        value = 0
        for i in range(8):
            bit = self.read_bit()
            value |= bit << i
        #self.pin.init(self.pin.IN, self.pin.PULL_UP)
        return value

    def search(self):
        """
        Return a list of ROMs for all attached devices.
        """

        self.roms = self._search_roms(self.CMD_SEARCHROM)
        return self.roms

    def alarm_search(self):
        """
        Return a list of ROMs for all attached devices with alarm flag.
        """

        return self._search_roms(self.CMD_ALARM_SEARCH)
    
    def _search_roms(self, cmd):
        # Return a list of ROMs for all attached devices after command CMD
        
        res = []
        diff = 65
        rom = False
        for i in range(0xff):
            rom, diff = self._search_rom(rom, diff, cmd)
            if rom:
                res += [rom]
            if diff == 0:
                break
        return res
    
    def _search_rom(self, l_rom, diff, cmd):
        # Utility method for search of ROMs.
        
        if not self.reset():
            return None, 0
        self.write_byte(cmd)
        if not l_rom:
            l_rom = bytearray(8)
        rom = bytearray(8)
        next_diff = 0
        i = 64
        for byte in range(8):
            r_b = 0
            for bit in range(8):
                b = self.read_bit()
                if self.read_bit():                    
                    if b: # There are no devices or there is a mistake on the wire
                        return None, 0
                else:               
                    if not b: # Collision. Two devices with different bit meaning
                        if diff > i or ((l_rom[byte] & (1 << bit)) and (diff != i)):
                            b = 1
                            next_diff = i
                self.write_bit(b)
                if b:
                    r_b |= (1<<bit)
                i -= 1
            rom[byte] = r_b
        return rom, next_diff

    def match_rom(self, rom):
        """
        Select a specific device to talk to.
        """
        
        self.reset()        
        self.write_byte(self.CMD_MATCHROM)
        
        for byte in rom:
            self.write_byte(byte)

    def dev_list(self, family_code):
        """
        Returns a list of devices with pointed family code.
        """
    
        if len(self.roms) == 0:
            self.search()
        roms = []
        for rom in self.roms:
            if rom[0] == family_code:
                roms += [rom]
        return roms

    def crc8(self, data):
        """
        Check CRC.
        """

        crc = 0
        for i in range(len(data)):
            byte = data[i]
            for b in range(8):
                fb_bit = (crc ^ byte) & 0x01
                if fb_bit == 0x01:
                    crc = crc ^ 0x18
                crc = (crc >> 1) & 0x7f
                if fb_bit == 0x01:
                    crc = crc | 0x80
                byte = byte >> 1
        
        if crc:
            self.num_errors += 1
        
        return crc
