import math

def command_get(variable):
    return variable.value()

def command_set(variable, value, delay=0):
    variable.value(value, delay)

def command_on(variable, delay=0):
    variable.value(1, delay)

def command_off(variable, delay=0):
    variable.value(0, delay)

def command_toggle(variable, delay=0):
    variable.value(not variable.value(), delay)
    
def command_speech(speechID):
    pass
    
def command_play(mediaID):
    pass

def command_info():
    pass

def command_print(value):
    pass

def command_abs(value):
    return math.abs(value)
    
def command_round(value):
    return round(value)

def command_ceil(value):
    return math.ceil(value)

def command_floor(value):
    return math.floor(value)
