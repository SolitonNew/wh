<?php

namespace OrangePi;

class I2c 
{
    const PORT = 0;
    
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
        shell_exec('i2cset -y '.self::PORT.' '.$this->_address.' '.$adr.' '.$byte);
    }

    /**
     * 
     * @param type $adr
     * @return type
     */
    protected function readByte($adr): int
    {
        return intval(trim(shell_exec('i2cget -y '.self::PORT.' '.$this->_address.' '.$adr)));
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
