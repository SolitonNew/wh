import os
import subprocess

class Speecher():
    def __init__(self):
        pass

    def check_comm(self, db, command):
        try:            
            command.index("speech")
            s = command.replace("speech", "")
            s = s.replace("(", "")
            s = s.replace(")", "")
            s = s.replace("\"", "")
            subprocess.call('echo "' + s.strip() + '" | spd-say -o rhvoice -l ru -e -t female1 -r 30 -p 10', shell=True)
            return True
        except:
            pass
        return False

    def time_handler(self):
        pass
