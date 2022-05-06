<?php

use OrangePi\Bme280;

include_once 'I2c.php';
include_once 'Bme280.php';

try {
    $bme = new Bme280();
    $data = $bme->getData();
    print(print_r($data, true));
} catch (\Exception $ex) {
    print($ex->getMessage());
}
    