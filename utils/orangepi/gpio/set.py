#!/usr/bin/python3
#-*- coding: utf-8 -*-

import sys
from pyA20.gpio import gpio

port = sys.argv[1]
value = sys.argv[2]

gpio.init()
gpio.setcfg(port, gpio.OUTPUT)
gpio.setcfg(port, value)