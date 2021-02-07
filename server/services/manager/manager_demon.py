#!/usr/bin/python3
#-*- coding: utf-8 -*-

import time
import curses
from subprocess import Popen, PIPE, STDOUT, call
import math

SCREENS = [["Справка", "", "", None, None, [], None],
           ["RS485", "rs485_demon.py", "/home/pyhome/server/rs485", None, None, [], None],
           ["Веб", "http_admin_demon.py", "/home/pyhome/server/http_admin", None, None, [], None],
           ["Расписание", "scheduler_demon.py", "/home/pyhome/server/scheduler", None, None, [], None],
           ["Выполнение", "executor_demon.py", "/home/pyhome/server/executor", None, None, [], None],
           ["Монитор", "watcher_demon.py", "/home/pyhome/server/watcher", None, None, [], None],
           ["Пульты", "lan_control_demon.py", "/home/pyhome/server/lan_control", None, None, [], None],
           ["Голос", "local_control_demon.py", "/home/pyhome/server/lan_control/local_control", None, None, [], None],
           ["Видео", "video_alerts_demon.py", "/home/pyhome/server/video_alerts", None, None, [], None]]

CURRENT_SCREEN = 0

screen_0_tmp = ["",
                 "   + Кнопки 1-%s это переключение между экранами." % (len(SCREENS) - 1),
                 "   + Кнопки Right|Left переклюают экраны по порядку.",
                 "   + Кнопка B перезапускает модули всех экранов.",
                 "   + Кнопка R перезапускает только модуль текущего экрана.",
                 "   + Кнопка Q прерывает работу экранов и закрывает приложение.",
                 ""]

SCREENS[0][5] = screen_0_tmp

def startProces(ind):
    global SCREENS
    if SCREENS[ind][1]:
        try:                                
            fff = open(str(ind) + ".txt", "w")
            f = open(str(ind) + ".txt", "w+")
            SCREENS[ind][3] = f
            SCREENS[ind][4] = Popen(["/usr/bin/python3", "-u", SCREENS[ind][2] + "/" + SCREENS[ind][1]], cwd=SCREENS[ind][2], stdout=fff, stderr=fff)
            SCREENS[ind][6] = fff
            time.sleep(0.25)
        except:
            pass
            
def startProceses():
    global SCREENS
    for ind in range(len(SCREENS)):
        startProces(ind)

def stopProces(ind):
    global SCREENS
    if SCREENS[ind][4]:
        SCREENS[ind][4].kill()
        SCREENS[ind][4] = None
    if SCREENS[ind][3]:
        SCREENS[ind][3].close()
        SCREENS[ind][3] = None
            
def stopProceses():
    global SCREENS
    for ind in range(len(SCREENS)):
        stopProces(ind)

call("pulseaudio", shell=True)

startProceses()

scr = curses.initscr()
curses.noecho()
curses.cbreak()
curses.curs_set(0)
scr.nodelay(1)
scr.keypad(1)

btn_keys = [ord('0'), 49, 50, 51, 52, 53]
btn_exit = [ord('q'), ord('Q')]
btn_reboot = [ord('r'), ord('R')]
btn_reboot_all = [ord('b'), ord('B')]

curses.start_color()
curses.init_pair(1, curses.COLOR_WHITE, curses.COLOR_BLACK)
c_normal = curses.color_pair(1)
curses.init_pair(2, curses.COLOR_YELLOW, curses.COLOR_BLUE)
c_header = curses.color_pair(2)

while True:
    c = scr.getch()
    scr.erase()
    size = scr.getmaxyx()

    scr.addstr(0, 0, ' ' * size[1], c_header)
    
    if c in btn_keys:
        CURRENT_SCREEN = btn_keys.index(c)        
    elif c in btn_exit:
        break
    elif c in btn_reboot:
        stopProces(CURRENT_SCREEN)
        startProces(CURRENT_SCREEN)
    elif c in btn_reboot_all:
        stopProceses()
        startProceses()
    elif c == curses.KEY_RIGHT:
        CURRENT_SCREEN += 1
        if CURRENT_SCREEN > len(SCREENS) - 1:
            CURRENT_SCREEN = 0 
    elif c == curses.KEY_LEFT:
        CURRENT_SCREEN -= 1
        if CURRENT_SCREEN < 0:
            CURRENT_SCREEN = len(SCREENS) - 1
        
    x = 0
    for i in range(len(SCREENS)):
        fff = SCREENS[i][3]
        if fff:
            emp = True
            while True:
                s = fff.readline()
                if len(s) > 0:
                    emp = False
                    SCREENS[i][5] += [s]
                    if len(SCREENS[i][5]) > 200:
                        del SCREENS[i][5][0]
                else:
                    break
            if not emp:
                f1 = SCREENS[i][3]
                f2 = SCREENS[i][6]
                f1.seek(0)
                f2.seek(0)
                f1.truncate()
                f2.truncate()
        btn = " %s-%s " % (i, SCREENS[i][0])
        if x < size[1]:
            btn_c = btn
            if len(btn) + x >= size[1]:
                btn_c = btn[:size[1] - x:]
            if CURRENT_SCREEN == i:
                scr.addstr(0, x, btn_c, c_header | curses.A_REVERSE)
            else:
                scr.addstr(0, x, btn_c, c_header | curses.A_NORMAL)
        x += len(btn)

        if CURRENT_SCREEN == i:
            if CURRENT_SCREEN == 0:
                tim = ["   Системное время:        %s" % (time.strftime("%d-%m-%Y %H:%M:%S"))]

                temp = "--"
                try:
                    ft = open("/sys/devices/virtual/thermal/thermal_zone0/temp", "r")
                    temp = ft.readline()
                    ft.close()
                except:
                    pass
                temp = temp.replace("\n", "")
                SCREENS[i][5] = screen_0_tmp + tim + ["   Температура процессора: %sºC" % (temp)]
    
            lines = SCREENS[i][5]
            try:
                h = size[0] - 1
                num = len(lines)
                off = 0
                if num > h:
                    off = num - h
                for y in range(min(h, num)):
                    s = lines[y + off]
                    scr.addstr(y + 1, 0, s[:size[1]], c_normal | curses.A_NORMAL)
            except:
                pass

    scr.refresh()
    time.sleep(0.1)
    
curses.endwin()

stopProceses()
