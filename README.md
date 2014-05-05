mediabox-php
============

## Install:

1) git clone https://github.com/Zazza/mediabox-php-yii.git

2) cd mediabox-php-yii

3) curl -s https://getcomposer.org/installer | php

4) php composer.phar install

5) chown -R www-data:www-data *

6) chmod -R 770 app/runtime/ web/assets/

7) edit /app/config/main.php:

<pre>
'db'=>array(
    'connectionString' => 'mysql:host=localhost;dbname=mediabox',
    'emulatePrepare' => true,
    'username' => 'mediabox',
    'password' => 'mediabox',
    'charset' => 'utf8',
),
</pre>

8) /etc/init.d/apache2 restart or /etc/init.d/nginx restart

-----

php.ini:
<pre>
upload_max_filesize = [NUM]M
post_max_size = [NUM]M
</pre>

nginx.conf:
<pre>
client_max_body_size = [NUM]M
</pre>
