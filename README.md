# WISE HOUSE
<p>
<img src="logo.png" width="30%">
<img src="logo_2.png" width="30%">
<img src="logo_3.png" width="30%">
</p>

## Short installation guide

An example of installing the system on OrangePi Light with the Armbian operating system.

```
sudo apt update
sudo apt install apache2
sudo apt install php libapache2-mod-php
cd /var/www
sudo rm -r html
sudo chgrp www-data .
sudo chmod 775 .
sudo -u www-data git clone https://github.com/SolitonNew/wh.git .
sudo nano /etc/apache2/apache2.conf
```
```
<Directory /var/www/server>
	AllowOverride All
	Require all granted
</Directory>
```
```
sudo nano /etc/apache2/sites-available/000-default.conf
```
```
DocumentRoot /var/www/server/public
```
```
sudo a2enmod rewrite
sudo systemctl restart apache2
```
```
sudo apt install mysql-server
sudo apt install php7.4-mysql
```
```
sudo mysql
```
```
CREATE USER 'wisehouse'@'localhost' IDENTIFIED BY 'wisehousepass';
CREATE DATABASE wisehouse;
GRANT ALL PRIVILEGES ON wisehouse.* TO 'wisehouse'@'localhost';
FLUSH PRIVILEGES;
exit
```
```
sudo apt install php-xml
sudo apt install composer
cd server
sudo -u www-data composer install
```
```
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
sudo -u www-data nano .env
```
```
DB_DATABASE=wisehouse
DB_USERNAME=wisehouse
DB_PASSWORD=wisehousepass
TIMEZONE="Europe/Kiev"
LOCATION_LATITUDE=50.45466
LOCATION_LONGITUDE=30.5238
```
```
sudo -u www-data php artisan optimize
sudo -u www-data php artisan migrate --force --seed
```
```
sudo adduser www-data dialout
```
```
sudo nano /etc/cron.d/wh
```
```
* * * * *   www-data   /usr/bin/php /var/www/server/artisan schedule:run >/dev/null 2>&1
```
```
sudo apt-get install avr-libc gcc-avr
```

## License

The WiseHouse system is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

