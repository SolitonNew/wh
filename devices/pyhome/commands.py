"""

    Pyhome component v2
    Part of the Watch House system     
    https://github.com/SolitonNew/wh
    
    Author: Moklyak Alexandr
  
"""

import math

commandList = []

def command_get(device):
    return device.value()

def command_set(device, value, delay=0):
    device.value(value, delay)

def command_on(device, delay=0):
    device.value(1, delay)

def command_off(device, delay=0):
    device.value(0, delay)

def command_toggle(device, delay=0):
    device.value(not device.value(), delay)
    
def command_speech(targetID, speechID, *args):
    global commandList
    data = ['speech', targetID, speechID]
    for a in args:
        data += [a]
    commandList += [data]
    
def command_play(targetID, mediaID, start, end):
    global commandList
    data = ['play', targetID, mediaID, start, end]
    commandList += [data]

def command_print(textID, *args):
    global commandList
    data = ['print', textID]
    for a in args:
        data += [a]
    commandList += [data]

def command_abs(value):
    return math.abs(value)
    
def command_round(value):
    return round(value)

def command_ceil(value):
    return math.ceil(value)

def command_floor(value):
    return math.floor(value)

