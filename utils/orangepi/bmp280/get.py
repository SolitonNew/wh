#!/usr/bin/python3
#-*- coding: utf-8 -*-

from bmp280 import BMP280

try:
    drv = BMP280(addr)
    res = drv.get_data()
    t = res["t"]
    p = res["p"]

    print("OK: %s %s" % (t, p))
except:
    print("ERROR")