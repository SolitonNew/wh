<?php

namespace App\Library\Server\I2c;

class Bmp280 extends I2c
{
    // BME280 Registers
    const BME280_CONTROL_MEAS      = 0xF4;
    const BME280_CONTROL_HUM       = 0xF2;

    const BME280_CONFIG            = 0xF5;
    const BME280_PRESSURE          = 0xF7;
    const BME280_TEMP              = 0xFA;

    const BME280_DIG_T1            = 0x88;
    const BME280_DIG_T2            = 0x8A;
    const BME280_DIG_T3            = 0x8C;
    const BME280_DIG_P1            = 0x8E;
    const BME280_DIG_P2            = 0x90;
    const BME280_DIG_P3            = 0x92;
    const BME280_DIG_P4            = 0x94;
    const BME280_DIG_P5            = 0x96;
    const BME280_DIG_P6            = 0x98;
    const BME280_DIG_P7            = 0x9A;
    const BME280_DIG_P8            = 0x9C;
    const BME280_DIG_P9            = 0x9E;
    const BME280_DIG_H1            = 0xA1;
    const BME280_DIG_H2            = 0xE1;
    const BME280_DIG_H3            = 0xE3;
    const BME280_DIG_H4            = 0xE4;
    const BME280_DIG_H5            = 0xE5;
    const BME280_DIG_H6            = 0xE7;

    // Oversampling Setting
    const BME280_OVERS_T1          = 0x20;
    const BME280_OVERS_T2          = 0x40;
    const BME280_OVERS_T4          = 0x60;
    const BME280_OVERS_T8          = 0x80;
    const BME280_OVERS_T16         = 0xA0;

    const BME280_OVERS_P1          = 0x04;
    const BME280_OVERS_P2          = 0x08;
    const BME280_OVERS_P4          = 0x0C;
    const BME280_OVERS_P8          = 0x10;
    const BME280_OVERS_P16         = 0x14;

    const BME280_OVERS_H1          = 0x01;
    const BME280_OVERS_H2          = 0x02;
    const BME280_OVERS_H4          = 0x03;
    const BME280_OVERS_H8          = 0x04;
    const BME280_OVERS_H16         = 0x05;

    // Power Modes.
    const BME280_NORMAL_MODE       = 0x03;

    const BME280_TSB_0_5           = 0x00;
    const BME280_TSB_62_5          = 0x20;
    const BME280_TSB_125           = 0x40;
    const BME280_TSB_250           = 0x60;
    const BME280_TSB_500           = 0x80;
    const BME280_TSB_1000          = 0xA0;
    const BME280_TSB_2000          = 0xC0;
    const BME280_TSB_4000          = 0xE0;

    const BME280_FILTER_OFF                = 0x00;
    const BME280_FILTER_COEFFICIENT2       = 0x04;
    const BME280_FILTER_COEFFICIENT4       = 0x08;
    const BME280_FILTER_COEFFICIENT8       = 0x0C;
    const BME280_FILTER_COEFFICIENT16      = 0x10;

    const BME280_SPI_OFF           = 0x00;
    const BME280_SPI_ON            = 0x01;

    const BME280_CONTROL_MEAS_SET = (self::BME280_OVERS_T16 | self::BME280_OVERS_P16 | self::BME280_NORMAL_MODE);
    const BME280_CONTROL_HUM_SET  = self::BME280_OVERS_H2;
    const BME280_CONFIG_SET       = (self::BME280_TSB_0_5 | self::BME280_FILTER_COEFFICIENT16 | self::BME280_SPI_OFF);

    private array $digs = [];

    public function __construct(int $address = 0x76)
    {
        parent::__construct($address);
        $this->init();
    }

