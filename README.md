mediabox-php
============
## Demo Site:
http://mediabox.8x86.ru/
Login/password: test/test

## Install:

1) git clone https://github.com/Zazza/mediabox-php-yii.git

2) cd mediabox-php-yii

3) curl -s https://getcomposer.org/installer | php

4) php composer.phar install

5) chown -R www-data:www-data *

6) chmod -R 770 app/runtime/ web/assets/

7) Install DB form /sql:
mediabox-structure.sql
mediabox-data.sql

8) edit /app/config/main.php:

<pre>
'db'=>array(
    'connectionString' => 'mysql:host=localhost;dbname=mediabox',
    'emulatePrepare' => true,
    'username' => 'mediabox',
    'password' => 'mediabox',
    'charset' => 'utf8',
),
</pre>

<pre>
'storage' => 'http://storage'
</pre>

9) /etc/init.d/apache2 restart or /etc/init.d/nginx restart

10) Default login/password: admin:admin

