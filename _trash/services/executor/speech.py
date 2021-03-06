import os
import subprocess
import time

class Speech():
    def __init__(self):
        pass

    def check_comm(self, db, command):
        try:            
            command.index("speech")
            a = command.split('"')

            text = a[1]
            try:
                speech_type = a[3]
            except:
                speech_type = 'notify'

            res = db.select("select ID from app_control_speech_audio where SPEECH = '%s'" % (text))
            if len(res) == 0:
                db.IUD("insert into app_control_speech_audio (SPEECH) values ('%s')" % (text))
                exe_id = db._lastID
            else:
                exe_id = res[0][0]
                db.IUD("update app_control_speech_audio "
                       "   set LAST_USE_TIME = CURRENT_TIMESTAMP "
                       " where ID = %s" % (exe_id))

            f_name = "/var/tmp/wisehouse/audio_%s.wav" % (exe_id)
            if not os.path.exists(f_name):
                subprocess.call('echo "' + text + '" | RHVoice-test -p Anna -o /var/tmp/wisehouse/audio_%s.wav' % (exe_id), shell=True)

            db.IUD("insert into app_control_exe_queue "
                   "   (SPEECH_AUDIO_ID, SPEECH_TYPE, EXE_TYPE) "
                   "values "
                   "   (%s, '%s', 'speech')" % (exe_id, speech_type))
            db.commit()                
            print("")
                
            return True
        except:
            pass
        return False

    def time_handler(self):
        pass
