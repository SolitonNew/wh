#!/usr/bin/python3
#-*- coding: utf-8 -*-

from bmp280 import BMP280

addr = 0x76
for i in range(2):
    addr += i
    try:
        BMP280(addr)
        print("OK: %s" % (hex(addr)))
        break
    except:
        print("ERROR: %s" % (hex(addr)))
