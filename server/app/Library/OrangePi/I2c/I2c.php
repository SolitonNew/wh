<?php

namespace App\Library\OrangePi\I2c;

class I2c
{
    const PORT = 0;

    /**
     * @var int|bool
     */
    private int|bool $address = false;

    /**
     * @return array
     */
    public static function scan(): array
    {
        $output = shell_exec('i2cdetect -y -a '.self::PORT);

        $result = [];
        $num = 0;
        $y = 0;
        foreach (explode("\n", $output) as $line) {
            if ($y > 0) {
                $x = 0;
                foreach (explode(' ', trim($line)) as $cell) {
                    if ($x > 0) {
                        if ($cell != '--') {
                            $result[] = hexdec($cell);
                        }
                    }
                    $x++;
                }
            }
            $y++;
        }

        return $result;
    }

    /**
     * @param int $address
     */
    public function __construct(int $address)
    {
        $this->address = $address;
    }

    /**
     * @param int $adr
     * @param int $byte
     * @return void
     */
    protected function writeByte(int $adr, int $byte): void
    {
        shell_exec('i2cset -y '.self::PORT.' '.$this->address.' '.$adr.' '.$byte);
    }

    /**
     * @param int $adr
     * @return int
     */
    protected function readByte(int $adr): int
    {
        return hexdec(trim(shell_exec('i2cget -y '.self::PORT.' '.$this->address.' '.$adr)));
    }

    /**
     * @param int $adr
     * @return int
     */
    protected function readWord(int $adr): int
    {
        // ATANTION! Joke from Bosch! LBS before HBS. For calibration registers only!
        $lbs = $this->readByte($adr);
        $hbs = $this->readByte($adr + 1);
        return ($hbs << 8) + $lbs;
    }

    /**
     * @param int $adr
     * @return int
     */
    protected function readWordSign(int $adr): int
    {
        $val = $this->readWord($adr);
        if ($val >= 0x8000) {
            return -((65535 - $val) + 1);
        } else {
            return $val;
        }
    }

    /**
     * @param int $adr
     * @return int
     */
    protected function readLong(int $adr): int
    {
        $mbs = $this->readByte($adr);
        $lbs = $this->readByte($adr + 1);
        $xbs = $this->readByte($adr + 2);
        $val = ($mbs << 16) + ($lbs << 8) + $xbs;
        $val = ($val >> 4);
        return $val;
    }

    /**
     * @return array|null
     */
    public function getData(): array|null
    {
        return [];
    }
}
