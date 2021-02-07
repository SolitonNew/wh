import os
from subprocess import Popen, PIPE

class Play():
    def __init__(self):
        self.player = False
        self.volume = 0
        self.duration = 0

    def play(self, file_name, loops, duration):
        if self.player:
            self.stop()
        self.duration = int(duration)
        self.volume = 0
        file_name = "/home/pyhome/server/executor/tracks/" + file_name
        self.player = Popen(["mplayer", "-slave", "-quiet", "-loop", loops, "-volume", "0"] + [file_name],
                             stdin=PIPE, stdout=PIPE, stderr=PIPE)

    def stop(self):
        if self.player:
            self.send_cmd("q")
            self.player = False        

    def send_cmd(self, cmd):
        if self.player:
            try:
                self.player.stdin.write((cmd + "\n").encode("utf-8"))
                self.player.stdin.flush()
            except:
                self.player = False

    def check_comm(self, db, command):
        try:            
            command.index("play")
            s = command.replace("play", "")
            s = s.replace("(", "")
            s = s.replace(")", "")
            s = s.replace("\"", "")
            args = s.split(",")

            db.IUD("insert into app_control_exe_queue "
                   "   (EXE_TYPE, EXE_DATA) "
                   "values "
                   "   ('play', '%s')" % (args[0].strip()))
            db.commit()
            
            self.play(args[0].strip(), args[1].strip(), args[2].strip())
            return True
        except:
            pass
        
        return False

    def time_handler(self):
        if self.player:
            max_vol = 40
            self.volume += max_vol / self.duration
            if self.volume > max_vol:
                self.volume = max_vol
            self.send_cmd("volume %s 100" % round(40 + round(self.volume)))
