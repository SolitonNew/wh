<?php

namespace OrangePi;

class I2c 
{
    const PORT = '/dev/i2c-0';
    
    private $_address = false;
    
    /**
     * 
     * @param type $address
     */
    public function __construct($address)
    {
        $this->_address = $address;
    }
    
    /**
     * 
     * @param type $adr
     * @param type $byte
     */
    protected function writeByte($adr, $byte)
    {
        try {
            $f = fopen(self::PORT, 'r+b');
            if (!$f) return ;
            fwrite($f, pack('C', $adr));
            fwrite($f, pack('C', $byte));
            fflush($f);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        } finally {
            if ($f) {
                fclose($f);
            }
        }
    }

    /**
     * 
     * @param type $adr
     * @return type
     */
    protected function readByte($adr)
    {
        $res = null;
        $counter = 0;
        try {
            $f = fopen(self::PORT, 'r+b');
            if (!$f) return null;
            fwrite($f, pack('C', $adr));
            fflush($f);
            while ($counter < 1000) {
                $c = fgetc($f);
                if ($c !== false) {
                    $res = unpack('C', $c);
                } else {
                    usleep(1000);
                    $counter++;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        } finally {
            if ($f) {
                fclose($f);
            }
        }
        return $res;
    }

    /**
     * 
     * @param type $adr
     * @return type
     */
    protected function readWord($adr)
    {
        // ATANTION! Joke from Bosch! LBS before HBS. For calibration registers only!
        $lbs = $this->readByte($adr);
        $hbs = $this->readByte($adr + 1);
        return ($hbs << 8) + $lbs;
    }
    
    /**
     * 
     * @param type $adr
     * @return type
     */
    protected function readWordSign($adr)
    {
        $val = $this->readWord($adr);
        if ($val >= 0x8000) {
            return -((65535 - $val) + 1);
        } else {
            return $val;
        }
    }

    /**
     * 
     * @param type $adr
     * @return type
     */
    protected function readLong($adr)
    {
        $mbs = $this->readByte($adr);
        $lbs = $this->readByte($adr + 1);
        $xbs = $this->readByte($adr + 2);
        $val = ($mbs << 16) + ($lbs << 8) + $xbs;
        $val = ($val >> 4);
        return $val;
    }
}