    /**
     * @return void
     */
    protected function init(): void
    {
        // Read calibration values
        $this->digs['t1'] = $this->readWord(self::BME280_DIG_T1);      // Unsigned
        $this->digs['t2'] = $this->readWordSign(self::BME280_DIG_T2);
        $this->digs['t3'] = $this->readWordSign(self::BME280_DIG_T3);
        $this->digs['p1'] = $this->readWord(self::BME280_DIG_P1);      // Unsigned
        $this->digs['p2'] = $this->readWordSign(self::BME280_DIG_P2);
        $this->digs['p3'] = $this->readWordSign(self::BME280_DIG_P3);
        $this->digs['p4'] = $this->readWordSign(self::BME280_DIG_P4);
        $this->digs['p5'] = $this->readWordSign(self::BME280_DIG_P5);
        $this->digs['p6'] = $this->readWordSign(self::BME280_DIG_P6);
        $this->digs['p7'] = $this->readWordSign(self::BME280_DIG_P7);
        $this->digs['p8'] = $this->readWordSign(self::BME280_DIG_P8);
        $this->digs['p9'] = $this->readWordSign(self::BME280_DIG_P9);

        $this->digs['h1'] = $this->readByte(self::BME280_DIG_H1);	// unsigned char
        $this->digs['h2'] = $this->readWordSign(self::BME280_DIG_H2);
        $this->digs['h3'] = $this->readByte(self::BME280_DIG_H3);	// unsigned char
        $this->digs['h4'] = ($this->readByte(self::BME280_DIG_H4) << 24) >> 20;
        $this->digs['h4'] = $this->digs['h4'] | $this->readByte(self::BME280_DIG_H4 + 1) & 0x0F;

        $this->digs['h5'] = ($this->readByte(self::BME280_DIG_H5 + 1) << 24) >> 20;
        $this->digs['h5'] = $this->digs['h5'] | ($this->readByte(self::BME280_DIG_H5) >> 4) & 0x0F;

        $this->digs['h6'] = $this->readByte(self::BME280_DIG_H6);	# signed char
        if ($this->digs['h6'] > 127) {
            $this->digs['h6'] = 127 - $this->digs['h6'];
        }

        // Set Configuration
        $this->writeByte(self::BME280_CONFIG, self::BME280_CONFIG_SET);
        $this->writeByte(self::BME280_CONTROL_HUM, self::BME280_CONTROL_HUM_SET);
        $this->writeByte(self::BME280_CONTROL_MEAS, self::BME280_CONTROL_MEAS_SET);
    }

    /**
     * @return array|null
     */
    public function getData(): array|null
    {
        $adc_t = $this->readLong(self::BME280_TEMP);
        $adc_p = $this->readLong(self::BME280_PRESSURE);

        $var1 = ($adc_t / 16384.0 - $this->digs['t1'] / 1024.0) * $this->digs['t2'];
        $var2 = (($adc_t / 131072.0 - $this->digs['t1'] / 8192.0) * ($adc_t / 131072.0 - $this->digs['t1'] / 8192.0)) * $this->digs['t3'];
        $t_fine = ($var1 + $var2);
        $temperature = round(($t_fine / 5120.0) * 10) / 10;

        if ($temperature < -40 || $temperature > 85) return null;

        $var1 = ($t_fine / 2.0) - 64000.0;
        $var2 = $var1 * $var1 * $this->digs['p6'] / 32768.0;
        $var2 = $var2 + $var1 * $this->digs['p5'] * 2.0;
        $var2 = ($var2 / 4.0) + ($this->digs['p4'] * 65536.0);
        $var1 = ($this->digs['p3'] * $var1 * $var1 / 524288.0 + $this->digs['p2'] * $var1) / 524288.0;
        $var1 = (1.0 + $var1 / 32768.0) * $this->digs['p1'];

        // Avoid exception caused by division by zero
        if ($var1 == 0.0) return null;

        $p = 1048576.0 - $adc_p;
        $p = ($p - ($var2 / 4096.0)) * 6250.0 / $var1;
        $var1 = $this->digs['p9'] * $p * $p / 2147483648.0;
        $var2 = $p * $this->digs['p8'] / 32768.0;
        $pressure = round(($p + ($var1 + $var2 + $this->digs['p7']) / 16.0));
        $pressure = round(($pressure / 133.322) * 10) / 10;

        if ($pressure < 700 || $pressure > 800) return null;

        return [
            'TEMP' => $temperature,
            'P' => round($pressure),
        ];
    }
}
