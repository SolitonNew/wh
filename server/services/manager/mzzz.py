#!/usr/bin/python3

import time
from subprocess import Popen, PIPE, STDOUT

f1 = open("zzz1.txt", "w")
f2 = open("zzz1.txt", "w+")

proc = Popen(["/usr/bin/python3", "-u", "zzz.py"], stdout=f1)

while True:
    s = f2.readline()
    if s:
        print(s)
        f1.seek(0)
        f1.truncate()
        f2.seek(0)
        f2.truncate()
    time.sleep(0.1)
